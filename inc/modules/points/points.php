<?php
/**
 * 积分系统
 *
 * 余额存于 usermeta：_slv_points_balance
 * 流水存于表：slv_points_log
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'slv_purchase_completed', 'slv_points_on_purchase', 10, 3 );
add_action( 'user_register', 'slv_points_on_register', 10, 1 );

/**
 * 获取用户积分余额
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return int
 */
function slv_get_user_points( int $user_id ): int {
    if ( $user_id <= 0 ) {
        return 0;
    }

    return (int) get_user_meta( $user_id, '_slv_points_balance', true );
}

/**
 * 增加积分
 *
 * @since 1.0.0
 *
 * @param int    $user_id     用户ID
 * @param int    $points      积分（正数）
 * @param string $action      动作标识
 * @param string $description 描述
 * @param int    $reference_id 关联ID
 * @param int    $expire_days 过期天数（0 为不过期）
 *
 * @return bool
 */
function slv_add_points( int $user_id, int $points, string $action = 'manual', string $description = '', int $reference_id = 0, int $expire_days = 0 ): bool {
    if ( $user_id <= 0 || $points <= 0 ) {
        return false;
    }

    $expires_at = null;

    if ( $expire_days <= 0 ) {
        $expire_days = (int) slv_get_config( 'points.expire_days', 365 );
    }

    if ( $expire_days > 0 ) {
        $expires_at = gmdate( 'Y-m-d H:i:s', time() + ( $expire_days * DAY_IN_SECONDS ) );
    }

    global $wpdb;

    $inserted = $wpdb->insert(
        "{$wpdb->prefix}slv_points_log",
        [
            'user_id'      => $user_id,
            'points'       => $points,
            'action'       => $action,
            'reference_id' => $reference_id,
            'description'  => $description,
            'expires_at'   => $expires_at,
        ],
        [ '%d', '%d', '%s', '%d', '%s', '%s' ]
    );

    if ( false === $inserted ) {
        return false;
    }

    $current = slv_get_user_points( $user_id );
    update_user_meta( $user_id, '_slv_points_balance', $current + $points );

    // 累计获得积分（影响会员等级）
    slv_add_user_points_earned( $user_id, $points );

    /**
     * 积分增加后触发
     *
     * @since 1.0.0
     *
     * @param int    $user_id 用户ID
     * @param int    $points  积分
     * @param string $action  动作标识
     */
    do_action( 'slv_points_added', $user_id, $points, $action );

    return true;
}

/**
 * 扣减积分
 *
 * @since 1.0.0
 *
 * @param int    $user_id     用户ID
 * @param int    $points      积分（正数）
 * @param string $action      动作标识
 * @param string $description 描述
 * @param int    $reference_id 关联ID
 *
 * @return bool
 */
function slv_deduct_points( int $user_id, int $points, string $action = 'manual', string $description = '', int $reference_id = 0 ): bool {
    if ( $user_id <= 0 || $points <= 0 ) {
        return false;
    }

    $current = slv_get_user_points( $user_id );

    if ( $current < $points ) {
        return false;
    }

    global $wpdb;

    $inserted = $wpdb->insert(
        "{$wpdb->prefix}slv_points_log",
        [
            'user_id'      => $user_id,
            'points'       => -$points,
            'action'       => $action,
            'reference_id' => $reference_id,
            'description'  => $description,
        ],
        [ '%d', '%d', '%s', '%d', '%s' ]
    );

    if ( false === $inserted ) {
        return false;
    }

    update_user_meta( $user_id, '_slv_points_balance', $current - $points );

    /**
     * 积分扣减后触发
     *
     * @since 1.0.0
     *
     * @param int    $user_id 用户ID
     * @param int    $points  积分
     * @param string $action  动作标识
     */
    do_action( 'slv_points_deducted', $user_id, $points, $action );

    return true;
}

/**
 * 获取用户积分流水
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 * @param int $limit   条数
 * @param int $offset  偏移
 *
 * @return array<int, array<string, mixed>>
 */
function slv_get_user_points_log( int $user_id, int $limit = 20, int $offset = 0 ): array {
    if ( $user_id <= 0 ) {
        return [];
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}slv_points_log
             WHERE user_id = %d
             ORDER BY created_at DESC, id DESC
             LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        ),
        ARRAY_A
    );

    return is_array( $rows ) ? $rows : [];
}

/**
 * 计算订单应得积分
 *
 * @since 1.0.0
 *
 * @param float $amount  订单金额
 * @param int   $user_id 用户ID
 *
 * @return int
 */
function slv_calculate_order_points( float $amount, int $user_id ): int {
    if ( $amount <= 0 ) {
        return 0;
    }

    $enabled = (bool) slv_get_config( 'points.enabled', true );

    if ( ! $enabled ) {
        return 0;
    }

    $rate       = (float) slv_get_config( 'points.earn_rate', 1.00 );
    $multiplier = 1.0;

    // 会员等级加成
    $membership = slv_get_user_membership( $user_id );

    if ( null !== $membership && isset( $membership['points_multiplier'] ) ) {
        $multiplier = (float) $membership['points_multiplier'];
    }

    return (int) floor( $amount * $rate * $multiplier );
}

/**
 * 订单完成后自动发积分
 *
 * @since 1.0.0
 *
 * @param int   $user_id  用户ID
 * @param int   $order_id 订单ID
 * @param array $context  上下文
 *
 * @return void
 */
function slv_points_on_purchase( int $user_id, int $order_id, array $context ): void {
    if ( $user_id <= 0 ) {
        return;
    }

    $amount = isset( $context['amount'] ) ? (float) $context['amount'] : 0.0;

    if ( $amount <= 0 ) {
        return;
    }

    // 避免重复发放
    $already = get_user_meta( $user_id, '_slv_points_order_' . $order_id, true );

    if ( $already ) {
        return;
    }

    $points = slv_calculate_order_points( $amount, $user_id );

    if ( $points > 0 ) {
        slv_add_points(
            $user_id,
            $points,
            'order',
            sprintf(
                /* translators: %d: order id */
                __( '订单 #%d 消费奖励', 'sunlyvo-nexus' ),
                $order_id
            ),
            $order_id
        );

        update_user_meta( $user_id, '_slv_points_order_' . $order_id, 1 );
    }
}

/**
 * 注册用户赠送积分
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return void
 */
function slv_points_on_register( int $user_id ): void {
    if ( $user_id <= 0 ) {
        return;
    }

    slv_add_points( $user_id, 100, 'register', __( '注册奖励', 'sunlyvo-nexus' ) );
}