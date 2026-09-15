<?php
/**
 * 购物车
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取购物车表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_cart_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_carts';
}

/**
 * 获取购物车 token
 *
 * @since 1.0.0
 * @return string
 */
function slv_cart_get_token(): string {
    $user_id = get_current_user_id();

    if ( $user_id > 0 ) {
        return 'u' . $user_id;
    }

    if ( isset( $_COOKIE[ SLV_CART_TOKEN_COOKIE ] ) ) {
        $token = sanitize_text_field( wp_unslash( (string) $_COOKIE[ SLV_CART_TOKEN_COOKIE ] ) );

        if ( '' !== $token && preg_match( '/^[a-zA-Z0-9]{16,64}$/', $token ) ) {
            return $token;
        }
    }

    $token = wp_generate_password( 32, false, false );

    if ( ! headers_sent() ) {
        setcookie(
            SLV_CART_TOKEN_COOKIE,
            $token,
            [
                'expires'  => time() + SLV_CART_TOKEN_TTL,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN ?: '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    $_COOKIE[ SLV_CART_TOKEN_COOKIE ] = $token;

    return $token;
}

/**
 * 添加商品到购物车
 *
 * @since 1.0.0
 *
 * @param int                  $product_id 商品ID
 * @param int                  $quantity   数量
 * @param int                  $variant_id 变体ID
 * @param array<string, mixed> $meta       附加元数据
 *
 * @return bool
 */
function slv_cart_add( int $product_id, int $quantity = 1, int $variant_id = 0, array $meta = [] ): bool {
    if ( $product_id <= 0 ) {
        return false;
    }

    $product = slv_product_get( $product_id );

    if ( null === $product ) {
        return false;
    }

    if ( ! slv_product_is_purchasable( $product ) ) {
        return false;
    }

    $quantity = max( 1, $quantity );
    $token    = slv_cart_get_token();

    global $wpdb;

    $existing_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            'SELECT id FROM ' . slv_cart_table() . '
             WHERE cart_token = %s AND product_id = %d AND variant_id = %d
             LIMIT 1',
            $token,
            $product_id,
            $variant_id
        )
    );

    if ( $existing_id > 0 ) {
        $current_qty = (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT quantity FROM ' . slv_cart_table() . ' WHERE id = %d',
                $existing_id
            )
        );

        return slv_cart_update_quantity( $existing_id, $current_qty + $quantity );
    }

    $inserted = $wpdb->insert(
        slv_cart_table(),
        [
            'cart_token' => $token,
            'user_id'    => get_current_user_id(),
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'quantity'   => $quantity,
            'meta'       => ! empty( $meta ) ? wp_json_encode( $meta ) : null,
        ],
        [ '%s', '%d', '%d', '%d', '%d', '%s' ]
    );

    if ( false === $inserted ) {
        return false;
    }

    do_action( 'slv_cart_item_added', $product_id, $quantity, $variant_id );

    return true;
}

/**
 * 更新购物车项数量
 *
 * @since 1.0.0
 *
 * @param int $cart_item_id 购物车项ID
 * @param int $quantity     新数量
 *
 * @return bool
 */
function slv_cart_update_quantity( int $cart_item_id, int $quantity ): bool {
    if ( $cart_item_id <= 0 ) {
        return false;
    }

    global $wpdb;

    if ( $quantity <= 0 ) {
        return slv_cart_remove_item( $cart_item_id );
    }

    $updated = $wpdb->update(
        slv_cart_table(),
        [ 'quantity' => max( 1, $quantity ) ],
        [ 'id' => $cart_item_id ],
        [ '%d' ],
        [ '%d' ]
    );

    return false !== $updated;
}

/**
 * 移除购物车项
 *
 * @since 1.0.0
 *
 * @param int $cart_item_id 购物车项ID
 *
 * @return bool
 */
function slv_cart_remove_item( int $cart_item_id ): bool {
    if ( $cart_item_id <= 0 ) {
        return false;
    }

    global $wpdb;

    $deleted = $wpdb->delete( slv_cart_table(), [ 'id' => $cart_item_id ], [ '%d' ] );

    return false !== $deleted;
}

/**
 * 清空购物车
 *
 * @since 1.0.0
 * @return bool
 */
function slv_cart_clear(): bool {
    global $wpdb;

    $token   = slv_cart_get_token();
    $deleted = $wpdb->delete( slv_cart_table(), [ 'cart_token' => $token ], [ '%s' ] );

    return false !== $deleted;
}

/**
 * 获取购物车所有项
 *
 * @since 1.0.0
 * @return array<int, array<string, mixed>>
 */
