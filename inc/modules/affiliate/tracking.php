<?php
/**
 * 分销追踪
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'slv_affiliate_capture_ref' );

/**
 * 捕获推荐参数
 *
 * URL 参数 ref=USER_ID 或 ref=username，写入 cookie。
 *
 * @since 1.0.0
 * @return void
 */
function slv_affiliate_capture_ref(): void {
    if ( is_admin() ) {
        return;
    }

    if ( ! isset( $_GET['ref'] ) ) {
        return;
    }

    $ref = sanitize_text_field( wp_unslash( (string) $_GET['ref'] ) );

    if ( '' === $ref ) {
        return;
    }

    $user = ctype_digit( $ref )
        ? get_user_by( 'ID', (int) $ref )
        : get_user_by( 'login', $ref );

    if ( ! $user ) {
        return;
    }

    // 不能自己推荐自己
    if ( get_current_user_id() === (int) $user->ID ) {
        return;
    }

    setcookie(
        'slv_ref',
        (string) $user->ID,
        [
            'expires'  => time() + ( 30 * DAY_IN_SECONDS ),
            'path'     => COOKIEPATH ?: '/',
            'domain'   => COOKIE_DOMAIN ?: '',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );

    $_COOKIE['slv_ref'] = (string) $user->ID;
}

/**
 * 获取当前推荐的用户ID
 *
 * @since 1.0.0
 * @return int
 */
function slv_affiliate_get_referrer_id(): int {
    if ( ! isset( $_COOKIE['slv_ref'] ) ) {
        return 0;
    }

    $ref = (int) $_COOKIE['slv_ref'];

    if ( $ref <= 0 || $ref === get_current_user_id() ) {
        return 0;
    }

    return $ref;
}

/**
 * 生成推荐链接
 *
 * @since 1.0.0
 *
 * @param int    $user_id 推荐人ID
 * @param string $url     目标URL
 *
 * @return string
 */
function slv_affiliate_get_referral_url( int $user_id, string $url = '' ): string {
    if ( $user_id <= 0 ) {
        return $url;
    }

    if ( '' === $url ) {
        $url = home_url( '/' );
    }

    return add_query_arg( 'ref', $user_id, $url );
}