<?php
/**
 * WP-CLI 命令
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

/**
 * SunLyvo Nexus 管理命令
 */
class SLV_CLI_Command {

    /**
     * 显示 CLI 版本与诊断信息
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function version( $args, $assoc_args ): void {
        $methods = [
            'version', 'seed', 'list_users', 'list_levels',
            'purchase', 'purchase_delete', 'reset_content',
            'user_info', 'access',
            'product_list', 'product_sync', 'order_list', 'order_create', 'order_pay',
        ];

        $rows = [];

        foreach ( $methods as $method ) {
            $rows[] = [
                'method'     => $method,
                'registered' => method_exists( $this, $method ) ? 'yes' : 'no',
            ];
        }

        \WP_CLI\Utils\format_items( 'table', $rows, [ 'method', 'registered' ] );

        WP_CLI::log( sprintf( 'CLI 版本：%s', 'v2.1.0' ) );
        WP_CLI::log( sprintf( '当前 locale：%s', determine_locale() ) );
    }

    /**
     * 删除购买记录
     *
     * ## OPTIONS
     *
     * <user>
     * : 用户ID或登录名
     *
     * <object_type>
     * : chapter 或 collection
     *
     * <object_id>
     * : 内容ID
     *
     * ## EXAMPLES
     *
     *     wp slv purchase_delete iSeven chapter 25
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function purchase_delete( $args, $assoc_args ): void {
        if ( count( $args ) < 3 ) {
            WP_CLI::error( '用法：wp slv purchase_delete <user> <object_type> <object_id>' );
        }

        $user = $this->resolve_user( (string) $args[0] );

        if ( ! $user ) {
            WP_CLI::error( sprintf( '用户不存在：%s', $args[0] ) );
        }

        $object_type = (string) $args[1];
        $object_id   = (int) $args[2];

        if ( ! in_array( $object_type, [ 'chapter', 'collection' ], true ) ) {
            WP_CLI::error( 'object_type 必须是 chapter 或 collection。' );
        }

        global $wpdb;

        $deleted = $wpdb->delete(
            $wpdb->prefix . 'slv_user_purchases',
            [
                'user_id'     => (int) $user->ID,
                'object_type' => $object_type,
                'object_id'   => $object_id,
            ],
            [ '%d', '%s', '%d' ]
        );

        if ( false === $deleted ) {
            WP_CLI::error( '删除失败。' );
        }

        if ( 0 === $deleted ) {
            WP_CLI::warning( '未找到匹配的购买记录。' );
            return;
        }

        WP_CLI::success(
            sprintf( '已删除 %d 条购买记录（%s #%d）', $deleted, $object_type, $object_id )
        );
    }

    /**
     * 重置某个内容的所有测试数据
     *
     * 删除：
     * - 所有购买记录（所有用户）
     * - 关联的订单（保留订单本身，只清理关联，避免影响其他测试）
     * - 所有购物车项
     *
     * ## OPTIONS
     *
     * <content_id>
     * : 内容ID（章节或合集）
     *
     * [--yes]
     * : 跳过确认
     *
     * ## EXAMPLES
     *
     *     wp slv reset_content 25
     *     wp slv reset_content 25 --yes
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function reset_content( $args, $assoc_args ): void {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( '用法：wp slv reset_content <content_id> [--yes]' );
        }

        $content_id = (int) $args[0];
        $post       = get_post( $content_id );

        if ( ! $post ) {
            WP_CLI::error( sprintf( '内容不存在：%d', $content_id ) );
        }

        if ( ! in_array( $post->post_type, [ 'slv_chapter', 'slv_collection' ], true ) ) {
            WP_CLI::error( '内容类型必须是 slv_chapter 或 slv_collection。' );
        }

        $content_type = 'slv_chapter' === $post->post_type ? 'chapter' : 'collection';

        // 确认
        $confirmed = isset( $assoc_args['yes'] );

        if ( ! $confirmed ) {
            WP_CLI::confirm(
                sprintf( '将删除 "%s" 的所有购买记录和购物车项，确定？', $post->post_title ),
                $assoc_args
            );
        }

        global $wpdb;

        // 1. 删除购买记录
        $deleted_purchases = $wpdb->delete(
            $wpdb->prefix . 'slv_user_purchases',
            [
                'object_type' => $content_type,
                'object_id'   => $content_id,
            ],
            [ '%s', '%d' ]
        );

        // 2. 清理购物车中关联的商品
        $product = slv_product_get_by_linked_object( $content_type, $content_id );
        $deleted_cart = 0;

        if ( null !== $product ) {
            $deleted_cart = $wpdb->delete(
                $wpdb->prefix . 'slv_carts',
                [ 'product_id' => (int) $product['id'] ],
                [ '%d' ]
            );
        }

        WP_CLI::success(
            sprintf(
                '已重置 "%s"：购买记录 %d 条，购物车项 %d 条。',
                $post->post_title,
                false === $deleted_purchases ? 0 : (int) $deleted_purchases,
                false === $deleted_cart ? 0 : (int) $deleted_cart
            )
        );
    }

    /**
     * 列出所有商品
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function product_list( $args, $assoc_args ): void {
        $products = slv_product_list(
            [
                'product_status' => '',
                'limit'          => 500,
            ]
        );

        if ( empty( $products ) ) {
            WP_CLI::warning( '无商品。' );
            return;
        }

        $rows = [];

        foreach ( $products as $p ) {
            $rows[] = [
                'id'       => (string) $p['id'],
                'type'     => (string) $p['product_type'],
                'status'   => (string) $p['product_status'],
                'title'    => mb_substr( (string) $p['title'], 0, 40 ),
                'price'    => number_format( (float) $p['price'], 2 ),
                'currency' => (string) $p['currency'],
                'linked'   => (string) $p['linked_object_type'] . '#' . (string) $p['linked_object_id'],
            ];
        }

        \WP_CLI\Utils\format_items( 'table', $rows, [ 'id', 'type', 'status', 'title', 'price', 'currency', 'linked' ] );
    }

    /**
     * 同步内容为商品
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function product_sync( $args, $assoc_args ): void {
        $chapters = get_posts(
            [
                'post_type'      => 'slv_chapter',
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids',
            ]
        );

        $collections = get_posts(
            [
                'post_type'      => 'slv_collection',
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids',
            ]
        );

        $chapter_count = 0;

        foreach ( $chapters as $id ) {
            $post = get_post( (int) $id );
            if ( $post ) {
                slv_commerce_sync_chapter_product( (int) $id, $post, true );
                $chapter_count++;
            }
        }

        $collection_count = 0;

        foreach ( $collections as $id ) {
            $post = get_post( (int) $id );
            if ( $post ) {
                slv_commerce_sync_collection_product( (int) $id, $post, true );
                $collection_count++;
            }
        }

        WP_CLI::success(
            sprintf(
                '同步完成：章节 %d 个，合集 %d 个。',
                $chapter_count,
                $collection_count
            )
        );
    }

    /**
     * 列出订单
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function order_list( $args, $assoc_args ): void {
        $query = [
            'limit'  => isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : 20,
            'status' => isset( $assoc_args['status'] ) ? (string) $assoc_args['status'] : '',
        ];

        if ( isset( $assoc_args['user'] ) ) {
            $user = $this->resolve_user( (string) $assoc_args['user'] );
            if ( $user ) {
                $query['user_id'] = (int) $user->ID;
            }
        }

        $orders = slv_order_list( $query );

        if ( empty( $orders ) ) {
            WP_CLI::warning( '无订单。' );
            return;
        }

        $rows = [];

        foreach ( $orders as $o ) {
            $rows[] = [
                'id'       => (string) $o['id'],
                'number'   => (string) $o['order_number'],
                'user_id'  => (string) $o['user_id'],
                'status'   => (string) $o['status'],
                'total'    => number_format( (float) $o['total'], 2 ),
                'currency' => (string) $o['currency'],
                'gateway'  => (string) $o['payment_gateway'],
                'created'  => (string) $o['created_at'],
            ];
        }

        \WP_CLI\Utils\format_items( 'table', $rows, [ 'id', 'number', 'user_id', 'status', 'total', 'currency', 'gateway', 'created' ] );
    }

    /**
     * 创建测试订单
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function order_create( $args, $assoc_args ): void {
        if ( count( $args ) < 2 ) {
            WP_CLI::error( '用法：wp slv order_create <user> <content_id> [--pay=false]' );
        }

        $user = $this->resolve_user( (string) $args[0] );

        if ( ! $user ) {
            WP_CLI::error( sprintf( '用户不存在：%s', $args[0] ) );
        }

        $content_id = (int) $args[1];
        $post       = get_post( $content_id );

        if ( ! $post ) {
            WP_CLI::error( sprintf( '内容不存在：%d', $content_id ) );
        }

        $content_type = 'slv_chapter' === $post->post_type ? 'chapter' : 'collection';

        $product = slv_product_get_by_linked_object( $content_type, $content_id );

        if ( null === $product ) {
            WP_CLI::error( '内容未关联商品，请先执行 wp slv product_sync。' );
        }

        // 切换用户上下文
        wp_set_current_user( (int) $user->ID );

        // 清空购物车 + 添加
        slv_cart_clear();
        slv_cart_add( (int) $product['id'], 1 );

        // 创建订单
        $result = slv_checkout_create_order( [] );

        if ( ! $result['success'] ) {
            wp_set_current_user( 0 );
            WP_CLI::error( $result['message'] );
        }

        $order_id = (int) $result['order_id'];

        WP_CLI::log( sprintf( '订单已创建，ID: %d', $order_id ) );

        slv_cart_clear();

        $do_pay = ! isset( $assoc_args['pay'] ) || 'false' !== (string) $assoc_args['pay'];

        if ( $do_pay ) {
            $pay_result = slv_checkout_pay_order( $order_id, 'manual' );

            wp_set_current_user( 0 );

            if ( ! $pay_result['success'] ) {
                WP_CLI::error( $pay_result['message'] );
            }

            WP_CLI::success( sprintf( '订单 #%d 已完成支付。', $order_id ) );
        } else {
            wp_set_current_user( 0 );
            WP_CLI::success( sprintf( '订单 #%d 已创建，等待支付。', $order_id ) );
        }
    }

    /**
     * 支付订单
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function order_pay( $args, $assoc_args ): void {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( '用法：wp slv order_pay <order_id> [--gateway=manual]' );
        }

        $order_id = (int) $args[0];
        $gateway  = isset( $assoc_args['gateway'] ) ? (string) $assoc_args['gateway'] : 'manual';

        $order = slv_order_get( $order_id );

        if ( null === $order ) {
            WP_CLI::error( sprintf( '订单不存在：%d', $order_id ) );
        }

        wp_set_current_user( (int) $order['user_id'] );

        $result = slv_checkout_pay_order( $order_id, $gateway );

        wp_set_current_user( 0 );

        if ( ! $result['success'] ) {
            WP_CLI::error( $result['message'] );
        }

        WP_CLI::success( $result['message'] );
    }

    /**
     * 生成测试数据
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function seed( $args, $assoc_args ): void {
        $collection_id = $this->create_collection();

        if ( ! $collection_id ) {
            WP_CLI::error( '创建合集失败。' );
        }

        WP_CLI::log( sprintf( '合集已创建，ID: %d', $collection_id ) );

        $chapters = [
            [ 'number' => '00', 'slug' => 'chapter-00-preface',     'title' => '前言：代码越来越便宜之后', 'access' => 'public',   'content' => $this->sample_content( 'preface' ) ],
            [ 'number' => '01', 'slug' => 'chapter-01-what-changed', 'title' => 'AI Coding 改变了什么',     'access' => 'public',   'content' => $this->sample_content( 'ch01' ) ],
            [ 'number' => '02', 'slug' => 'chapter-02-constraints',  'title' => '约束工程的三个层次',       'access' => 'purchase', 'price' => 9.90,  'content' => $this->sample_content( 'ch02' ) ],
            [ 'number' => '03', 'slug' => 'chapter-03-context',      'title' => '从提示到上下文',           'access' => 'member',   'content' => $this->sample_content( 'ch03' ) ],
            [ 'number' => '04', 'slug' => 'chapter-04-modeling',     'title' => '约束的建模方法',           'access' => 'purchase', 'price' => 12.00, 'content' => $this->sample_content( 'ch04' ) ],
        ];

        foreach ( $chapters as $ch ) {
            $chapter_id = $this->create_chapter( $collection_id, $ch );

            if ( $chapter_id ) {
                WP_CLI::log( sprintf( '  章节 %s 已创建，ID: %d', $ch['number'], $chapter_id ) );
            }
        }

        WP_CLI::success( '测试数据生成完成。' );
    }

    /**
     * 列出所有用户
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function list_users( $args, $assoc_args ): void {
        $users = get_users( [ 'number' => 100 ] );

        if ( empty( $users ) ) {
            WP_CLI::warning( '没有用户。' );
            return;
        }

        $rows = [];

        foreach ( $users as $u ) {
            $roles = is_array( $u->roles ) ? implode( ', ', $u->roles ) : '';

            if ( is_super_admin( $u->ID ) ) {
                $roles = '' === $roles ? 'super_admin' : $roles . ', super_admin';
            }

            $rows[] = [
                'id'           => (string) $u->ID,
                'login'        => (string) $u->user_login,
                'email'        => (string) $u->user_email,
                'display_name' => (string) $u->display_name,
                'roles'        => $roles,
            ];
        }

        \WP_CLI\Utils\format_items( 'table', $rows, [ 'id', 'login', 'email', 'display_name', 'roles' ] );
    }

    /**
     * 列出会员等级
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function list_levels( $args, $assoc_args ): void {
        $levels = slv_get_member_levels( false );

        if ( empty( $levels ) ) {
            WP_CLI::warning( '没有会员等级。' );
            return;
        }

        $rows = [];

        foreach ( $levels as $level ) {
            $rows[] = [
                'order'      => (string) $level['level_order'],
                'slug'       => (string) $level['level_slug'],
                'name'       => (string) $level['level_name'],
                'spent'      => (string) $level['required_spent'],
                'points'     => (string) $level['required_points'],
                'discount'   => (string) $level['discount_rate'] . '%',
                'multiplier' => (string) $level['points_multiplier'],
                'active'     => 1 === (int) $level['is_active'] ? 'yes' : 'no',
            ];
        }

        \WP_CLI\Utils\format_items( 'table', $rows, [ 'order', 'slug', 'name', 'spent', 'points', 'discount', 'multiplier', 'active' ] );
    }

    /**
     * 模拟用户购买
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function purchase( $args, $assoc_args ): void {
        if ( count( $args ) < 3 ) {
            WP_CLI::error( '用法：wp slv purchase <user> <object_type> <object_id>' );
        }

        $user = $this->resolve_user( (string) $args[0] );

        if ( ! $user ) {
            WP_CLI::error( sprintf( '用户不存在：%s', $args[0] ) );
        }

        $object_type = (string) $args[1];
        $object_id   = (int) $args[2];

        if ( ! in_array( $object_type, [ 'chapter', 'collection' ], true ) ) {
            WP_CLI::error( 'object_type 必须是 chapter 或 collection。' );
        }

        $object = get_post( $object_id );

        if ( ! $object ) {
            WP_CLI::error( sprintf( '内容不存在：%d', $object_id ) );
        }

        $result = slv_simulate_purchase( (int) $user->ID, $object_type, $object_id );

        if ( ! $result ) {
            WP_CLI::error( '模拟购买失败。' );
        }

        WP_CLI::success(
            sprintf(
                '用户 %s 已购买 %s #%d（%s）',
                $user->user_login,
                $object_type,
                $object_id,
                $object->post_title
            )
        );
    }

    /**
     * 查看用户状态
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function user_info( $args, $assoc_args ): void {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( '用法：wp slv user_info <user>' );
        }

        $user = $this->resolve_user( (string) $args[0] );

        if ( ! $user ) {
            WP_CLI::error( sprintf( '用户不存在：%s', $args[0] ) );
        }

        $uid        = (int) $user->ID;
        $membership = slv_get_user_membership( $uid );

        $rows = [
            [ 'field' => '用户ID',   'value' => (string) $uid ],
            [ 'field' => '登录名',   'value' => (string) $user->user_login ],
            [ 'field' => '会员等级', 'value' => $membership ? (string) $membership['level_name'] : '普通用户' ],
            [ 'field' => '积分余额', 'value' => (string) slv_get_user_points( $uid ) ],
            [ 'field' => '累计消费', 'value' => (string) slv_get_user_total_spent( $uid ) ],
            [ 'field' => '累计积分', 'value' => (string) slv_get_user_total_points_earned( $uid ) ],
            [ 'field' => '已购章节', 'value' => (string) slv_count_user_purchases( $uid, 'chapter' ) ],
            [ 'field' => '已购合集', 'value' => (string) slv_count_user_purchases( $uid, 'collection' ) ],
        ];

        \WP_CLI\Utils\format_items( 'table', $rows, [ 'field', 'value' ] );
    }

    /**
     * 检查内容访问状态
     *
     * @param array $args       位置参数
     * @param array $assoc_args 关联参数
     *
     * @return void
     */
    public function access( $args, $assoc_args ): void {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( '用法：wp slv access <post_id> [--user=<user>]' );
        }

