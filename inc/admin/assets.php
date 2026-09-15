<?php
/**
 * 后台资源加载
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_enqueue_scripts', 'slv_admin_enqueue_assets' );
add_action( 'admin_head', 'slv_admin_inline_styles' );

function slv_admin_enqueue_assets( string $hook ): void {
    if ( file_exists( SLV_THEME_DIR . '/assets/css/admin.css' ) ) {
        wp_enqueue_style( 'slv-admin', SLV_ASSETS_URI . '/css/admin.css', [], SLV_VERSION );
    }

    if ( 'toplevel_page_slv-dashboard' === $hook ) {
        if ( file_exists( SLV_THEME_DIR . '/assets/css/admin-dashboard.css' ) ) {
            wp_enqueue_style( 'slv-admin-dashboard', SLV_ASSETS_URI . '/css/admin-dashboard.css', [], SLV_VERSION );
        }
    }

    $commerce_hooks = [
        'toplevel_page_slv-orders',
        'slv-orders_page_slv-coupons',
        'slv-orders_page_slv-products',
        'slv-orders_page_slv-reports',
        'slv-orders_page_slv-withdrawals',
    ];

    if ( in_array( $hook, $commerce_hooks, true ) ) {
        if ( file_exists( SLV_THEME_DIR . '/assets/css/admin-commerce.css' ) ) {
            wp_enqueue_style( 'slv-admin-commerce', SLV_ASSETS_URI . '/css/admin-commerce.css', [], SLV_VERSION );
        }
        if ( file_exists( SLV_THEME_DIR . '/assets/js/admin-commerce.js' ) ) {
            wp_enqueue_script( 'slv-admin-commerce', SLV_ASSETS_URI . '/js/admin-commerce.js', [], SLV_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );
        }
    }

    // 编辑页布局 meta box
    if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        if ( file_exists( SLV_THEME_DIR . '/assets/js/admin-layout.js' ) ) {
            wp_enqueue_script( 'slv-admin-layout', SLV_ASSETS_URI . '/js/admin-layout.js', [], SLV_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );
        }
    }

    // SEO 页面
    if ( strpos( $hook, 'slv-seo' ) !== false ) {
        wp_enqueue_media();
        if ( file_exists( SLV_THEME_DIR . '/assets/css/admin-seo.css' ) ) {
            wp_enqueue_style( 'slv-admin-seo', SLV_ASSETS_URI . '/css/admin-seo.css', [], SLV_VERSION );
        }
        if ( file_exists( SLV_THEME_DIR . '/assets/js/admin-seo.js' ) ) {
            wp_enqueue_script( 'slv-admin-seo', SLV_ASSETS_URI . '/js/admin-seo.js', [ 'jquery' ], SLV_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );
        }
    }
}

function slv_admin_inline_styles(): void {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen ) return;

    $relevant = [ 'slv_chapter', 'edit-slv_chapter', 'slv_collection', 'edit-slv_collection' ];
    if ( ! in_array( $screen->id, $relevant, true ) ) return;
    ?>
    <style>
        .slv-meta-field { margin-bottom: 16px; }
        .slv-meta-field__label { display: block; font-weight: 600; margin-bottom: 4px; }
        .slv-meta-field__hint { color: #666; font-size: 12px; margin: 4px 0 0; }

        .slv-badge { display: inline-block; padding: 1px 8px; border-radius: 10px; font-size: 11px; line-height: 18px; white-space: nowrap; }
        .slv-badge--public      { background: #e6fff3; color: #00733d; }
        .slv-badge--member      { background: #e6f0ff; color: #004a99; }
        .slv-badge--subscriber  { background: #f3e6ff; color: #5c0099; }
        .slv-badge--level       { background: #fff0e6; color: #a34d00; }
        .slv-badge--purchase    { background: #fff3e6; color: #8a4a00; }

        .slv-access-panel { background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; padding: 16px; }
        .slv-access-row { display: grid; grid-template-columns: 140px 1fr; gap: 12px; align-items: start; padding: 10px 0; border-bottom: 1px solid #eee; }
        .slv-access-row:last-child { border-bottom: none; }
        .slv-access-label { font-weight: 600; padding-top: 6px; color: #1d2327; }
        .slv-access-field { display: flex; flex-direction: column; gap: 4px; }
        .slv-access-field--inline { flex-direction: row; flex-wrap: wrap; align-items: center; gap: 8px; }
        .slv-access-field--inline .slv-access-hint { flex-basis: 100%; }
        .slv-access-select { min-width: 160px; }
        .slv-access-input  { width: 120px; }
        .slv-access-hint { margin: 0; font-size: 12px; color: #666; line-height: 1.5; }
        .slv-access-row[data-access-for] { transition: opacity 0.2s ease; }
        .slv-access-row.is-hidden { display: none; }

        .slv-chapter-table { margin-top: 8px; }
        .slv-chapter-table th { font-weight: 600; }

        .slv-layout-custom { margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ddd; }
    </style>
    <?php
}