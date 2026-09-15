<?php
/**
 * 统计读取
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取文章统计
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return array<string, int|float>
 */
function slv_stats_get( int $post_id ): array {
    $default = [
        'views' => 0, 'reads' => 0, 'time_total' => 0,
        'p25' => 0, 'p50' => 0, 'p75' => 0, 'p100' => 0,
        'avg_scroll' => 0.0,
    ];

    if ( $post_id <= 0 ) return $default;

    $cache_key = 'slv_stats_' . $post_id;
    $cached    = wp_cache_get( $cache_key, SLV_CACHE_GROUP );

    if ( false !== $cached && is_array( $cached ) ) {
        return array_merge( $default, $cached );
    }

    global $wpdb;
    $row = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT views, reads, time_total, p25, p50, p75, p100, avg_scroll
             FROM ' . $wpdb->prefix . 'slv_post_stats WHERE post_id = %d',
            $post_id
        ),
        ARRAY_A
    );

    $data = $row ? array_merge( $default, [
        'views'      => (int) $row['views'],
        'reads'      => (int) $row['reads'],
        'time_total' => (int) $row['time_total'],
        'p25'        => (int) $row['p25'],
        'p50'        => (int) $row['p50'],
        'p75'        => (int) $row['p75'],
        'p100'       => (int) $row['p100'],
        'avg_scroll' => (float) $row['avg_scroll'],
    ] ) : $default;

    wp_cache_set( $cache_key, $data, SLV_CACHE_GROUP, SLV_QUERY_CACHE_TTL );

    return $data;
}

/**
 * 清除缓存
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return void
 */
function slv_stats_clear_cache( int $post_id ): void {
    wp_cache_delete( 'slv_stats_' . $post_id, SLV_CACHE_GROUP );
}