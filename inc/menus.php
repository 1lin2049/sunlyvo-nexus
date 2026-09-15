<?php
/**
 * 菜单位置与主题支持
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'after_setup_theme', 'slv_setup_theme_supports', 5 );

/**
 * 主题支持声明
 *
 * @since 1.0.0
 * @return void
 */
function slv_setup_theme_supports(): void {
    // 菜单位置
    register_nav_menus(
        [
            'primary' => esc_html__( '主菜单', 'sunlyvo-nexus' ),
            'footer'  => esc_html__( '页脚菜单', 'sunlyvo-nexus' ),
        ]
    );

    // 主题支持
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [
        'height'      => 48,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    // 图片尺寸
    add_image_size( 'slv-card', 640, 360, true );
    add_image_size( 'slv-hero', 1600, 900, true );

    // 加载文本域
    load_theme_textdomain( 'sunlyvo-nexus', SLV_THEME_DIR . '/languages' );
}

add_action( 'widgets_init', 'slv_register_sidebars' );

/**
 * 注册小工具区域
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_sidebars(): void {
    register_sidebar(
        [
            'name'          => esc_html__( '主侧边栏', 'sunlyvo-nexus' ),
            'id'            => 'sidebar-primary',
            'description'   => esc_html__( '显示在博客和归档页右侧', 'sunlyvo-nexus' ),
            'before_widget' => '<div id="%1$s" class="slv-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="slv-widget__title">',
            'after_title'   => '</h3>',
        ]
    );

    register_sidebar(
        [
            'name'          => esc_html__( '章节目录', 'sunlyvo-nexus' ),
            'id'            => 'chapter-toc',
            'description'   => esc_html__( '显示在章节阅读页侧边', 'sunlyvo-nexus' ),
            'before_widget' => '<div id="%1$s" class="slv-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="slv-widget__title">',
            'after_title'   => '</h3>',
        ]
    );
}

add_filter( 'body_class', 'slv_body_class' );

/**
 * 添加 body class
 *
 * @since 1.0.0
 *
 * @param array<int, string> $classes 已有 class
 *
 * @return array<int, string>
 */
function slv_body_class( array $classes ): array {
    $classes[] = 'slv-theme';

    if ( is_singular( 'slv_chapter' ) ) {
        $classes[] = 'slv-single-chapter';
    } elseif ( is_singular( 'slv_collection' ) ) {
        $classes[] = 'slv-single-collection';
    } elseif ( is_front_page() ) {
        $classes[] = 'slv-front-page';
    }

    return $classes;
}