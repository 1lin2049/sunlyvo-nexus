<?php
/**
 * 种子数据
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'after_switch_theme', 'slv_seed_default_data', 20 );

/**
 * 初始化所有种子数据
 *
 * @since 1.0.0
 * @return void
 */
function slv_seed_default_data(): void {
    slv_migrate_remove_chapter_access_taxonomy();
    slv_seed_default_config();
    slv_seed_default_config_permissions();
    slv_seed_default_field_definitions();
    slv_seed_default_taxonomy_terms();
    slv_seed_default_member_levels();
    slv_seed_commerce_pages();

    update_option( 'slv_seeded', SLV_VERSION );
}

/**
 * 创建电商页面
 *
 * @since 1.0.0
 * @return void
 */
function slv_seed_commerce_pages(): void {
    $pages = [
        [
            'title'    => '购物车',
            'slug'     => 'cart',
            'template' => 'page-templates/cart.php',
            'option'   => 'commerce.cart_page_id',
        ],
        [
            'title'    => '结算',
            'slug'     => 'checkout',
            'template' => 'page-templates/checkout.php',
            'option'   => 'commerce.checkout_page_id',
        ],
        [
            'title'    => '我的订单',
            'slug'     => 'my-orders',
            'template' => 'page-templates/my-orders.php',
            'option'   => 'commerce.my_orders_page_id',
        ],
        [
            'title'    => '订单详情',
            'slug'     => 'order-view',
            'template' => 'page-templates/order-view.php',
            'option'   => 'commerce.order_view_page_id',
        ],
        [
            'title'    => '用户中心',
            'slug'     => 'account',
            'template' => 'page-templates/account.php',
            'option'   => 'commerce.account_page_id',
        ],
    ];

    foreach ( $pages as $page ) {
        $existing = get_page_by_path( $page['slug'] );

        if ( $existing ) {
            // 确保模板正确
            update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
            update_option( $page['option'], (int) $existing->ID );
            continue;
        }

        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $page['title'],
                'post_name'    => $page['slug'],
                'post_content' => '',
            ]
        );

        if ( is_wp_error( $page_id ) || ! $page_id ) {
            continue;
        }

        update_post_meta( $page_id, '_wp_page_template', $page['template'] );
        update_option( $page['option'], (int) $page_id );
    }
}

/**
 * 清理废弃的 slv_chapter_access 分类法数据
 *
 * @since 1.0.0
 * @return void
 */
function slv_migrate_remove_chapter_access_taxonomy(): void {
    global $wpdb;

    $taxonomy_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT taxonomy FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s LIMIT 1",
            'slv_chapter_access'
        )
    );

    if ( null === $taxonomy_exists ) {
        return;
    }

    $rows = $wpdb->get_results(
        "SELECT tr.object_id AS post_id, t.slug AS term_slug
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         WHERE tt.taxonomy = 'slv_chapter_access'",
        ARRAY_A
    );

    foreach ( (array) $rows as $row ) {
        $post_id = (int) $row['post_id'];
        $type    = 'free' === $row['term_slug'] ? 'public' : 'purchase';

        if ( $post_id > 0 ) {
            update_post_meta( $post_id, '_slv_access_type', $type );
        }
    }

    $term_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s",
            'slv_chapter_access'
        )
    );

    if ( ! empty( $term_ids ) ) {
        $ids = implode( ',', array_map( 'intval', $term_ids ) );

        $wpdb->query( "DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (
            SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'slv_chapter_access'
        )" );

        $wpdb->query( "DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'slv_chapter_access'" );
        $wpdb->query( "DELETE FROM {$wpdb->terms} WHERE term_id IN ({$ids})" );
        $wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE term_id IN ({$ids})" );
    }

    wp_cache_flush();
}

/**
 * 初始化默认配置项
 *
 * @since 1.0.0
 * @return void
 */
