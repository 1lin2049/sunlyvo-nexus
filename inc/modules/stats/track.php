<?php
/**
 * 统计埋点
 *
 * 事件类型：
 * - view      浏览（60 秒去重）
 * - read      深度阅读（30 分钟去重）
 * - progress  进度点（24 小时去重）
 * - time      停留时长（累加）
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 表名快捷方式
 *
 * @since 1.0.0
 * @return string
 */
function slv_stats_log_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_track_log';
}

/**
 * 处理埋点请求
 *
 * @since 1.0.0
 *
 * @param int    $post_id    文章ID
 * @param string $event      事件
 * @param int    $value      事件值
 * @param string $visitor_id 访客标识
 *
 * @return bool
 */
function slv_stats_handle_track( int $post_id, string $event, int $value = 0, string $visitor_id = '' ): bool {
    $post = get_post( $post_id );

    if ( ! $post ) {
        return false;
    }

    $allowed_events = [ 'view', 'read', 'progress', 'time' ];

    if ( ! in_array( $event, $allowed_events, true ) ) {
        return false;
    }

    $visitor_id = '' !== $visitor_id ? sanitize_text_field( $visitor_id ) : '';

    if ( '' === $visitor_id ) {
        return false;
    }

    $user_id = get_current_user_id();
    $ip_hash = slv_stats_hash_ip();

    // 去重
    if ( ! slv_stats_should_record( $post_id, $event, $visitor_id, $value ) ) {
        return false;
    }

    global $wpdb;

    // 写入原始日志
    $wpdb->insert(
        slv_stats_log_table(),
        [
            'post_id'    => $post_id,
            'event'      => $event,
            'value'      => $value,
            'visitor_id' => $visitor_id,
            'user_id'    => $user_id,
            'ip_hash'    => $ip_hash,
        ],
        [ '%d', '%s', '%d', '%s', '%d', '%s' ]
    );

    // 更新聚合表
    slv_stats_update_aggregate( $post_id, $event, $value );

    return true;
}

/**
 * 判断是否需要记录（去重）
 *
 * @since 1.0.0
 *
 * @param int    $post_id    文章ID
 * @param string $event      事件
 * @param string $visitor_id 访客标识
 * @param int    $value      事件值
 *
 * @return bool
 */
function slv_stats_should_record( int $post_id, string $event, string $visitor_id, int $value ): bool {
    global $wpdb;

    // time 事件不去重
    if ( 'time' === $event ) {
        return true;
    }

    // 去重窗口（秒）
    $windows = [
        'view'     => 60,
        'read'     => 1800,
        'progress' => 86400,
    ];

    $window = $windows[ $event ] ?? 60;
    $threshold = gmdate( 'Y-m-d H:i:s', time() - $window );

    // progress 事件额外按 value 区分（25/50/75/100）
    if ( 'progress' === $event ) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . slv_stats_log_table() . '
                 WHERE post_id = %d AND event = %s AND visitor_id = %s AND value = %d
                 AND created_at > %s
                 LIMIT 1',
                $post_id,
                $event,
                $visitor_id,
                $value,
                $threshold
            )
        );
    } else {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . slv_stats_log_table() . '
                 WHERE post_id = %d AND event = %s AND visitor_id = %s
                 AND created_at > %s
                 LIMIT 1',
                $post_id,
                $event,
                $visitor_id,
                $threshold
            )
        );
    }

    return null === $exists;
}

/**
 * IP 哈希
 *
 * @since 1.0.0
 * @return string
 */
function slv_stats_hash_ip(): string {
    $ip = '';

    if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $parts = explode( ',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'] );
        $ip    = trim( $parts[0] );
    } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = (string) $_SERVER['REMOTE_ADDR'];
    }

    if ( '' === $ip ) {
        return '';
    }

    $salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'slv-default-salt';

    return substr( hash( 'sha256', $ip . $salt ), 0, 32 );
}

/**
 * 更新聚合表
 *
 * @since 1.0.0
 *
 * @param int    $post_id 文章ID
 * @param string $event   事件
 * @param int    $value   事件值
 *
 * @return void
 */
function slv_stats_update_aggregate( int $post_id, string $event, int $value ): void {
    global $wpdb;

    $table     = $wpdb->prefix . 'slv_post_stats';
    $date      = gmdate( 'Y-m-d' );
    $daily     = $wpdb->prefix . 'slv_post_daily';

    // 确保存在行
    $exists = $wpdb->get_var(
        $wpdb->prepare( "SELECT id FROM {$table} WHERE post_id = %d", $post_id )
    );

    if ( null === $exists ) {
        $wpdb->insert( $table, [ 'post_id' => $post_id ], [ '%d' ] );
    }

    switch ( $event ) {
        case 'view':
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$table} SET views = views + 1 WHERE post_id = %d",
                $post_id
            ) );
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO {$daily} (post_id, stat_date, views) VALUES (%d, %s, 1)
                 ON DUPLICATE KEY UPDATE views = views + 1",
                $post_id,
                $date
            ) );
            break;

        case 'read':
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$table} SET reads = reads + 1 WHERE post_id = %d",
                $post_id
            ) );
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO {$daily} (post_id, stat_date, reads) VALUES (%d, %s, 1)
                 ON DUPLICATE KEY UPDATE reads = reads + 1",
                $post_id,
                $date
            ) );
            break;

        case 'progress':
            $bucket = in_array( $value, [ 25, 50, 75, 100 ], true ) ? 'p' . $value : '';

            if ( '' !== $bucket ) {
                $wpdb->query( $wpdb->prepare(
                    "UPDATE {$table} SET {$bucket} = {$bucket} + 1 WHERE post_id = %d",
                    $post_id
                ) );
            }
            break;

        case 'time':
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$table} SET time_total = time_total + %d WHERE post_id = %d",
                max( 0, min( 600, $value ) ),
                $post_id
            ) );
            break;
    }
}