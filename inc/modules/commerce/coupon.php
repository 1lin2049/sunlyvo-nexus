<?php
/**
 * 优惠券
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取优惠券表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_coupon_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_coupons';
}

/**
 * 获取优惠券使用记录表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_coupon_use_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_coupon_uses';
}

/**
 * 根据 code 获取优惠券
 *
 * @since 1.0.0
 *
 * @param string $code 优惠券码
 *
 * @return array<string, mixed>|null
 */
function slv_coupon_get_by_code( string $code ): ?array {
    if ( '' === $code ) {
        return null;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_coupon_table() . ' WHERE code = %s LIMIT 1',
            $code
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        return null;
    }

    return slv_coupon_cast( $row );
}

/**
 * 优惠券数据转换
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $row 行数据
 *
 * @return array<string, mixed>
 */
function slv_coupon_cast( array $row ): array {
    $row['id']               = (int) $row['id'];
    $row['discount_value']   = (float) $row['discount_value'];
    $row['min_order_amount'] = (float) $row['min_order_amount'];
    $row['max_discount']     = (float) $row['max_discount'];
    $row['max_uses']         = (int) $row['max_uses'];
    $row['max_uses_per_user'] = (int) $row['max_uses_per_user'];
    $row['used_count']       = (int) $row['used_count'];
    $row['is_active']        = (bool) $row['is_active'];
    $row['applies_to']       = slv_product_decode_json_object( $row['applies_to'] ?? null );

    return $row;
}

/**
 * 校验优惠券是否可用
 *
 * @since 1.0.0
 *
 * @param string $code      优惠券码
 * @param float  $subtotal  订单小计
 * @param int    $user_id   用户ID
 *
 * @return array{valid:bool, message:string, coupon:array<string,mixed>|null}
 */
function slv_coupon_validate( string $code, float $subtotal, int $user_id = 0 ): array {
    $result = [
        'valid'   => false,
        'message' => '',
        'coupon'  => null,
    ];

    $coupon = slv_coupon_get_by_code( $code );

    if ( null === $coupon ) {
        $result['message'] = esc_html__( '优惠券不存在。', 'sunlyvo-nexus' );
        return $result;
    }

    if ( ! $coupon['is_active'] ) {
        $result['message'] = esc_html__( '优惠券已停用。', 'sunlyvo-nexus' );
        return $result;
    }

    $now = current_time( 'mysql' );

    if ( ! empty( $coupon['starts_at'] ) && $coupon['starts_at'] > $now ) {
        $result['message'] = esc_html__( '优惠券尚未生效。', 'sunlyvo-nexus' );
        return $result;
    }

    if ( ! empty( $coupon['expires_at'] ) && $coupon['expires_at'] < $now ) {
        $result['message'] = esc_html__( '优惠券已过期。', 'sunlyvo-nexus' );
        return $result;
    }

    if ( $coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses'] ) {
        $result['message'] = esc_html__( '优惠券已达使用上限。', 'sunlyvo-nexus' );
        return $result;
    }

    if ( $coupon['min_order_amount'] > 0 && $subtotal < $coupon['min_order_amount'] ) {
        $result['message'] = sprintf(
            /* translators: %s: minimum amount */
            esc_html__( '订单金额需达到 %s 才可使用。', 'sunlyvo-nexus' ),
            slv_format_price( $coupon['min_order_amount'] )
        );
        return $result;
    }

    // 用户使用次数校验
    if ( $user_id > 0 && $coupon['max_uses_per_user'] > 0 ) {
        global $wpdb;

        $user_uses = (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . slv_coupon_use_table() . ' WHERE coupon_id = %d AND user_id = %d',
                (int) $coupon['id'],
                $user_id
            )
        );

        if ( $user_uses >= $coupon['max_uses_per_user'] ) {
            $result['message'] = esc_html__( '你已达到此优惠券的使用次数上限。', 'sunlyvo-nexus' );
            return $result;
        }
    }

    $result['valid']   = true;
    $result['message'] = esc_html__( '优惠券可用。', 'sunlyvo-nexus' );
    $result['coupon']  = $coupon;

    return $result;
}

/**
 * 计算优惠券折扣金额
 *
 * @since 1.0.0
 *
 * @param string $code     优惠券码
 * @param float  $subtotal 订单小计
 *
 * @return array{valid:bool, discount:float, message:string}
 */
function slv_coupon_calculate_discount( string $code, float $subtotal ): array {
    $result = [
        'valid'    => false,
        'discount' => 0.0,
        'message'  => '',
    ];

    $validation = slv_coupon_validate( $code, $subtotal, get_current_user_id() );

    if ( ! $validation['valid'] || null === $validation['coupon'] ) {
        $result['message'] = $validation['message'];
        return $result;
    }

    $coupon = $validation['coupon'];
    $discount = 0.0;

    if ( 'percent' === $coupon['discount_type'] ) {
        $discount = $subtotal * ( (float) $coupon['discount_value'] / 100 );
    } elseif ( 'fixed' === $coupon['discount_type'] ) {
        $discount = (float) $coupon['discount_value'];
    }

    // 上限
    if ( $coupon['max_discount'] > 0 && $discount > $coupon['max_discount'] ) {
        $discount = (float) $coupon['max_discount'];
    }

    // 不超过小计
    $discount = min( $discount, $subtotal );

    $result['valid']    = true;
    $result['discount'] = round( $discount, 2 );
    $result['message']  = esc_html__( '优惠券已应用。', 'sunlyvo-nexus' );

    return $result;
}

