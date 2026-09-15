<?php
/**
 * Webhook 接收
 *
 * 路由：/wp-json/slv/v1/webhook/{gateway}
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rest_api_init', 'slv_webhook_register_routes' );

/**
 * 注册 Webhook 路由
 *
 * @since 1.0.0
 * @return void
 */
function slv_webhook_register_routes(): void {
    register_rest_route(
        SLV_REST_NAMESPACE,
        '/webhook/(?P<gateway>[a-z0-9_-]+)',
        [
            'methods'             => 'POST',
            'callback'            => 'slv_webhook_handle',
            'permission_callback' => '__return_true',
            'args'                => [
                'gateway' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_key',
                ],
            ],
        ]
    );
}

/**
 * 处理 Webhook 请求
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求对象
 *
 * @return WP_REST_Response
 */
function slv_webhook_handle( WP_REST_Request $request ): WP_REST_Response {
    $gateway_id = (string) $request->get_param( 'gateway' );
    $gateway    = slv_gateway_get( $gateway_id );

    if ( null === $gateway ) {
        return new WP_REST_Response(
            [ 'error' => 'Gateway not found' ],
            404
        );
    }

    $payload = $request->get_json_params();

    if ( ! is_array( $payload ) ) {
        $payload = [];
    }

    // 收集 HTTP 头
    $headers = [];

    foreach ( $request->get_headers() as $key => $values ) {
        $headers[ $key ] = is_array( $values ) ? implode( ',', $values ) : (string) $values;
    }

    // 记录日志
    slv_log(
        sprintf( '[Webhook][%s] %s', $gateway_id, wp_json_encode( $payload ) ),
        'slv_webhook'
    );

    $result = $gateway->handle_webhook( $payload, $headers );

    if ( ! $result['success'] ) {
        return new WP_REST_Response(
            [ 'error' => $result['message'] ],
            400
        );
    }

    return new WP_REST_Response(
        [
            'ok'       => true,
            'order_id' => (int) $result['order_id'],
            'message'  => $result['message'],
        ],
        200
    );
}