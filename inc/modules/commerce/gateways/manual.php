<?php
/**
 * 手动支付网关（开发/测试环境）
 *
 * 直接标记支付成功，用于本地开发验证全流程。
 * 生产环境应通过 filter 禁用。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 手动支付网关
 */
class SLV_Gateway_Manual extends SLV_Payment_Gateway {

    /**
     * @return string
     */
    public function get_id(): string {
        return 'manual';
    }

    /**
     * @return string
     */
    public function get_title(): string {
        return esc_html__( '手动支付（开发）', 'sunlyvo-nexus' );
    }

    /**
     * @return string
     */
    public function get_description(): string {
        return esc_html__( '仅用于开发环境，点击后立即完成支付。', 'sunlyvo-nexus' );
    }

    /**
     * 是否可用
     *
     * 仅在 WP_DEBUG 为 true 时启用。
     *
     * @return bool
     */
    public function is_available(): bool {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return false;
        }

        /**
         * 过滤手动网关可用性
         *
         * @since 1.0.0
         *
         * @param bool $available 是否可用
         */
        return (bool) apply_filters( 'slv_gateway_manual_available', true );
    }

    /**
     * 处理支付
     *
     * @param array<string, mixed> $order 订单
     * @param array<string, mixed> $args  参数
     *
     * @return array{success:bool, message:string, transaction_id:string, redirect_url:string, payment_data:array<string,mixed>}
     */
    public function process( array $order, array $args ): array {
        $order_id = (int) $order['id'];
        $txn_id   = 'MANUAL-' . $order_id . '-' . time();

        // 立即标记支付成功
        slv_checkout_complete_payment( $order_id, $txn_id );

        // 记录交易流水
        slv_payment_record_transaction(
            $order_id,
            'manual',
            $txn_id,
            (float) $order['total'],
            (string) $order['currency'],
            'succeeded'
        );

        $result_url = add_query_arg(
            [
                'slv_order' => $order_id,
                'slv_status' => 'success',
            ],
            slv_commerce_get_checkout_url()
        );

        return [
            'success'        => true,
            'message'        => esc_html__( '支付成功（手动）', 'sunlyvo-nexus' ),
            'transaction_id' => $txn_id,
            'redirect_url'   => $result_url,
            'payment_data'   => [],
        ];
    }
}