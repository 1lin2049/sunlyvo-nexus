<?php
/**
 * 电商 REST API
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rest_api_init', 'slv_commerce_rest_register_routes' );

/**
 * 注册 REST 路由
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_rest_register_routes(): void {
    // 购物车
    register_rest_route(
        SLV_REST_NAMESPACE,
        '/cart',
        [
            'methods'             => 'GET',
            'callback'            => 'slv_commerce_rest_cart_get',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        SLV_REST_NAMESPACE,
        '/cart/items',
        [
            'methods'             => 'POST',
            'callback'            => 'slv_commerce_rest_cart_add',
            'permission_callback' => '__return_true',
            'args'                => [
                'product_id' => [
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ],
                'quantity'   => [
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]
    );

    register_rest_route(
        SLV_REST_NAMESPACE,
        '/cart/items/(?P<id>\d+)',
        [
            'methods'             => 'DELETE',
            'callback'            => 'slv_commerce_rest_cart_remove',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        SLV_REST_NAMESPACE,
        '/cart/items/(?P<id>\d+)/quantity',
        [
            'methods'             => 'POST',
            'callback'            => 'slv_commerce_rest_cart_quantity',
            'permission_callback' => '__return_true',
            'args'                => [
                'quantity' => [
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]
    );

    // 结算
    register_rest_route(
        SLV_REST_NAMESPACE,
        '/checkout',
        [
            'methods'             => 'POST',
            'callback'            => 'slv_commerce_rest_checkout',
            'permission_callback' => 'is_user_logged_in',
        ]
    );

    // 优惠券校验
    register_rest_route(
        SLV_REST_NAMESPACE,
        '/coupon/validate',
        [
            'methods'             => 'POST',
            'callback'            => 'slv_commerce_rest_coupon_validate',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'code' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]
    );
}

/**
 * 购物车查询
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求
 *
 * @return WP_REST_Response
 */
function slv_commerce_rest_cart_get( WP_REST_Request $request ): WP_REST_Response {
    $items  = slv_cart_get_items();
    $totals = slv_cart_get_totals();

    return new WP_REST_Response(
        [
            'items'  => slv_commerce_rest_serialize_items( $items ),
            'totals' => $totals,
        ],
        200
    );
}

/**
 * 购物车加购
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求
 *
 * @return WP_REST_Response
 */
function slv_commerce_rest_cart_add( WP_REST_Request $request ): WP_REST_Response {
    $product_id = (int) $request->get_param( 'product_id' );
    $quantity   = max( 1, (int) $request->get_param( 'quantity' ) );

    $result = slv_cart_add( $product_id, $quantity );

    if ( ! $result ) {
        return new WP_REST_Response(
            [ 'error' => esc_html__( '加入购物车失败。', 'sunlyvo-nexus' ) ],
            400
        );
    }

    return new WP_REST_Response(
        [
            'success' => true,
            'count'   => slv_cart_get_count(),
            'totals'  => slv_cart_get_totals(),
        ],
        200
    );
}

/**
 * 购物车移除
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求
 *
 * @return WP_REST_Response
 */
function slv_commerce_rest_cart_remove( WP_REST_Request $request ): WP_REST_Response {
    $item_id = (int) $request->get_param( 'id' );

    $result = slv_cart_remove_item( $item_id );

    if ( ! $result ) {
        return new WP_REST_Response(
            [ 'error' => esc_html__( '移除失败。', 'sunlyvo-nexus' ) ],
            400
        );
    }

    return new WP_REST_Response(
        [
            'success' => true,
            'totals'  => slv_cart_get_totals(),
        ],
        200
    );
}

/**
 * 购物车更新数量
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求
 *
 * @return WP_REST_Response
 */
