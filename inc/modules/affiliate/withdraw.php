<?php
/**
 * 提现申请
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 创建提现申请
 *
 * @since 1.0.0
 *
 * @param int    $user_id 用户ID
 * @param float  $amount  金额
 * @param string $method  方式（bank / paypal / stripe）
 * @param string $account 账户信息
 *
 * @return int 提现ID，失败返回 0
 */
function slv_affiliate_create_withdrawal( int $user_id, float $amount, string $method, string $account ): int {
    if ( $user_id <= 0 || $amount <= 0 || '' === $method || '' === $account ) {
        return 0;
    }

    // 检查可用余额
    $earnings = slv_affiliate_get_user_earnings( $user_id );

    if ( $earnings['available'] < $amount ) {
        return 0;
    }

    global $wpdb;

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'slv_withdrawals',
        [
            'user_id' => $user_id,
            'amount'  => $amount,
            'method'  => sanitize_key( $method ),
            'account' => sanitize_text_field( $account ),
            'status'  => 'pending',
        ],
        [ '%d', '%f', '%s', '%s', '%s' ]
    );

    if ( false === $inserted ) {
        return 0;
    }

    do_action( 'slv_affiliate_withdrawal_created', $user_id, $amount, $method );

    return (int) $wpdb->insert_id;
}

/**
 * 列出用户的提现记录
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 * @param int $limit   条数
 *
 * @return array<int, array<string, mixed>>
 */
function slv_affiliate_list_user_withdrawals( int $user_id, int $limit = 20 ): array {
    global $wpdb;

    if ( $user_id <= 0 ) {
        return [];
    }

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'slv_withdrawals
             WHERE user_id = %d
             ORDER BY created_at DESC
             LIMIT %d',
            $user_id,
            $limit
        ),
        ARRAY_A
    );

    return is_array( $rows ) ? $rows : [];
}

/**
 * 处理提现申请（管理员）
 *
 * @since 1.0.0
 *
 * @param int    $withdrawal_id 提现ID
 * @param string $action        approve | reject
 * @param string $remark        备注
 *
 * @return bool
 */
function slv_affiliate_process_withdrawal( int $withdrawal_id, string $action, string $remark = '' ): bool {
    if ( $withdrawal_id <= 0 ) {
        return false;
    }

    global $wpdb;

    $table = $wpdb->prefix . 'slv_withdrawals';

    $withdrawal = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $withdrawal_id ),
        ARRAY_A
    );

    if ( ! $withdrawal ) {
        return false;
    }

    if ( 'pending' !== $withdrawal['status'] ) {
        return false;
    }

    if ( 'approve' === $action ) {
        // 扣除可用余额（把 available 改为 settled）
        $wpdb->update(
            $wpdb->prefix . 'slv_vendor_earnings',
            [ 'status' => 'settled', 'settled_at' => current_time( 'mysql' ) ],
            [
                'vendor_id' => (int) $withdrawal['user_id'],
                'status'    => 'available',
            ],
            [ '%s', '%s' ],
            [ '%d', '%s' ]
        );

        $wpdb->update(
            $table,
            [
                'status'      => 'approved',
                'reviewed_by' => get_current_user_id(),
                'reviewed_at' => current_time( 'mysql' ),
                'remark'      => $remark,
            ],
            [ 'id' => $withdrawal_id ],
            [ '%s', '%d', '%s', '%s' ],
            [ '%d' ]
        );
    } elseif ( 'reject' === $action ) {
        $wpdb->update(
            $table,
            [
                'status'      => 'rejected',
                'reviewed_by' => get_current_user_id(),
                'reviewed_at' => current_time( 'mysql' ),
                'remark'      => $remark,
            ],
            [ 'id' => $withdrawal_id ],
            [ '%s', '%d', '%s', '%s' ],
            [ '%d' ]
        );
    } else {
        return false;
    }

    do_action( 'slv_affiliate_withdrawal_processed', $withdrawal_id, $action );

    return true;
}