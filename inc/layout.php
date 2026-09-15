<?php
/**
 * 布局系统
 *
 * 优先级：单篇 meta > 全站默认 > 硬编码默认
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取当前页面的布局 slug
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return string
 */
function slv_layout_get( int $post_id = 0 ): string {
    if ( $post_id <= 0 ) {
        $post_id = (int) get_the_ID();
    }

    // 1. 单篇 meta
    if ( $post_id > 0 ) {
        $meta = (string) get_post_meta( $post_id, '_slv_layout', true );

        if ( '' !== $meta && array_key_exists( $meta, SLV_LAYOUT_OPTIONS ) ) {
            return $meta;
        }
    }

    // 2. 全站默认
    $default = (string) slv_get_config( 'layout.site_default', SLV_LAYOUT_DEFAULT );

    if ( array_key_exists( $default, SLV_LAYOUT_OPTIONS ) ) {
        return $default;
    }

    // 3. 硬编码默认
    return SLV_LAYOUT_DEFAULT;
}

/**
 * 获取布局的 body class
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return string
 */
function slv_layout_body_class( int $post_id = 0 ): string {
    return 'slv-layout-' . slv_layout_get( $post_id );
}

/**
 * 获取自定义 vw（PC）
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return int
 */
function slv_layout_get_custom_vw( int $post_id = 0 ): int {
    if ( $post_id <= 0 ) {
        $post_id = (int) get_the_ID();
    }

    $meta = (int) get_post_meta( $post_id, '_slv_layout_custom_vw', true );

    if ( $meta > 0 ) {
        return max( 30, min( 100, $meta ) );
    }

    return (int) slv_get_config( 'layout.custom_vw_pc', 60 );
}

/**
 * 获取自定义 vw（移动端）
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return int
 */
function slv_layout_get_custom_vw_mobile( int $post_id = 0 ): int {
    if ( $post_id <= 0 ) {
        $post_id = (int) get_the_ID();
    }

    $meta = (int) get_post_meta( $post_id, '_slv_layout_custom_vw_mobile', true );

    if ( $meta > 0 ) {
        return max( 30, min( 100, $meta ) );
    }

    return (int) slv_get_config( 'layout.custom_vw_mobile', 95 );
}

/**
 * 输出自定义 vw 的内联样式
 *
 * 注意：不接受参数，内部通过 get_the_ID() 获取当前文章。
 * wp_head 钩子不传参，函数签名不能有必需参数。
 *
 * @since 1.0.0
 * @return void
 */
function slv_layout_output_custom_vw(): void {
    if ( ! is_singular() ) {
        return;
    }

    $post_id = (int) get_the_ID();

    if ( $post_id <= 0 ) {
        return;
    }

    if ( 'custom' !== slv_layout_get( $post_id ) ) {
        return;
    }

    $pc     = slv_layout_get_custom_vw( $post_id );
    $mobile = slv_layout_get_custom_vw_mobile( $post_id );

    printf(
        '<style id="slv-layout-custom-vw">.slv-layout-custom .slv-container { max-width: calc(%dvw + 48px); } @media (max-width: 900px) { .slv-layout-custom .slv-container { max-width: calc(%dvw + 32px); } }</style>',
        $pc,
        $mobile
    );
}
add_action( 'wp_head', 'slv_layout_output_custom_vw', 20, 0 );

/**
 * 添加 body class
 *
 * @since 1.0.0
 *
 * @param array<int, string> $classes 已有 class
 *
 * @return array<int, string>
 */
function slv_layout_filter_body_class( array $classes ): array {
    if ( is_singular() ) {
        $classes[] = slv_layout_body_class();
    }
    return $classes;
}
add_filter( 'body_class', 'slv_layout_filter_body_class' );