function slv_seed_default_config(): void {
    global $wpdb;

    $configs = [
        [ 'general.currency',               'general',     'string', 'USD',    'USD',    '平台默认货币代码',          10 ],
        [ 'general.language',               'general',     'string', 'en_US',  'en_US',  '平台默认语言',              20 ],
        [ 'general.timezone',               'general',     'string', 'UTC',    'UTC',    '平台默认时区',              30 ],
        [ 'commission.default_rate',        'commission',  'float',  '10.00',  '10.00',  '默认佣金比例（%）',          10 ],
        [ 'commission.affiliate_rate',      'commission',  'float',  '5.00',   '5.00',   '分销佣金比例（%）',          20 ],
        [ 'points.enabled',                 'points',      'bool',   'true',   'true',   '是否启用积分系统',          10 ],
        [ 'points.earn_rate',               'points',      'float',  '1.00',   '1.00',   '每消费1单位货币获得的积分',  20 ],
        [ 'points.expire_days',             'points',      'int',    '365',    '365',    '积分过期天数',              30 ],
        [ 'membership.enabled',             'membership',  'bool',   'true',   'true',   '是否启用会员等级系统',      10 ],
        [ 'asset.default_access',           'asset',       'string', 'public', 'public', '数字资产默认访问类型',      10 ],
        [ 'asset.download_limit',           'asset',       'int',    '0',      '0',      '默认下载次数上限',          20 ],
        [ 'layout.site_default',            'layout',      'string', 'narrow', 'narrow', '全站默认布局',              10 ],
        [ 'collection.default_access_mode', 'collection',  'string', 'mixed',  'mixed',  '合集默认访问模式',          10 ],
        [ 'payment.stripe_secret_key',      'payment',     'string', '',       '',       'Stripe 密钥（secret）',     10 ],
        [ 'payment.stripe_publishable_key', 'payment',     'string', '',       '',       'Stripe 密钥（publishable）', 20 ],
        [ 'payment.stripe_webhook_secret',  'payment',     'string', '',       '',       'Stripe Webhook 密钥',       30 ],
    ];

    foreach ( $configs as $config ) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}slv_config_registry
                 WHERE config_key = %s AND owner_type = 'platform' AND owner_id = 0",
                $config[0]
            )
        );

        if ( $exists ) {
            continue;
        }

        $wpdb->insert(
            "{$wpdb->prefix}slv_config_registry",
            [
                'config_key'    => $config[0],
                'config_group'  => $config[1],
                'config_type'   => $config[2],
                'default_value' => $config[3],
                'config_value'  => $config[4],
                'owner_type'    => 'platform',
                'owner_id'      => 0,
                'description'   => $config[5],
                'sort_order'    => $config[6],
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d' ]
        );
    }
}

/**
 * 初始化默认配置权限
 *
 * @since 1.0.0
 * @return void
 */
function slv_seed_default_config_permissions(): void {
    global $wpdb;

    $permissions = [
        [ 'administrator',  'general',    1, 1, 1, 'all' ],
        [ 'administrator',  'commission', 1, 1, 1, 'all' ],
        [ 'administrator',  'points',     1, 1, 1, 'all' ],
        [ 'administrator',  'membership', 1, 1, 1, 'all' ],
        [ 'administrator',  'asset',      1, 1, 1, 'all' ],
        [ 'administrator',  'layout',     1, 1, 1, 'all' ],
        [ 'administrator',  'collection', 1, 1, 1, 'all' ],
        [ 'administrator',  'payment',    1, 1, 1, 'all' ],
        [ 'region_admin',   'general',    1, 1, 0, 'region' ],
        [ 'region_admin',   'commission', 1, 1, 0, 'region' ],
        [ 'region_admin',   'layout',     1, 1, 0, 'region' ],
        [ 'station_master', 'general',    1, 1, 0, 'station' ],
        [ 'station_master', 'commission', 1, 1, 0, 'station' ],
        [ 'station_master', 'layout',     1, 1, 0, 'station' ],
        [ 'vendor',         'commission', 1, 1, 0, 'own' ],
        [ 'vendor',         'asset',      1, 1, 0, 'own' ],
        [ 'vendor',         'collection', 1, 1, 0, 'own' ],
    ];

    foreach ( $permissions as $perm ) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}slv_config_permissions
                 WHERE role = %s AND config_group = %s",
                $perm[0],
                $perm[1]
            )
        );

        if ( $exists ) {
            continue;
        }

        $wpdb->insert(
            "{$wpdb->prefix}slv_config_permissions",
            [
                'role'         => $perm[0],
                'config_group' => $perm[1],
                'can_view'     => $perm[2],
                'can_edit'     => $perm[3],
                'can_lock'     => $perm[4],
                'scope'        => $perm[5],
            ],
            [ '%s', '%s', '%d', '%d', '%d', '%s' ]
        );
    }
}

/**
 * 初始化默认字段定义
 *
 * @since 1.0.0
 * @return void
 */