        $post_id = (int) $args[0];
        $post    = get_post( $post_id );

        if ( ! $post ) {
            WP_CLI::error( sprintf( '内容不存在：%d', $post_id ) );
        }

        $user_id = 0;

        if ( isset( $assoc_args['user'] ) ) {
            $user = $this->resolve_user( (string) $assoc_args['user'] );

            if ( ! $user ) {
                WP_CLI::error( sprintf( '用户不存在：%s', $assoc_args['user'] ) );
            }

            $user_id = (int) $user->ID;
        }

        $config = slv_get_chapter_access_config( $post_id );
        $state  = slv_get_access_state( $post_id, $user_id );
        $can    = slv_can_access( $post_id, $user_id );

        $product = slv_product_get_by_linked_object( 'slv_chapter' === $post->post_type ? 'chapter' : 'collection', $post_id );

        $rows = [
            [ 'field' => '内容ID',   'value' => (string) $post_id ],
            [ 'field' => '标题',     'value' => (string) $post->post_title ],
            [ 'field' => '访问类型', 'value' => (string) $config['type'] ],
            [ 'field' => '价格',     'value' => (string) $config['price'] ],
            [ 'field' => '商品ID',   'value' => $product ? (string) $product['id'] : '—' ],
            [ 'field' => '用户ID',   'value' => (string) $user_id ],
            [ 'field' => '访问状态', 'value' => $state ],
            [ 'field' => '可访问',   'value' => $can ? 'yes' : 'no' ],
        ];

