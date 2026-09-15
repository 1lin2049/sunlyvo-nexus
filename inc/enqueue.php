<?php
/**
 * 资源加载
 *
 * 两套独立链路：
 * - 主站：tokens → reset → components → main → header-footer → mobile
 * - Reader：ant-tokens → reset → reader-base → reader-extras（不加载 main.css）
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'slv_enqueue_frontend_assets', 10 );

/**
 * 加载前端资源
 *
 * @since 1.0.0
 * @return void
 */
function slv_enqueue_frontend_assets(): void {
    $version = SLV_VERSION;
    $is_reader = function_exists( 'slv_is_reader_context' ) && slv_is_reader_context();

    // Ant Design Token 层（所有页面共享）
    if ( file_exists( SLV_THEME_DIR . '/assets/css/ant-tokens.css' ) ) {
        wp_enqueue_style( 'slv-ant-tokens', SLV_ASSETS_URI . '/css/ant-tokens.css', [], $version );
    }

    if ( $is_reader ) {
        // === Reader 子系统链路 ===
        $reader_chain = [
            'tokens'        => [ 'slv-ant-tokens' ],
            'reset'         => [ 'slv-tokens' ],
            'reader-base'   => [ 'slv-reset' ],
            'reader-extras' => [ 'slv-reader-base' ],
        ];

        foreach ( $reader_chain as $slug => $deps ) {
            $file = SLV_THEME_DIR . "/assets/css/{$slug}.css";
            if ( ! file_exists( $file ) ) continue;

            $deps = array_values( array_filter( $deps, 'wp_style_is' ) );
            wp_enqueue_style( 'slv-' . $slug, SLV_ASSETS_URI . "/css/{$slug}.css", $deps, $version );
        }
    } else {
        // === 主站链路 ===
        $style_chain = [
            'tokens'        => [ 'slv-ant-tokens' ],
            'reset'         => [ 'slv-tokens' ],
            'components'    => [ 'slv-reset' ],
            'layout'        => [ 'slv-components' ],
            'header-footer' => [ 'slv-layout' ],
            'main'          => [ 'slv-header-footer' ],
            'comments'      => [ 'slv-main' ],
            'commerce'      => [ 'slv-comments' ],
            'affiliate'     => [ 'slv-commerce' ],
            'mobile'        => [ 'slv-affiliate' ],
            'dark-mode'     => [ 'slv-mobile' ],
        ];

        foreach ( $style_chain as $slug => $deps ) {
            $file = SLV_THEME_DIR . "/assets/css/{$slug}.css";
            if ( ! file_exists( $file ) ) continue;

            $deps = array_values( array_filter( $deps, 'wp_style_is' ) );
            wp_enqueue_style( 'slv-' . $slug, SLV_ASSETS_URI . "/css/{$slug}.css", $deps, $version );
        }
    }

    // 主脚本
    if ( file_exists( SLV_THEME_DIR . '/assets/js/main.js' ) ) {
        wp_enqueue_script( 'slv-main', SLV_ASSETS_URI . '/js/main.js', [], $version, [ 'strategy' => 'defer', 'in_footer' => true ] );
    }

    // Reader 子系统脚本
    if ( $is_reader && file_exists( SLV_THEME_DIR . '/assets/js/reader.js' ) ) {
        wp_enqueue_script( 'slv-reader', SLV_ASSETS_URI . '/js/reader.js', [], $version, [ 'strategy' => 'defer', 'in_footer' => true ] );
    }

    // 电商脚本
    if ( ! $is_reader && file_exists( SLV_THEME_DIR . '/assets/js/commerce.js' ) ) {
        wp_enqueue_script( 'slv-commerce', SLV_ASSETS_URI . '/js/commerce.js', [], $version, [ 'strategy' => 'defer', 'in_footer' => true ] );

        $cart_url = function_exists( 'slv_commerce_get_cart_url' ) ? slv_commerce_get_cart_url() : home_url( '/cart/' );
        wp_localize_script( 'slv-commerce', 'slvCommerce', [
            'restBase' => rest_url( SLV_REST_NAMESPACE ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'cartUrl'  => $cart_url,
        ] );
    }
}

add_action( 'enqueue_block_editor_assets', 'slv_enqueue_editor_assets' );

/**
 * 编辑器资源
 *
 * @since 1.0.0
 * @return void
 */
function slv_enqueue_editor_assets(): void {
    $path = SLV_THEME_DIR . '/assets/css/editor.css';
    if ( ! file_exists( $path ) ) return;
    wp_enqueue_style( 'slv-editor', SLV_ASSETS_URI . '/css/editor.css', [], SLV_VERSION );
}