function slv_cart_get_items(): array {
    global $wpdb;

    $token = slv_cart_get_token();

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_cart_table() . ' WHERE cart_token = %s ORDER BY id ASC',
            $token
        ),
        ARRAY_A
    );

    if ( ! is_array( $rows ) ) {
        return [];
    }

    $items = [];

    foreach ( $rows as $row ) {
        $product = slv_product_get( (int) $row['product_id'] );

        if ( null === $product ) {
            continue;
        }

        $items[] = [
            'cart_item_id' => (int) $row['id'],
            'product'      => $product,
            'variant_id'   => (int) $row['variant_id'],
            'quantity'     => (int) $row['quantity'],
            'meta'         => slv_product_decode_json_object( $row['meta'] ?? null ),
        ];
    }

    return $items;
}

/**
 * 获取购物车统计
 *
 * @since 1.0.0
 * @return array{count:int, subtotal:float, currency:string}
 */
function slv_cart_get_totals(): array {
    $items = slv_cart_get_items();

    $count    = 0;
    $subtotal = 0.0;
    $currency = (string) slv_get_config( 'general.currency', SLV_DEFAULT_CURRENCY );

    foreach ( $items as $item ) {
        $count    += (int) $item['quantity'];
        $subtotal += (float) $item['product']['price'] * (int) $item['quantity'];
        $currency  = (string) $item['product']['currency'];
    }

    return [
        'count'    => $count,
        'subtotal' => $subtotal,
        'currency' => $currency,
    ];
}

/**
 * 获取购物车项数
 *
 * @since 1.0.0
 * @return int
 */
function slv_cart_get_count(): int {
    $totals = slv_cart_get_totals();
    return (int) $totals['count'];
}

/**
 * 判断购物车是否为空
 *
 * @since 1.0.0
 * @return bool
 */
function slv_cart_is_empty(): bool {
    return slv_cart_get_count() <= 0;
}

/**
 * 合并匿名购物车到用户账户
 *
 * wp_login 钩子的第一个参数是 $user_login（字符串），
 * 需要解析出用户 ID 再处理。已兼容 WP 5.4+ 的 wp_login 双参数版本。
 *
 * @since 1.0.0
 *
 * @param string       $user_login 用户登录名
 * @param WP_User|null $user       用户对象（WP 5.4+ 提供）
 *
 * @return void
 */
function slv_cart_merge_anonymous_to_user( $user_login = '', $user = null ): void {
    // 解析用户
    if ( $user instanceof WP_User ) {
        $user_id = (int) $user->ID;
    } elseif ( is_numeric( $user_login ) ) {
        $user_id = (int) $user_login;
    } else {
        $user_obj = get_user_by( 'login', (string) $user_login );
        $user_id  = $user_obj ? (int) $user_obj->ID : 0;
    }

    if ( $user_id <= 0 ) {
        return;
    }

    if ( ! isset( $_COOKIE[ SLV_CART_TOKEN_COOKIE ] ) ) {
        return;
    }

    $anon_token = sanitize_text_field( wp_unslash( (string) $_COOKIE[ SLV_CART_TOKEN_COOKIE ] ) );

    if ( '' === $anon_token || 'u' === substr( $anon_token, 0, 1 ) ) {
        return;
    }

    global $wpdb;

    $user_token = 'u' . $user_id;

    $anon_items = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_cart_table() . ' WHERE cart_token = %s',
            $anon_token
        ),
        ARRAY_A
    );

    if ( ! is_array( $anon_items ) ) {
        return;
    }

    foreach ( $anon_items as $item ) {
        $existing_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . slv_cart_table() . '
                 WHERE cart_token = %s AND product_id = %d AND variant_id = %d
                 LIMIT 1',
                $user_token,
                (int) $item['product_id'],
                (int) $item['variant_id']
            )
        );

        if ( $existing_id > 0 ) {
            $existing_qty = (int) $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT quantity FROM ' . slv_cart_table() . ' WHERE id = %d',
                    $existing_id
                )
            );

            $wpdb->update(
                slv_cart_table(),
                [ 'quantity' => $existing_qty + (int) $item['quantity'] ],
                [ 'id' => $existing_id ],
                [ '%d' ],
                [ '%d' ]
            );
        } else {
            $wpdb->update(
                slv_cart_table(),
                [
                    'cart_token' => $user_token,
                    'user_id'    => $user_id,
                ],
                [ 'id' => (int) $item['id'] ],
                [ '%s', '%d' ],
                [ '%d' ]
            );
        }
    }

    if ( ! headers_sent() ) {
        setcookie(
            SLV_CART_TOKEN_COOKIE,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN ?: '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    unset( $_COOKIE[ SLV_CART_TOKEN_COOKIE ] );
}

// wp_login 钩子：WP 5.4+ 会传 2 个参数（$user_login, $user）
add_action( 'wp_login', 'slv_cart_merge_anonymous_to_user', 10, 2 );