function slv_seed_default_field_definitions(): void {
    global $wpdb;

    $fields = [
        [ 'chapter_number',   'chapter',    'string', '章节序号',       1, 0, 10 ],
        [ 'chapter_words',    'chapter',    'int',    '字数',           0, 0, 20 ],
        [ 'chapter_time',     'chapter',    'int',    '预计阅读分钟',   0, 0, 30 ],
        [ 'access_type',      'access',     'string', '访问类型',       1, 0, 10 ],
        [ 'access_price',     'access',     'float',  '单章价格',       0, 0, 20 ],
        [ 'access_points',    'access',     'int',    '积分成本',       0, 0, 30 ],
        [ 'preview_type',     'access',     'string', '试读类型',       0, 0, 40 ],
        [ 'preview_value',    'access',     'int',    '试读值',         0, 0, 50 ],
        [ 'layout_override',  'layout',     'string', '布局覆盖',       0, 0, 10 ],
        [ 'collection_intro', 'collection', 'text',   '合集简介',       0, 0, 10 ],
    ];

    foreach ( $fields as $field ) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}slv_field_definitions WHERE field_key = %s",
                $field[0]
            )
        );

        if ( $exists ) {
            continue;
        }

        $wpdb->insert(
            "{$wpdb->prefix}slv_field_definitions",
            [
                'field_key'       => $field[0],
                'field_group'     => $field[1],
                'field_type'      => $field[2],
                'label'           => $field[3],
                'is_required'     => $field[4],
                'is_translatable' => $field[5],
                'sort_order'      => $field[6],
            ],
            [ '%s', '%s', '%s', '%s', '%d', '%d', '%d' ]
        );
    }
}

/**
 * 初始化默认分类法 term
 *
 * @since 1.0.0
 * @return void
 */
function slv_seed_default_taxonomy_terms(): void {
    $sections = [
        '第一部分 · 问题',
        '第二部分 · 范式',
        '第三部分 · 框架',
        '第四部分 · 实践',
        '附录',
    ];

    foreach ( $sections as $index => $name ) {
        if ( ! term_exists( $name, 'slv_chapter_section' ) ) {
            wp_insert_term( $name, 'slv_chapter_section', [ 'slug' => 'section-' . ( $index + 1 ) ] );
        }
    }

    if ( ! term_exists( 'methodology', 'slv_collection_category' ) ) {
        wp_insert_term( '方法论', 'slv_collection_category', [ 'slug' => 'methodology' ] );
    }
    if ( ! term_exists( 'engineering', 'slv_collection_category' ) ) {
        wp_insert_term( '工程实践', 'slv_collection_category', [ 'slug' => 'engineering' ] );
    }
}

/**
 * 初始化默认会员等级
 *
 * @since 1.0.0
 * @return void
 */
function slv_seed_default_member_levels(): void {
    global $wpdb;

    $levels = [
        [ 'slug' => 'bronze',   'name' => '青铜会员', 'order' => 1, 'spent' => 0,    'points' => 0,     'discount' => 0,  'multiplier' => 1.00, 'benefits' => '基础访问权限' ],
        [ 'slug' => 'silver',   'name' => '白银会员', 'order' => 2, 'spent' => 100,  'points' => 500,   'discount' => 5,  'multiplier' => 1.20, 'benefits' => '5% 折扣 · 积分 1.2 倍' ],
        [ 'slug' => 'gold',     'name' => '黄金会员', 'order' => 3, 'spent' => 500,  'points' => 2000,  'discount' => 10, 'multiplier' => 1.50, 'benefits' => '10% 折扣 · 积分 1.5 倍 · 专属内容' ],
        [ 'slug' => 'platinum', 'name' => '铂金会员', 'order' => 4, 'spent' => 2000, 'points' => 10000, 'discount' => 15, 'multiplier' => 2.00, 'benefits' => '15% 折扣 · 积分 2 倍 · 全部专属' ],
    ];

    foreach ( $levels as $level ) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}slv_member_levels WHERE level_slug = %s",
                $level['slug']
            )
        );

        if ( $exists ) {
            continue;
        }

        $wpdb->insert(
            "{$wpdb->prefix}slv_member_levels",
            [
                'level_name'        => $level['name'],
                'level_slug'        => $level['slug'],
                'level_order'       => $level['order'],
                'required_spent'    => $level['spent'],
                'required_points'   => $level['points'],
                'discount_rate'     => $level['discount'],
                'points_multiplier' => $level['multiplier'],
                'benefits'          => $level['benefits'],
                'is_active'         => 1,
            ],
            [ '%s', '%s', '%d', '%f', '%d', '%f', '%f', '%s', '%d' ]
        );
    }
}