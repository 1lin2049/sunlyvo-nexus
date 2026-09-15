<?php
/**
 * 电商定时任务
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'slv_commerce_schedule_cron' );
add_action( 'slv_commerce_daily_cleanup', 'slv_commerce_cleanup_expired_orders' );

/**
 * 注册定时任务
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_schedule_cron(): void {
    if ( ! wp_next_scheduled( 'slv_commerce_daily_cleanup' ) ) {
        wp_schedule_event( time(), 'daily', 'slv_commerce_daily_cleanup' );
    }
}

/**
 * 清理过期未付款订单
 *
 * 超过 24 小时仍为 pending_payment 的订单标记为 cancelled。
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_cleanup_expired_orders(): void {
    global $wpdb;

    $threshold = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );
    $table     = $wpdb->prefix . 'slv_orders';

    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT id FROM {$table}
             WHERE status = %s
             AND created_at < %s
             LIMIT 100",
            SLV_ORDER_STATUS_PENDING_PAYMENT,
            $threshold
        )
    );

    if ( ! is_array( $ids ) ) {
        return;
    }

    foreach ( $ids as $order_id ) {
        slv_order_update_status(
            (int) $order_id,
            SLV_ORDER_STATUS_CANCELLED,
            __( '超时未支付，自动取消', 'sunlyvo-nexus' )
        );
    }
}

add_action( 'init', 'slv_commerce_schedule_cart_cleanup' );
add_action( 'slv_commerce_cart_cleanup', 'slv_commerce_cleanup_old_carts' );

/**
 * 注册购物车清理任务
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_schedule_cart_cleanup(): void {
    if ( ! wp_next_scheduled( 'slv_commerce_cart_cleanup' ) ) {
        wp_schedule_event( time(), 'daily', 'slv_commerce_cart_cleanup' );
    }
}

/**
 * 清理陈旧购物车
 *
 * 超过 30 天未更新的购物车项。
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_cleanup_old_carts(): void {
    global $wpdb;

    $threshold = gmdate( 'Y-m-d H:i:s', time() - ( 30 * DAY_IN_SECONDS ) );
    $table     = $wpdb->prefix . 'slv_carts';

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table} WHERE updated_at < %s LIMIT 1000",
            $threshold
        )
    );
}