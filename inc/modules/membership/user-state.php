<?php
/**
 * 用户会员状态
 *
 * 用户会员状态存于 usermeta：
 * - _slv_membership_level   当前等级 slug
 * - _slv_membership_expires 会员过期时间
 * - _slv_total_spent        累计消费
 * - _slv_total_points_earned 累计获得积分
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'slv_user_meets_level', 'slv_membership_check_level', 10, 3 );
add_action( 'slv_purchase_completed', 'slv_membership_on_purchase', 10, 3 );

/**
 * 获取用户会员等级 slug
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return string
 */
function slv_get_user_membership_level( int $user_id ): string {
    if ( $user_id <= 0 ) {
        return '';
    }

    return (string) get_user_meta( $user_id, '_slv_membership_level', true );
}

/**
 * 获取用户会员等级完整数据
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return array<string, mixed>|null
 */
function slv_get_user_membership( int $user_id ): ?array {
    $slug = slv_get_user_membership_level( $user_id );

    if ( '' === $slug ) {
        return null;
    }

    return slv_get_member_level( $slug );
}

/**
 * 设置用户会员等级
 *
 * @since 1.0.0
 *
 * @param int    $user_id 用户ID
 * @param string $slug    等级 slug
 *
 * @return bool
 */
function slv_set_user_membership_level( int $user_id, string $slug ): bool {
    if ( $user_id <= 0 ) {
        return false;
    }

    $level = slv_get_member_level( $slug );

    if ( null === $level ) {
        return false;
    }

    $old_slug = slv_get_user_membership_level( $user_id );

    update_user_meta( $user_id, '_slv_membership_level', $slug );

    if ( $old_slug !== $slug ) {
        /**
         * 会员等级变更
         *
         * @since 1.0.0
         *
         * @param int    $user_id  用户ID
         * @param string $new_slug 新等级
         * @param string $old_slug 旧等级
         */
        do_action( 'slv_membership_level_changed', $user_id, $slug, $old_slug );
    }

    return true;
}

/**
 * 获取用户累计消费
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return float
 */
function slv_get_user_total_spent( int $user_id ): float {
    return (float) get_user_meta( $user_id, '_slv_total_spent', true );
}

/**
 * 增加用户累计消费
 *
 * @since 1.0.0
 *
 * @param int   $user_id 用户ID
 * @param float $amount  金额
 *
 * @return void
 */
function slv_add_user_spent( int $user_id, float $amount ): void {
    if ( $user_id <= 0 || $amount <= 0 ) {
        return;
    }

    $current = slv_get_user_total_spent( $user_id );
    update_user_meta( $user_id, '_slv_total_spent', $current + $amount );
}

/**
 * 获取用户累计获得积分
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return int
 */
function slv_get_user_total_points_earned( int $user_id ): int {
    return (int) get_user_meta( $user_id, '_slv_total_points_earned', true );
}

/**
 * 增加用户累计获得积分
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 * @param int $points  积分
 *
 * @return void
 */
function slv_add_user_points_earned( int $user_id, int $points ): void {
    if ( $user_id <= 0 || $points <= 0 ) {
        return;
    }

    $current = slv_get_user_total_points_earned( $user_id );
    update_user_meta( $user_id, '_slv_total_points_earned', $current + $points );
}

/**
 * 重新计算并更新用户会员等级
 *
 * @since 1.0.0
 *
 * @param int $user_id 用户ID
 *
 * @return void
 */
function slv_recalculate_user_membership( int $user_id ): void {
    if ( $user_id <= 0 ) {
        return;
    }

    $total_spent  = slv_get_user_total_spent( $user_id );
    $total_points = slv_get_user_total_points_earned( $user_id );

    $level = slv_calculate_member_level( $total_spent, $total_points );

    if ( null === $level ) {
        return;
    }

    slv_set_user_membership_level( $user_id, (string) $level['level_slug'] );
}

/**
 * 会员等级判断钩子：slv_user_meets_level
 *
 * @since 1.0.0
 *
 * @param bool   $meets   当前判断（默认 false）
 * @param int    $user_id 用户ID
 * @param string $level   需要的等级 slug
 *
 * @return bool
 */
function slv_membership_check_level( bool $meets, int $user_id, string $level ): bool {
    if ( $meets ) {
        return true;
    }

    if ( $user_id <= 0 || '' === $level ) {
        return false;
    }

    $user_slug = slv_get_user_membership_level( $user_id );

    if ( '' === $user_slug ) {
        return false;
    }

    $required = slv_get_member_level( $level );

    if ( null === $required ) {
        return false;
    }

    $current = slv_get_member_level( $user_slug );

    if ( null === $current ) {
        return false;
    }

    return (int) $current['level_order'] >= (int) $required['level_order'];
}

/**
 * 购买完成后更新会员状态
 *
 * @since 1.0.0
 *
 * @param int   $user_id  用户ID
 * @param int   $order_id 订单ID
 * @param array $context  上下文
 *
 * @return void
 */
function slv_membership_on_purchase( int $user_id, int $order_id, array $context ): void {
    if ( $user_id <= 0 ) {
        return;
    }

    $amount = isset( $context['amount'] ) ? (float) $context['amount'] : 0.0;

    if ( $amount > 0 ) {
        slv_add_user_spent( $user_id, $amount );
    }

    slv_recalculate_user_membership( $user_id );
}