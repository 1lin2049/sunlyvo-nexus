<?php
/**
 * Stripe 支付网关
 *
 * 使用 Stripe Checkout Session，卡片信息不经过服务器。
 *
 * 配置读取：slv_get_config('payment.stripe_secret_key')
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Stripe 支付网关
 */
class SLV_Gateway_Stripe extends SLV_Payment_Gateway {

    /**
     * @return string
     */
    public function get_id(): string {
        return 'stripe';
    }

    /**
     * @return string
     */
    public function get_title(): string {
        return esc_html__( '信用卡支付', 'sunlyvo-nexus' );
    }

    /**
     * @return string
     */
    public function get_description(): string {
        return esc_html__( '支持 Visa / Mastercard / Amex，通过 Stripe 处理。', 'sunlyvo-nexus' );
    }

    /**
     * 是否可用
     *
     * @return bool
     */
    public function is_available(): bool {
        if ( '' === $this->get_secret_key() || '' === $this->get_publishable_key() ) {
            return false;
        }

        /**
         * 过滤 Stripe 网关可用性
         *
         * @since 1.0.0
         *
         * @param bool $available 是否可用
         */
        return (bool) apply_filters( 'slv_gateway_stripe_available', true );
    }

    /**
     * 获取 secret key
     *
     * @return string
     */
    private function get_secret_key(): string {
        $key = (string) slv_get_config( 'payment.stripe_secret_key', '' );

        if ( '' === $key ) {
            $key = (string) getenv( 'SLV_STRIPE_SECRET_KEY' );
        }

        return $key;
    }

    /**
     * 获取 publishable key
     *
     * @return string
     */
    private function get_publishable_key(): string {
        $key = (string) slv_get_config( 'payment.stripe_publishable_key', '' );

        if ( '' === $key ) {
            $key = (string) getenv( 'SLV_STRIPE_PUBLISHABLE_KEY' );
        }

        return $key;
    }

    /**
     * 发起支付：创建 Stripe Checkout Session
     *
     * @param array<string, mixed> $order 订单
     * @param array<string, mixed> $args  参数
     *
     * @return array{success:bool, message:string, transaction_id:string, redirect_url:string, payment_data:array<string,mixed>}
     */
    public function process( array $order, array $args ): array {
        $order_id  = (int) $order['id'];
        $total     = (float) $order['total'];
        $currency  = strtolower( (string) $order['currency'] );
        $items     = slv_order_get_items( $order_id );

        if ( empty( $items ) ) {
            return $this->failure( esc_html__( '订单无项目。', 'sunlyvo-nexus' ) );
        }

        // 构建 Stripe line_items
        $line_items = [];

        foreach ( $items as $item ) {
            $line_items[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => [
                        'name' => (string) $item['title'],
                    ],
                    'unit_amount'  => (int) round( (float) $item['unit_price'] * 100 ),
                ],
                'quantity'   => (int) $item['quantity'],
            ];
        }

        // 回调 URL
        $success_url = add_query_arg(
            [
                'slv_order'  => $order_id,
                'slv_status' => 'success',
            ],
            slv_commerce_get_checkout_url()
        );

        $cancel_url = add_query_arg(
            [
                'slv_order'  => $order_id,
                'slv_status' => 'cancel',
            ],
            slv_commerce_get_checkout_url()
        );

