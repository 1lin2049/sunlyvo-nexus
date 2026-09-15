<?php
/**
 * 访问控制
 *
 * 统一管理内容访问权限判断。所有访问判断必须走 slv_can_access()，
 * 禁止在模板或业务代码中直接读 meta 判断。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取所有访问类型定义
 *
 * @since 1.0.0
 * @return array<string, string>
 */
function slv_get_access_types(): array {
    return [
        'public'     => esc_html__( '公开（无需登录）', 'sunlyvo-nexus' ),
        'member'     => esc_html__( '需登录', 'sunlyvo-nexus' ),
        'subscriber' => esc_html__( '需订阅', 'sunlyvo-nexus' ),
        'level'      => esc_html__( '需会员等级', 'sunlyvo-nexus' ),
        'purchase'   => esc_html__( '需购买', 'sunlyvo-nexus' ),
    ];
}

/**
 * 获取所有试读类型定义
 *
 * @since 1.0.0
 * @return array<string, string>
 */
function slv_get_preview_types(): array {
    return [
        'none'       => esc_html__( '无试读', 'sunlyvo-nexus' ),
        'percent'    => esc_html__( '按百分比试读', 'sunlyvo-nexus' ),
        'words'      => esc_html__( '按字数试读', 'sunlyvo-nexus' ),
        'paragraphs' => esc_html__( '按段落试读', 'sunlyvo-nexus' ),
    ];
}

/**
 * 获取章节的访问配置
 *
 * @since 1.0.0
 *
 * @param int $chapter_id 章节ID
 *
 * @return array{
 *     type:string,
 *     price:float,
 *     points:int,
 *     required_level:string,
 *     preview_type:string,
 *     preview_value:int,
 *     download_limit:int,
 *     expiry_days:int
 * }
 */
function slv_get_chapter_access_config( int $chapter_id ): array {
    $config = [
        'type'           => (string) get_post_meta( $chapter_id, '_slv_access_type', true ),
        'price'          => (float) get_post_meta( $chapter_id, '_slv_access_price', true ),
        'points'         => (int) get_post_meta( $chapter_id, '_slv_access_points', true ),
        'required_level' => (string) get_post_meta( $chapter_id, '_slv_access_required_level', true ),
        'preview_type'   => (string) get_post_meta( $chapter_id, '_slv_preview_type', true ),
        'preview_value'  => (int) get_post_meta( $chapter_id, '_slv_preview_value', true ),
        'download_limit' => (int) get_post_meta( $chapter_id, '_slv_download_limit', true ),
        'expiry_days'    => (int) get_post_meta( $chapter_id, '_slv_expiry_days', true ),
    ];

    if ( '' === $config['type'] || ! array_key_exists( $config['type'], slv_get_access_types() ) ) {
        $config['type'] = 'public';
    }

    if ( '' === $config['preview_type'] || ! array_key_exists( $config['preview_type'], slv_get_preview_types() ) ) {
        $config['preview_type'] = 'none';
    }

    /**
     * 过滤章节访问配置
     *
     * @since 1.0.0
     *
     * @param array $config     访问配置
     * @param int   $chapter_id 章节ID
     */
    return (array) apply_filters( 'slv_chapter_access_config', $config, $chapter_id );
}

/**
 * 判断用户能否访问指定内容
 *
 * 所有访问判断的唯一入口。
 *
 * @since 1.0.0
 *
 * @param int $post_id 内容ID
 * @param int $user_id 用户ID，0 表示当前用户
 *
 * @return bool
 */