/**
 * 记录优惠券使用
 *
 * @since 1.0.0
 *
 * @param string $code            优惠券码
 * @param int    $user_id         用户ID
 * @param int    $order_id        订单ID
 * @param float  $discount_amount 折扣金额
 *
 * @return bool
 */
function slv_coupon_record_use( string $code, int $user_id, int $order_id, float $discount_amount ): bool {
    $coupon = slv_coupon_get_by_code( $code );

    if ( null === $coupon ) {
        return false;
    }

    global $wpdb;

    $inserted = $wpdb->insert(
        slv_coupon_use_table(),
        [
            'coupon_id'       => (int) $coupon['id'],
            'user_id'         => $user_id,
            'order_id'        => $order_id,
            'discount_amount' => $discount_amount,
        ],
        [ '%d', '%d', '%d', '%f' ]
    );

    if ( false === $inserted ) {
        return false;
    }

    // 更新使用次数
    $wpdb->query(
        $wpdb->prepare(
            'UPDATE ' . slv_coupon_table() . ' SET used_count = used_count + 1 WHERE id = %d',
            (int) $coupon['id']
        )
    );

    return true;
}

/**
 * 创建优惠券
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $data 优惠券数据
 *
 * @return int
 */
function slv_coupon_create( array $data ): int {
    $code = isset( $data['code'] ) ? sanitize_text_field( (string) $data['code'] ) : '';

    if ( '' === $code ) {
        return 0;
    }

    global $wpdb;

    // code 唯一性
    $existing = $wpdb->get_var(
        $wpdb->prepare( 'SELECT id FROM ' . slv_coupon_table() . ' WHERE code = %s LIMIT 1', $code )
    );

    if ( null !== $existing ) {
        return 0;
    }

    $inserted = $wpdb->insert(
        slv_coupon_table(),
        [
            'code'              => $code,
            'description'       => isset( $data['description'] ) ? sanitize_text_field( (string) $data['description'] ) : '',
            'discount_type'     => isset( $data['discount_type'] ) ? sanitize_key( (string) $data['discount_type'] ) : 'percent',
            'discount_value'    => isset( $data['discount_value'] ) ? (float) $data['discount_value'] : 0.0,
            'min_order_amount'  => isset( $data['min_order_amount'] ) ? (float) $data['min_order_amount'] : 0.0,
            'max_discount'      => isset( $data['max_discount'] ) ? (float) $data['max_discount'] : 0.0,
            'max_uses'          => isset( $data['max_uses'] ) ? (int) $data['max_uses'] : 0,
            'max_uses_per_user' => isset( $data['max_uses_per_user'] ) ? (int) $data['max_uses_per_user'] : 0,
            'starts_at'         => isset( $data['starts_at'] ) ? (string) $data['starts_at'] : null,
            'expires_at'        => isset( $data['expires_at'] ) ? (string) $data['expires_at'] : null,
            'applies_to'        => isset( $data['applies_to'] ) && is_array( $data['applies_to'] ) ? wp_json_encode( $data['applies_to'] ) : null,
            'is_active'         => isset( $data['is_active'] ) ? (int) (bool) $data['is_active'] : 1,
        ],
        [ '%s', '%s', '%s', '%f', '%f', '%f', '%d', '%d', '%s', '%s', '%s', '%d' ]
    );

    if ( false === $inserted ) {
        return 0;
    }

    return (int) $wpdb->insert_id;
}

/**
 * 列出优惠券
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $args 查询参数
 *
 * @return array<int, array<string, mixed>>
 */
function slv_coupon_list( array $args = [] ): array {
    $args = wp_parse_args(
        $args,
        [
            'is_active' => null,
            'limit'     => 50,
            'offset'    => 0,
        ]
    );

    global $wpdb;

    $where  = [ '1=1' ];
    $params = [];

    if ( null !== $args['is_active'] ) {
        $where[]  = 'is_active = %d';
        $params[] = (int) (bool) $args['is_active'];
    }

    $limit  = max( 1, min( 500, (int) $args['limit'] ) );
    $offset = max( 0, (int) $args['offset'] );

    $sql = 'SELECT * FROM ' . slv_coupon_table() . ' WHERE ' . implode( ' AND ', $where )
         . ' ORDER BY id DESC LIMIT %d OFFSET %d';

    $params[] = $limit;
    $params[] = $offset;

    $rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

    if ( ! is_array( $rows ) ) {
        return [];
    }

    return array_map( 'slv_coupon_cast', $rows );
}