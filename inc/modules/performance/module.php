<?php
/**
 * 性能模块
 *
 * M1 阶段实现基础性能优化，后续阶段扩展
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 移除 emoji 脚本
add_action( 'init', 'slv_performance_disable_emojis' );

/**
 * 禁用 WordPress 默认 emoji 脚本
 *
 * @since 1.0.0
 * @return void
 */
function slv_performance_disable_emojis(): void {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}

// 移除 dns-prefetch 冗余
add_filter( 'emoji_svg_url', '__return_false' );

// M4 阶段实现：
// require_once __DIR__ . '/performance-cache.php';
// require_once __DIR__ . '/performance-lazy-load.php';
// require_once __DIR__ . '/performance-query-monitor.php';