function slv_can_access( int $post_id, int $user_id = 0 ): bool {
    if ( $post_id <= 0 ) {
        return false;
    }

    $post = get_post( $post_id );

    if ( ! $post ) {
        return false;
    }

    // 非受限内容类型（post / page）默认公开
    if ( ! in_array( $post->post_type, [ 'slv_chapter', 'slv_collection' ], true ) ) {
        return true;
    }

    $config = slv_get_chapter_access_config( $post_id );

    // 公开内容，直接放行
    if ( 'public' === $config['type'] ) {
        return true;
    }

    // 未登录，直接拒绝
    if ( $user_id <= 0 ) {
        $user_id = get_current_user_id();
    }

    if ( $user_id <= 0 ) {
        return false;
    }

    // 管理员与作者放行
    if ( user_can( $user_id, 'manage_options' ) ) {
        return true;
    }

    if ( (int) $post->post_author === $user_id ) {
        return true;
    }

    // 用户已购买该章节
    if ( slv_user_has_purchased_chapter( $user_id, $post_id ) ) {
        return true;
    }

    // 用户已购买所属合集
    $collection_id = slv_get_chapter_collection_id( $post_id );

    if ( $collection_id > 0 && slv_user_has_purchased_collection( $user_id, $collection_id ) ) {
        return true;
    }

    // 会员等级满足
    if ( 'level' === $config['type'] && '' !== $config['required_level'] ) {
        if ( slv_user_meets_level( $user_id, $config['required_level'] ) ) {
            return true;
        }
    }

    // 订阅有效
    if ( 'subscriber' === $config['type'] ) {
        if ( slv_user_has_active_subscription( $user_id ) ) {
            return true;
        }
    }

    // 登录即可（member 类型）
    if ( 'member' === $config['type'] ) {
        return true;
    }

    /**
     * 过滤最终访问判断结果
     *
     * 允许模块（如分销、邀请码、限时活动）注入额外放行逻辑。
     *
     * @since 1.0.0
     *
     * @param bool  $allowed 当前判断结果
     * @param int   $post_id 内容ID
     * @param int   $user_id 用户ID
     * @param array $config  访问配置
     */
    return (bool) apply_filters( 'slv_can_access', false, $post_id, $user_id, $config );
}

/**
 * 获取用户对指定内容的访问状态
 *
 * 用于前台展示"公开 / 已购 / 会员 / 需登录 / 已锁定"等状态。
 *
 * @since 1.0.0
 *
 * @param int $post_id 内容ID
 * @param int $user_id 用户ID
 *
 * @return string public | purchased | member | preview | login_required | locked
 */
function slv_get_access_state( int $post_id, int $user_id = 0 ): string {
    if ( $post_id <= 0 ) {
        return 'locked';
    }

    $config = slv_get_chapter_access_config( $post_id );

    if ( 'public' === $config['type'] ) {
        return 'public';
    }

    if ( $user_id <= 0 ) {
        $user_id = get_current_user_id();
    }

    if ( $user_id <= 0 ) {
        return 'login_required';
    }

    if ( slv_can_access( $post_id, $user_id ) ) {
        if ( slv_user_has_purchased_chapter( $user_id, $post_id ) ) {
            return 'purchased';
        }

        $collection_id = slv_get_chapter_collection_id( $post_id );

        if ( $collection_id > 0 && slv_user_has_purchased_collection( $user_id, $collection_id ) ) {
            return 'purchased';
        }

        if ( 'member' === $config['type'] ) {
            return 'member';
        }

        if ( 'level' === $config['type'] || 'subscriber' === $config['type'] ) {
            return 'member';
        }

        return 'purchased';
    }

    return 'locked';
}

/**
 * 获取访问状态的展示标签
 *
 * @since 1.0.0
 *
 * @param string $state 访问状态
 *
 * @return string
 */
function slv_get_access_state_label( string $state ): string {
    $labels = [
        'public'         => esc_html__( '公开', 'sunlyvo-nexus' ),
        'purchased'      => esc_html__( '已购', 'sunlyvo-nexus' ),
        'member'         => esc_html__( '会员可读', 'sunlyvo-nexus' ),
        'preview'        => esc_html__( '试读', 'sunlyvo-nexus' ),
        'login_required' => esc_html__( '需登录', 'sunlyvo-nexus' ),
        'locked'         => esc_html__( '需购买', 'sunlyvo-nexus' ),
    ];

    return $labels[ $state ] ?? $labels['locked'];
}

/**
 * 获取访问类型的展示标签
 *
 * @since 1.0.0
 *
 * @param string $type 访问类型
 *
 * @return string
 */
function slv_get_access_type_label( string $type ): string {
    $types = slv_get_access_types();

    return $types[ $type ] ?? $types['public'];
}

