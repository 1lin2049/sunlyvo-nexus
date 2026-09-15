<?php
/**
 * 统计定时任务
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'slv_stats_schedule_cron' );
add_action( 'slv_stats_daily_cleanup', 'slv_stats_cleanup_old_logs' );

/**
 * 注册定时任务
 *
 * @since 1.0.0
 * @return void
 */
function slv_stats_schedule_cron(): void {
    if ( ! wp_next_scheduled( 'slv_stats_daily_cleanup' ) ) {
        wp_schedule_event( time(), 'daily', 'slv_stats_daily_cleanup' );
    }
}

/**
 * 清理 30 天前的日志
 *
 * @since 1.0.0
 * @return void
 */
function slv_stats_cleanup_old_logs(): void {
    global $wpdb;

    $threshold = gmdate( 'Y-m-d H:i:s', time() - ( 30 * DAY_IN_SECONDS ) );
    $table     = $wpdb->prefix . 'slv_track_log';

    // 单批最多 10000 条
    $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$table} WHERE created_at < %s LIMIT 10000",
        $threshold
    ) );

    // 表行数超 300 万时裁剪
    $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

    if ( $count > 3000000 ) {
        $wpdb->query( "DELETE FROM {$table} ORDER BY id ASC LIMIT 100000" );
    }
}