        // 请求 Stripe API
        $response = wp_remote_post(
            'https://api.stripe.com/v1/checkout/sessions',
            [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->get_secret_key(),
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                ],
                'body'    => [
                    'mode'                => 'payment',
                    'success_url'         => $success_url,
                    'cancel_url'          => $cancel_url,
                    'client_reference_id' => (string) $order_id,
                    'customer_email'      => (string) ( $order['billing_data']['email'] ?? '' ),
                    'metadata[order_id]'  => (string) $order_id,
                    'line_items'          => $this->encode_line_items( $line_items ),
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $this->failure(
                sprintf(
                    /* translators: %s: error message */
                    esc_html__( 'Stripe 请求失败：%s', 'sunlyvo-nexus' ),
                    $response->get_error_message()
                )
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( (string) wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 || ! is_array( $body ) ) {
            $error_msg = is_array( $body ) && isset( $body['error']['message'] )
                ? (string) $body['error']['message']
                : sprintf( 'HTTP %d', $code );

            return $this->failure(
                sprintf(
                    /* translators: %s: error message */
                    esc_html__( 'Stripe 返回错误：%s', 'sunlyvo-nexus' ),
                    $error_msg
                )
            );
        }

        $session_id = (string) ( $body['id'] ?? '' );
        $session_url = (string) ( $body['url'] ?? '' );

        if ( '' === $session_id || '' === $session_url ) {
            return $this->failure( esc_html__( 'Stripe 返回数据不完整。', 'sunlyvo-nexus' ) );
        }

        // 记录会话ID到订单
        slv_order_update(
            $order_id,
            [
                'payment_gateway' => 'stripe',
                'transaction_id'  => $session_id,
            ]
        );

        return [
            'success'        => true,
            'message'        => esc_html__( '正在跳转至 Stripe 支付页面。', 'sunlyvo-nexus' ),
            'transaction_id' => $session_id,
            'redirect_url'   => $session_url,
            'payment_data'   => [ 'session_id' => $session_id ],
        ];
    }

    /**
     * 处理 Stripe Webhook
     *
     * @param array<string, mixed>  $payload 事件数据
     * @param array<string, string> $headers HTTP 头
     *
     * @return array{success:bool, order_id:int, message:string}
     */
    public function handle_webhook( array $payload, array $headers ): array {
        $result = [
            'success'  => false,
            'order_id' => 0,
            'message'  => '',
        ];

        $event_type = (string) ( $payload['type'] ?? '' );
        $data       = $payload['data']['object'] ?? [];

        if ( ! is_array( $data ) ) {
            $result['message'] = 'Invalid data';
            return $result;
        }

        $order_id = (int) ( $data['metadata']['order_id'] ?? $data['client_reference_id'] ?? 0 );

        if ( $order_id <= 0 ) {
            $result['message'] = 'Missing order_id';
            return $result;
        }

        $result['order_id'] = $order_id;

        switch ( $event_type ) {
            case 'checkout.session.completed':
                $txn_id = (string) ( $data['payment_intent'] ?? $data['id'] ?? '' );

                slv_checkout_complete_payment( $order_id, $txn_id );

                slv_payment_record_transaction(
                    $order_id,
                    'stripe',
                    $txn_id,
                    (float) ( $data['amount_total'] ?? 0 ) / 100,
                    strtoupper( (string) ( $data['currency'] ?? 'usd' ) ),
                    'succeeded'
                );

                $result['success'] = true;
                $result['message'] = 'Payment completed';
                break;

            case 'checkout.session.expired':
            case 'payment_intent.payment_failed':
                slv_checkout_fail_payment( $order_id, 'Stripe: ' . $event_type );
                $result['success'] = true;
                $result['message'] = 'Payment failed';
                break;

            default:
                $result['message'] = 'Unhandled event: ' . $event_type;
        }

        return $result;
    }

    /**
     * 退款
     *
     * @param int   $order_id 订单ID
     * @param float $amount   退款金额
     *
     * @return array{success:bool, message:string}
     */
    public function refund( int $order_id, float $amount ): array {
        $order = slv_order_get( $order_id );

        if ( null === $order ) {
            return [ 'success' => false, 'message' => esc_html__( '订单不存在。', 'sunlyvo-nexus' ) ];
        }

        $txn_id = (string) $order['transaction_id'];

        if ( '' === $txn_id ) {
            return [ 'success' => false, 'message' => esc_html__( '无交易ID。', 'sunlyvo-nexus' ) ];
        }

        // 简化：实际应调用 Stripe Refund API
        // 这里仅记录，后续实现
        return [
            'success' => false,
            'message' => esc_html__( '退款功能待实现。', 'sunlyvo-nexus' ),
        ];
    }

    /**
     * 构建失败返回
     *
     * @param string $message 消息
     *
     * @return array{success:bool, message:string, transaction_id:string, redirect_url:string, payment_data:array<string,mixed>}
     */
    private function failure( string $message ): array {
        return [
            'success'        => false,
            'message'        => $message,
            'transaction_id' => '',
            'redirect_url'   => '',
            'payment_data'   => [],
        ];
    }

    /**
     * 编码 line_items（Stripe 使用嵌套 form-data）
     *
     * @param array<int, array<string, mixed>> $items line_items
     *
     * @return string
     */
    private function encode_line_items( array $items ): string {
        $parts = [];

        foreach ( $items as $i => $item ) {
            $parts[] = "line_items[{$i}][quantity]=" . urlencode( (string) $item['quantity'] );
            $parts[] = "line_items[{$i}][price_data][currency]=" . urlencode( (string) $item['price_data']['currency'] );
            $parts[] = "line_items[{$i}][price_data][unit_amount]=" . urlencode( (string) $item['price_data']['unit_amount'] );
            $parts[] = "line_items[{$i}][price_data][product_data][name]=" . urlencode( (string) $item['price_data']['product_data']['name'] );
        }

        return implode( '&', $parts );
    }
}