        \WP_CLI\Utils\format_items( 'table', $rows, [ 'field', 'value' ] );
    }

    /**
     * 解析用户
     *
     * @param string $input 用户ID或登录名或邮箱
     *
     * @return WP_User|null
     */
    private function resolve_user( string $input ): ?WP_User {
        if ( '' === $input ) {
            return null;
        }

        if ( ctype_digit( $input ) ) {
            $user = get_user_by( 'ID', (int) $input );
            if ( $user ) {
                return $user;
            }
        }

        $user = get_user_by( 'login', $input );
        if ( $user ) {
            return $user;
        }

        $user = get_user_by( 'email', $input );

        return $user ?: null;
    }

    /**
     * 创建一个测试合集
     *
     * @return int
     */
    private function create_collection(): int {
        $existing = get_page_by_path( 'ai-coding-methodology', OBJECT, 'slv_collection' );

        if ( $existing ) {
            WP_CLI::log( '合集已存在，复用。' );
            return (int) $existing->ID;
        }

        $id = wp_insert_post(
            [
                'post_type'    => 'slv_collection',
                'post_status'  => 'publish',
                'post_title'   => '从代码生成到约束工程',
                'post_name'    => 'ai-coding-methodology',
                'post_excerpt' => '面向 AI 原生软件工程从业者的方法论合集。',
                'post_content' => '本合集系统性地阐述"代码是结果，约束才是生产系统"这一核心命题。',
            ],
            true
        );

        if ( is_wp_error( $id ) ) {
            return 0;
        }

        update_post_meta( $id, '_slv_access_mode', 'mixed' );
        update_post_meta( $id, '_slv_collection_price', 99.00 );
        wp_set_object_terms( $id, 'methodology', 'slv_collection_category' );

        return (int) $id;
    }

    /**
     * 创建章节
     *
     * @param int   $collection_id 合集ID
     * @param array $data          章节数据
     *
     * @return int
     */
    private function create_chapter( int $collection_id, array $data ): int {
        $slug = $data['slug'];

        $existing = get_page_by_path( $slug, OBJECT, 'slv_chapter' );

        if ( $existing ) {
            return (int) $existing->ID;
        }

        $id = wp_insert_post(
            [
                'post_type'    => 'slv_chapter',
                'post_status'  => 'publish',
                'post_title'   => $data['title'],
                'post_name'    => $slug,
                'post_content' => $data['content'],
                'post_excerpt' => mb_substr( wp_strip_all_tags( $data['content'] ), 0, 100 ) . '…',
            ],
            true
        );

        if ( is_wp_error( $id ) ) {
            return 0;
        }

        update_post_meta( $id, '_slv_parent_collection_id', $collection_id );
        update_post_meta( $id, '_slv_chapter_number', $data['number'] );
        update_post_meta( $id, '_slv_access_type', $data['access'] );

        if ( 'purchase' === $data['access'] ) {
            if ( isset( $data['price'] ) ) {
                update_post_meta( $id, '_slv_access_price', (float) $data['price'] );
            }

            update_post_meta( $id, '_slv_preview_type', 'paragraphs' );
            update_post_meta( $id, '_slv_preview_value', 2 );
        }

        wp_set_object_terms( $id, 'section-1', 'slv_chapter_section' );

        slv_content_sync_chapter_meta( $id, get_post( $id ), false );

        return (int) $id;
    }

    /**
     * 生成示例内容
     *
     * @param string $key 内容键
     *
     * @return string
     */
    private function sample_content( string $key ): string {
        $samples = [
            'preface' => '<p>代码正在变得越来越便宜。</p><p>这不是一个技术判断，而是一个经济判断。当生成一段代码的边际成本趋近于零时，问题就不再是"如何写出代码"，而是"如何约束生成过程"。</p><p>本合集讨论的，是这个转变带来的全部工程问题。</p>',
            'ch01'    => '<p>AI Coding 带来的变化，不是"写代码更快了"，而是"代码不再是瓶颈"。</p><p>当瓶颈从"写"转移到"判断"时，整个软件开发的生产函数就变了。</p><p>过去十年积累的工程实践，多数都建立在"写代码很贵"这个前提之上。前提变了，实践也要变。</p>',
            'ch02'    => '<p>约束工程有三个层次：语法约束、结构约束、语义约束。</p><p>语法约束解决"能不能编译"，结构约束解决"能不能维护"，语义约束解决"能不能满足意图"。</p><p>多数团队只做到第一层，然后抱怨 AI 生成的代码不可用。</p>',
            'ch03'    => '<p>从提示工程到上下文工程，是一次范式跃迁。</p><p>提示工程关注"如何问"，上下文工程关注"给什么"。</p><p>当模型能力足够强时，问题几乎总是出在上下文，而不是提示。</p>',
            'ch04'    => '<p>约束需要建模。没有模型的约束是碎片化的规则集合。</p><p>约束模型的核心是：明确什么必须为真，什么可以变化，什么必须不出现。</p><p>这三者构成了一个完整的工程契约。</p>',
        ];

        return $samples[ $key ] ?? '<p>示例内容。</p>';
    }
}

WP_CLI::add_command( 'slv', 'SLV_CLI_Command' );