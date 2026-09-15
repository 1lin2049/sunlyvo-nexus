<?php
/**
 * 统计 REST 端点
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rest_api_init', 'slv_stats_register_routes' );

/**
 * 注册路由
 *
 * @since 1.0.0
 * @return void
 */
function slv_stats_register_routes(): void {
    register_rest_route(
        SLV_REST_NAMESPACE,
        '/track',
        [
            'methods'             => 'POST',
            'callback'            => 'slv_stats_rest_track',
            'permission_callback' => '__return_true',
        ]
    );
}

/**
 * 处理埋点请求
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求
 *
 * @return WP_REST_Response
 */
function slv_stats_rest_track( WP_REST_Request $request ): WP_REST_Response {
    // 速率限制：每分钟 30 次
    if ( ! slv_stats_rate_limit() ) {
        return new WP_REST_Response( [ 'error' => 'Rate limited' ], 429 );
    }

    $body = $request->get_json_params();

    if ( ! is_array( $body ) ) {
        return new WP_REST_Response( [ 'error' => 'Invalid body' ], 400 );
    }

    $post_id    = isset( $body['post_id'] ) ? (int) $body['post_id'] : 0;
    $event      = isset( $body['event'] ) ? sanitize_key( (string) $body['event'] ) : '';
    $value      = isset( $body['value'] ) ? (int) $body['value'] : 0;
    $visitor_id = isset( $body['visitor_id'] ) ? (string) $body['visitor_id'] : '';

    if ( $post_id <= 0 || '' === $event || '' === $visitor_id ) {
        return new WP_REST_Response( [ 'error' => 'Missing params' ], 400 );
    }

    $result = slv_stats_handle_track( $post_id, $event, $value, $visitor_id );

    return new WP_REST_Response( [ 'success' => $result ], 200 );
}

/**
 * 简易速率限制
 *
 * @since 1.0.0
 * @return bool
 */
function slv_stats_rate_limit(): bool {
    $ip_hash = slv_stats_hash_ip();

    if ( '' === $ip_hash ) {
        return true;
    }

    $key   = 'slv_rate_' . $ip_hash;
    $count = (int) wp_cache_get( $key, SLV_CACHE_GROUP );

    if ( $count >= 30 ) {
        return false;
    }

    wp_cache_set( $key, $count + 1, SLV_CACHE_GROUP, 60 );

    return true;
}