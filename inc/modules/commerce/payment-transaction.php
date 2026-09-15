<?php
/**
 * 支付流水记录
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取支付流水表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_payment_transaction_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_payment_transactions';
}

/**
 * 记录支付流水
 *
 * @since 1.0.0
 *
 * @param int    $order_id   订单ID
 * @param string $gateway    网关ID
 * @param string $txn_id     网关交易ID
 * @param float  $amount     金额
 * @param string $currency   货币
 * @param string $status     状态：pending / succeeded / failed / refunded
 * @param array<string, mixed> $response 网关原始响应
 *
 * @return int 流水ID
 */
function slv_payment_record_transaction(
    int $order_id,
    string $gateway,
    string $txn_id,
    float $amount,
    string $currency,
    string $status,
    array $response = []
): int {
    if ( $order_id <= 0 || '' === $gateway || '' === $txn_id ) {
        return 0;
    }

    global $wpdb;

    // 幂等性：已存在则更新状态
    $existing_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            'SELECT id FROM ' . slv_payment_transaction_table() . '
             WHERE gateway = %s AND gateway_txn_id = %s LIMIT 1',
            $gateway,
            $txn_id
        )
    );

    $record = [
        'order_id'       => $order_id,
        'gateway'        => $gateway,
        'gateway_txn_id' => $txn_id,
        'amount'         => $amount,
        'currency'       => $currency,
        'status'         => $status,
        'raw_response'   => ! empty( $response ) ? wp_json_encode( $response ) : null,
    ];

    if ( $existing_id > 0 ) {
        $wpdb->update(
            slv_payment_transaction_table(),
            $record,
            [ 'id' => $existing_id ],
            [ '%d', '%s', '%s', '%f', '%s', '%s', '%s' ],
            [ '%d' ]
        );

        return $existing_id;
    }

    $record['created_at'] = current_time( 'mysql' );

    $inserted = $wpdb->insert(
        slv_payment_transaction_table(),
        $record,
        [ '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s' ]
    );

    if ( false === $inserted ) {
        slv_log( 'Payment record failed: ' . $wpdb->last_error, 'slv_commerce' );
        return 0;
    }

    return (int) $wpdb->insert_id;
}

/**
 * 获取订单的支付流水
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return array<int, array<string, mixed>>
 */
function slv_payment_get_order_transactions( int $order_id ): array {
    if ( $order_id <= 0 ) {
        return [];
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_payment_transaction_table() . ' WHERE order_id = %d ORDER BY id ASC',
            $order_id
        ),
        ARRAY_A
    );

    return is_array( $rows ) ? $rows : [];
}