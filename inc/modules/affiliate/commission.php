<?php
/**
 * 分销佣金
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'slv_order_completed', 'slv_affiliate_on_order_completed', 20, 1 );

/**
 * 订单完成时计算佣金
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return void
 */
function slv_affiliate_on_order_completed( int $order_id ): void {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return;
    }

    // 只处理有推荐人的订单
    $meta         = is_array( $order['meta'] ) ? $order['meta'] : [];
    $referrer_id  = (int) ( $meta['referrer_id'] ?? 0 );

    if ( $referrer_id <= 0 ) {
        // 兜底：从 cookie 读
        $referrer_id = slv_affiliate_get_referrer_id();
    }

    if ( $referrer_id <= 0 ) {
        return;
    }

    // 不能推荐自己
    if ( $referrer_id === (int) $order['user_id'] ) {
        return;
    }

    // 幂等性检查
    global $wpdb;

    $existing = (int) $wpdb->get_var(
        $wpdb->prepare(
            'SELECT id FROM ' . $wpdb->prefix . 'slv_vendor_earnings
             WHERE order_id = %d AND type = %s LIMIT 1',
            $order_id,
            'affiliate'
        )
    );

    if ( $existing > 0 ) {
        return;
    }

    $rate = (float) slv_get_config( 'commission.affiliate_rate', 5.00 );

    if ( $rate <= 0 ) {
        return;
    }

    $gross      = (float) $order['total'];
    $commission = round( $gross * ( $rate / 100 ), 2 );
    $net        = $gross - $commission;

    $wpdb->insert(
        $wpdb->prefix . 'slv_vendor_earnings',
        [
            'vendor_id'  => $referrer_id,
            'order_id'   => $order_id,
            'gross'      => $gross,
            'commission' => $commission,
            'net'        => $net,
            'type'       => 'affiliate',
            'status'     => 'pending',
            'blog_id'    => get_current_blog_id(),
        ],
        [ '%d', '%d', '%f', '%f', '%f', '%s', '%s', '%d' ]
    );

    do_action( 'slv_affiliate_commission_recorded', $referrer_id, $order_id, $commission );
}

/**
 * 获取用户的分销收益统计
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return array{pending:float, available:float, settled:float, total:float, count:int}
 */
function slv_affiliate_get_user_earnings( int $user_id ): array {
    global $wpdb;

    if ( $user_id <= 0 ) {
        return [ 'pending' => 0.0, 'available' => 0.0, 'settled' => 0.0, 'total' => 0.0, 'count' => 0 ];
    }

    $table = $wpdb->prefix . 'slv_vendor_earnings';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT status, SUM(net) as total, COUNT(*) as count
             FROM {$table}
             WHERE vendor_id = %d
             GROUP BY status",
            $user_id
        ),
        ARRAY_A
    );

    $result = [
        'pending'   => 0.0,
        'available' => 0.0,
        'settled'   => 0.0,
        'total'     => 0.0,
        'count'     => 0,
    ];

    if ( ! is_array( $rows ) ) {
        return $result;
    }

    foreach ( $rows as $row ) {
        $status = (string) $row['status'];
        $total  = (float) $row['total'];
        $count  = (int) $row['count'];

        if ( 'pending' === $status ) {
            $result['pending'] += $total;
        } elseif ( 'available' === $status ) {
            $result['available'] += $total;
        } elseif ( 'settled' === $status ) {
            $result['settled'] += $total;
        }

        $result['total'] += $total;
        $result['count'] += $count;
    }

    return $result;
}

/**
 * 列出用户的收益记录
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 * @param int $limit   条数
 *
 * @return array<int, array<string, mixed>>
 */
function slv_affiliate_list_user_earnings( int $user_id, int $limit = 20 ): array {
    global $wpdb;

    if ( $user_id <= 0 ) {
        return [];
    }

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'slv_vendor_earnings
             WHERE vendor_id = %d
             ORDER BY created_at DESC
             LIMIT %d',
            $user_id,
            $limit
        ),
        ARRAY_A
    );

    return is_array( $rows ) ? $rows : [];
}