function slv_commerce_rest_cart_quantity( WP_REST_Request $request ): WP_REST_Response {
    $item_id  = (int) $request->get_param( 'id' );
    $quantity = (int) $request->get_param( 'quantity' );

    $result = slv_cart_update_quantity( $item_id, $quantity );

    if ( ! $result ) {
        return new WP_REST_Response(
            [ 'error' => esc_html__( '更新失败。', 'sunlyvo-nexus' ) ],
            400
        );
    }

    return new WP_REST_Response(
        [
            'success' => true,
            'totals'  => slv_cart_get_totals(),
        ],
        200
    );
}

/**
 * 结算下单
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求
 *
 * @return WP_REST_Response
 */
function slv_commerce_rest_checkout( WP_REST_Request $request ): WP_REST_Response {
    $body = $request->get_json_params();

    if ( ! is_array( $body ) ) {
        $body = [];
    }

    // 校验 nonce
    $nonce = isset( $body['nonce'] ) ? (string) $body['nonce'] : '';

    if ( ! wp_verify_nonce( $nonce, 'slv_checkout' ) ) {
        return new WP_REST_Response(
            [ 'error' => esc_html__( '会话已失效，请刷新页面重试。', 'sunlyvo-nexus' ) ],
            403
        );
    }

    $billing       = isset( $body['billing'] ) && is_array( $body['billing'] ) ? $body['billing'] : [];
    $customer_note = isset( $body['customer_note'] ) ? (string) $body['customer_note'] : '';
    $gateway       = isset( $body['gateway'] ) ? sanitize_key( (string) $body['gateway'] ) : '';

    // 创建订单
    $create = slv_checkout_create_order(
        [
            'billing'       => $billing,
            'customer_note' => $customer_note,
            'coupon_code'   => isset( $body['coupon_code'] ) ? (string) $body['coupon_code'] : '',
        ]
    );

    if ( ! $create['success'] ) {
        return new WP_REST_Response(
            [ 'error' => $create['message'] ],
            400
        );
    }

    $order_id = (int) $create['order_id'];

    // 清空购物车
    slv_cart_clear();

    // 支付
    if ( '' === $gateway ) {
        return new WP_REST_Response(
            [
                'success'  => true,
                'order_id' => $order_id,
                'redirect' => add_query_arg( [ 'order' => $order_id ], slv_commerce_get_checkout_url() ),
            ],
            200
        );
    }

    $pay = slv_checkout_pay_order( $order_id, $gateway );

    if ( ! $pay['success'] ) {
        return new WP_REST_Response(
            [
                'error'    => $pay['message'],
                'order_id' => $order_id,
            ],
            400
        );
    }

    return new WP_REST_Response(
        [
            'success'  => true,
            'order_id' => $order_id,
            'redirect' => $pay['redirect_url'],
        ],
        200
    );
}

/**
 * 优惠券校验
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request 请求
 *
 * @return WP_REST_Response
 */
function slv_commerce_rest_coupon_validate( WP_REST_Request $request ): WP_REST_Response {
    $code   = (string) $request->get_param( 'code' );
    $totals = slv_cart_get_totals();

    $result = slv_coupon_calculate_discount( $code, (float) $totals['subtotal'] );

    return new WP_REST_Response(
        [
            'valid'    => $result['valid'],
            'discount' => $result['discount'],
            'message'  => $result['message'],
        ],
        200
    );
}

/**
 * 序列化购物车项
 *
 * @since 1.0.0
 *
 * @param array<int, array<string, mixed>> $items 购物车项
 *
 * @return array<int, array<string, mixed>>
 */
function slv_commerce_rest_serialize_items( array $items ): array {
    $result = [];

    foreach ( $items as $item ) {
        $product = $item['product'];

        $result[] = [
            'item_id'    => (int) $item['cart_item_id'],
            'product_id' => (int) $product['id'],
            'title'      => (string) $product['title'],
            'price'      => (float) $product['price'],
            'currency'   => (string) $product['currency'],
            'quantity'   => (int) $item['quantity'],
            'subtotal'   => (float) $product['price'] * (int) $item['quantity'],
            'permalink'  => (int) $product['linked_object_id'] > 0 ? (string) get_permalink( (int) $product['linked_object_id'] ) : '',
        ];
    }

    return $result;
}