<?php
/**
 * 电商模块
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/product.php';
require_once __DIR__ . '/order.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/content-bridge.php';
require_once __DIR__ . '/payment-transaction.php';
require_once __DIR__ . '/gateways/gateway-base.php';
require_once __DIR__ . '/gateways/manual.php';
require_once __DIR__ . '/gateways/stripe.php';
require_once __DIR__ . '/checkout.php';
require_once __DIR__ . '/webhook.php';
require_once __DIR__ . '/purchase-writer.php';
require_once __DIR__ . '/coupon.php';
require_once __DIR__ . '/rest.php';
require_once __DIR__ . '/emails.php';
require_once __DIR__ . '/cron.php';
require_once __DIR__ . '/shortcodes.php';

// 后台管理（文件缺失时静默跳过）
if ( is_admin() ) {
    $slv_commerce_admin_files = [
        'admin.php',
        'admin-order-actions.php',
        'admin-coupons.php',
        'admin-products.php',
        'admin-reports.php',
    ];

    foreach ( $slv_commerce_admin_files as $slv_file ) {
        $slv_path = __DIR__ . '/' . $slv_file;

        if ( file_exists( $slv_path ) ) {
            require_once $slv_path;
        }
    }

    unset( $slv_commerce_admin_files, $slv_file, $slv_path );
}