<?php
/**
 * 安全模块
 *
 * M1 阶段实现基础安全加固，后续阶段扩展
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 移除 WordPress 版本号输出
add_filter( 'the_generator', '__return_empty_string' );

// 移除 X-Pingback 头
add_filter( 'wp_headers', 'slv_security_remove_pingback' );

/**
 * 移除 X-Pingback 响应头
 *
 * @since 1.0.0
 *
 * @param array $headers 响应头
 *
 * @return array
 */
function slv_security_remove_pingback( array $headers ): array {
    unset( $headers['X-Pingback'] );
    return $headers;
}

// 禁用 XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );

// M4 阶段实现：
// require_once __DIR__ . '/security-rate-limit.php';
// require_once __DIR__ . '/security-file-protection.php';
// require_once __DIR__ . '/security-gdpr.php';