/**
 * 判断用户是否已购买章节
 *
 * M2 阶段：始终返回 false。M3 阶段接入 WooCommerce 后实现。
 *
 * @since 1.0.0
 *
 * @param int $user_id    用户ID
 * @param int $chapter_id 章节ID
 *
 * @return bool
 */
function slv_user_has_purchased_chapter( int $user_id, int $chapter_id ): bool {
    if ( $user_id <= 0 || $chapter_id <= 0 ) {
        return false;
    }

    /**
     * 过滤章节购买判断
     *
     * M3 阶段 WooCommerce 模块通过此钩子实现真实判断。
     *
     * @since 1.0.0
     *
     * @param bool $purchased 是否已购买
     * @param int  $user_id   用户ID
     * @param int  $chapter_id 章节ID
     */
    return (bool) apply_filters( 'slv_user_has_purchased_chapter', false, $user_id, $chapter_id );
}

/**
 * 判断用户是否已购买合集
 *
 * M2 阶段：始终返回 false。M3 阶段实现。
 *
 * @since 1.0.0
 *
 * @param int $user_id       用户ID
 * @param int $collection_id 合集ID
 *
 * @return bool
 */
function slv_user_has_purchased_collection( int $user_id, int $collection_id ): bool {
    if ( $user_id <= 0 || $collection_id <= 0 ) {
        return false;
    }

    /**
     * 过滤合集购买判断
     *
     * @since 1.0.0
     *
     * @param bool $purchased   是否已购买
     * @param int  $user_id     用户ID
     * @param int  $collection_id 合集ID
     */
    return (bool) apply_filters( 'slv_user_has_purchased_collection', false, $user_id, $collection_id );
}

/**
 * 判断用户是否满足指定会员等级
 *
 * M2 阶段：始终返回 false。M3 阶段接入会员模块后实现。
 *
 * @since 1.0.0
 *
 * @param int    $user_id 用户ID
 * @param string $level   等级 slug
 *
 * @return bool
 */
function slv_user_meets_level( int $user_id, string $level ): bool {
    if ( $user_id <= 0 || '' === $level ) {
        return false;
    }

    /**
     * 过滤会员等级判断
     *
     * @since 1.0.0
     *
     * @param bool   $meets   是否满足
     * @param int    $user_id 用户ID
     * @param string $level   等级 slug
     */
    return (bool) apply_filters( 'slv_user_meets_level', false, $user_id, $level );
}

/**
 * 判断用户订阅是否有效
 *
 * M2 阶段：始终返回 false。M3 阶段接入订阅模块后实现。
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return bool
 */
function slv_user_has_active_subscription( int $user_id ): bool {
    if ( $user_id <= 0 ) {
        return false;
    }

    /**
     * 过滤订阅状态判断
     *
     * @since 1.0.0
     *
     * @param bool $active  是否有效
     * @param int  $user_id 用户ID
     */
    return (bool) apply_filters( 'slv_user_has_active_subscription', false, $user_id );
}

/**
 * 按试读设置截断内容
 *
 * @since 1.0.0
 *
 * @param string $content  原始内容
 * @param int    $post_id  内容ID
 *
 * @return string 截断后的内容
 */
function slv_apply_preview_limit( string $content, int $post_id ): string {
    $config = slv_get_chapter_access_config( $post_id );

    if ( 'none' === $config['preview_type'] ) {
        return '';
    }

    if ( $config['preview_value'] <= 0 ) {
        return '';
    }

    switch ( $config['preview_type'] ) {
        case 'words':
            return wp_trim_words( $content, $config['preview_value'], '…' );

        case 'paragraphs':
            $paragraphs = preg_split( '/\n\s*\n/', trim( $content ) );
            if ( ! is_array( $paragraphs ) ) {
                return '';
            }
            $paragraphs = array_slice( $paragraphs, 0, $config['preview_value'] );
            return implode( "\n\n", $paragraphs );

        case 'percent':
            $total = mb_strlen( wp_strip_all_tags( $content ) );
            if ( $total <= 0 ) {
                return '';
            }
            $limit = (int) ceil( $total * $config['preview_value'] / 100 );
            return wp_trim_words( wp_strip_all_tags( $content ), $limit, '…' );
    }

    return '';
}