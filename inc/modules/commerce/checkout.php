<?php
/**
 * 结算流程
 *
 * 从购物车创建订单，计算总额，触发支付。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 创建订单（从当前购物车）
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $args 下单参数
 *
 * @return array{success:bool, order_id:int, message:string}
 */
function slv_checkout_create_order( array $args ): array {
    $result = [
        'success'  => false,
        'order_id' => 0,
        'message'  => '',
    ];

    $user_id = get_current_user_id();

    if ( $user_id <= 0 ) {
        $result['message'] = esc_html__( '请先登录。', 'sunlyvo-nexus' );
        return $result;
    }

    $cart_items = slv_cart_get_items();

    if ( empty( $cart_items ) ) {
        $result['message'] = esc_html__( '购物车为空。', 'sunlyvo-nexus' );
        return $result;
    }

    // 计算金额
    $totals = slv_checkout_calculate_totals( $cart_items, $args );

    // 创建订单
    $order_id = slv_order_create(
        [
            'user_id'         => $user_id,
            'status'          => SLV_ORDER_STATUS_PENDING_PAYMENT,
            'subtotal'        => $totals['subtotal'],
            'discount_total'  => $totals['discount'],
            'shipping_total'  => $totals['shipping'],
            'tax_total'       => $totals['tax'],
            'total'           => $totals['total'],
            'currency'        => $totals['currency'],
            'billing_data'    => isset( $args['billing'] ) && is_array( $args['billing'] ) ? $args['billing'] : [],
            'customer_note'   => isset( $args['customer_note'] ) ? (string) $args['customer_note'] : '',
            'coupon_code'     => isset( $args['coupon_code'] ) ? (string) $args['coupon_code'] : '',
        ]
    );

    if ( $order_id <= 0 ) {
        $result['message'] = esc_html__( '订单创建失败，请重试。', 'sunlyvo-nexus' );
        return $result;
    }

    // 写入订单项（商品快照）
    foreach ( $cart_items as $item ) {
        $product = $item['product'];
        $qty     = (int) $item['quantity'];
        $price   = (float) $product['price'];

        slv_order_add_item(
            $order_id,
            [
                'product_id'         => (int) $product['id'],
                'variant_id'         => (int) $item['variant_id'],
                'product_type'       => (string) $product['product_type'],
                'sku'                => (string) $product['sku'],
                'title'              => (string) $product['title'],
                'unit_price'         => $price,
                'quantity'           => $qty,
                'subtotal'           => $price * $qty,
                'discount'           => 0.0,
                'tax'                => 0.0,
                'total'              => $price * $qty,
                'linked_object_type' => (string) $product['linked_object_type'],
                'linked_object_id'   => (int) $product['linked_object_id'],
            ]
        );
    }

    // 记录优惠券使用
    if ( ! empty( $args['coupon_code'] ) && $totals['discount'] > 0 ) {
        slv_coupon_record_use( (string) $args['coupon_code'], $user_id, $order_id, $totals['discount'] );
    }

    /**
     * 订单创建完成（等待支付）
     *
     * @since 1.0.0
     *
     * @param int   $order_id 订单ID
     * @param array $args     下单参数
     */
    do_action( 'slv_checkout_order_created', $order_id, $args );

    $result['success']  = true;
    $result['order_id'] = $order_id;
    $result['message']  = esc_html__( '订单已创建。', 'sunlyvo-nexus' );

    return $result;
}

/**
 * 计算订单金额
 *
 * @since 1.0.0
 *
 * @param array<int, array<string, mixed>> $cart_items 购物车项
 * @param array<string, mixed>             $args       参数（可能含 coupon_code）
 *
 * @return array{subtotal:float, discount:float, shipping:float, tax:float, total:float, currency:string}
 */
function slv_checkout_calculate_totals( array $cart_items, array $args = [] ): array {
    $subtotal = 0.0;
    $currency = (string) slv_get_config( 'general.currency', SLV_DEFAULT_CURRENCY );
    $requires_shipping = false;

    foreach ( $cart_items as $item ) {
        $product  = $item['product'];
        $subtotal += (float) $product['price'] * (int) $item['quantity'];
        $currency  = (string) $product['currency'];

        if ( ! empty( $product['requires_shipping'] ) ) {
            $requires_shipping = true;
        }
    }

    // 会员折扣
    $member_discount = slv_checkout_calculate_member_discount( $subtotal );

    // 优惠券折扣
    $coupon_discount = 0.0;

    if ( ! empty( $args['coupon_code'] ) ) {
        $coupon_result = slv_coupon_calculate_discount( (string) $args['coupon_code'], $subtotal );

        if ( $coupon_result['valid'] ) {
            $coupon_discount = (float) $coupon_result['discount'];
        }
    }

    $discount = $member_discount + $coupon_discount;

    // 运费（本轮返回 0，物理商品接口预留）
    $shipping = $requires_shipping ? slv_checkout_calculate_shipping( $cart_items, $args ) : 0.0;

    // 税费（本轮返回 0，后续接 Quaderno）
    $tax = slv_checkout_calculate_tax( $subtotal - $discount, $args );

    $total = max( 0.0, $subtotal - $discount + $shipping + $tax );

    return [
        'subtotal' => round( $subtotal, 2 ),
        'discount' => round( $discount, 2 ),
        'shipping' => round( $shipping, 2 ),
        'tax'      => round( $tax, 2 ),
        'total'    => round( $total, 2 ),
        'currency' => $currency,
    ];
}

/**
 * 会员折扣
 *
 * @since 1.0.0
 *
 * @param float $subtotal 小计
 *
 * @return float 折扣金额
 */
function slv_checkout_calculate_member_discount( float $subtotal ): float {
    if ( $subtotal <= 0 ) {
        return 0.0;
    }

    $user_id = get_current_user_id();

    if ( $user_id <= 0 ) {
        return 0.0;
    }

    $membership = slv_get_user_membership( $user_id );

    if ( null === $membership ) {
        return 0.0;
    }

    $rate = (float) ( $membership['discount_rate'] ?? 0 );

    if ( $rate <= 0 ) {
        return 0.0;
    }

    $discount = $subtotal * ( $rate / 100 );

    /**
     * 过滤会员折扣
     *
     * @since 1.0.0
     *
     * @param float $discount   折扣金额
     * @param float $subtotal   小计
     * @param array $membership 会员信息
     */
    return (float) apply_filters( 'slv_checkout_member_discount', $discount, $subtotal, $membership );
}

/**
 * 计算运费（物理商品用，本轮返回 0）
 *
 * @since 1.0.0
 *
 * @param array<int, array<string, mixed>> $cart_items 购物车项
 * @param array<string, mixed>             $args       参数
 *
 * @return float
 */
function slv_checkout_calculate_shipping( array $cart_items, array $args ): float {
    /**
     * 过滤运费
     *
     * @since 1.0.0
     *
     * @param float $shipping   运费
     * @param array $cart_items 购物车项
     * @param array $args       参数
     */
    return (float) apply_filters( 'slv_checkout_shipping_total', 0.0, $cart_items, $args );
}

/**
 * 计算税费（本轮返回 0）
 *
 * @since 1.0.0
 *
 * @param float                $taxable_amount 计税基数
 * @param array<string, mixed> $args           参数
 *
 * @return float
 */
function slv_checkout_calculate_tax( float $taxable_amount, array $args ): float {
    /**
     * 过滤税费
     *
     * @since 1.0.0
     *
     * @param float $tax            税费
     * @param float $taxable_amount 计税基数
     * @param array $args           参数
     */
    return (float) apply_filters( 'slv_checkout_tax_total', 0.0, $taxable_amount, $args );
}

/**
 * 支付订单
 *
 * @since 1.0.0
 *
 * @param int    $order_id 订单ID
 * @param string $gateway  支付网关
 * @param array<string, mixed> $args 支付参数
 *
 * @return array{success:bool, message:string, redirect_url:string, payment_data:array<string,mixed>}
 */
function slv_checkout_pay_order( int $order_id, string $gateway, array $args = [] ): array {
    $result = [
        'success'      => false,
        'message'      => '',
        'redirect_url' => '',
        'payment_data' => [],
    ];

    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        $result['message'] = esc_html__( '订单不存在。', 'sunlyvo-nexus' );
        return $result;
    }

    if ( SLV_ORDER_STATUS_PENDING_PAYMENT !== $order['status'] ) {
        $result['message'] = esc_html__( '订单状态不允许支付。', 'sunlyvo-nexus' );
        return $result;
    }

    if ( get_current_user_id() !== (int) $order['user_id'] ) {
        $result['message'] = esc_html__( '无权操作此订单。', 'sunlyvo-nexus' );
        return $result;
    }

    $gateway_instance = slv_gateway_get( $gateway );

    if ( null === $gateway_instance ) {
        $result['message'] = esc_html__( '支付方式不可用。', 'sunlyvo-nexus' );
        return $result;
    }

    $pay_result = $gateway_instance->process( $order, $args );

    if ( ! $pay_result['success'] ) {
        $result['message'] = $pay_result['message'];
        return $result;
    }

    // 更新订单网关信息
    slv_order_update(
        $order_id,
        [
            'payment_gateway' => $gateway,
            'transaction_id'  => (string) ( $pay_result['transaction_id'] ?? '' ),
        ]
    );

    $result['success']      = true;
    $result['redirect_url'] = (string) ( $pay_result['redirect_url'] ?? '' );
    $result['payment_data'] = (array) ( $pay_result['payment_data'] ?? [] );

    return $result;
}

/**
 * 完成订单支付（网关回调调用）
 *
 * @since 1.0.0
 *
 * @param int    $order_id       订单ID
 * @param string $transaction_id 交易ID
 * @param array<string, mixed> $meta 附加数据
 *
 * @return bool
 */
function slv_checkout_complete_payment( int $order_id, string $transaction_id = '', array $meta = [] ): bool {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return false;
    }

    // 幂等性：已支付直接返回
    if ( SLV_ORDER_STATUS_PAID === $order['status'] || SLV_ORDER_STATUS_COMPLETED === $order['status'] ) {
        return true;
    }

    if ( '' !== $transaction_id ) {
        slv_order_update( $order_id, [ 'transaction_id' => $transaction_id ] );
    }

    // 更新为已付款
    if ( ! slv_order_update_status( $order_id, SLV_ORDER_STATUS_PAID, __( '支付成功', 'sunlyvo-nexus' ) ) ) {
        return false;
    }

    // 虚拟商品：直接完成
    // 物理商品：需要后续发货流程
    $all_virtual = true;

    foreach ( slv_order_get_items( $order_id ) as $item ) {
        if ( 'physical' === $item['product_type'] ) {
            $all_virtual = false;
            break;
        }
    }

    if ( $all_virtual ) {
        slv_order_update_status( $order_id, SLV_ORDER_STATUS_COMPLETED, __( '虚拟商品自动完成', 'sunlyvo-nexus' ) );
    } else {
        slv_order_update_status( $order_id, SLV_ORDER_STATUS_PROCESSING, __( '等待发货', 'sunlyvo-nexus' ) );
    }

    /**
     * 订单支付完成
     *
     * @since 1.0.0
     *
     * @param int   $order_id       订单ID
     * @param array $order          订单数据
     * @param array $meta           附加数据
     */
    do_action( 'slv_checkout_payment_completed', $order_id, $order, $meta );

    return true;
}

/**
 * 标记订单支付失败
 *
 * @since 1.0.0
 *
 * @param int    $order_id 订单ID
 * @param string $reason   失败原因
 *
 * @return bool
 */
function slv_checkout_fail_payment( int $order_id, string $reason = '' ): bool {
    return slv_order_update_status(
        $order_id,
        SLV_ORDER_STATUS_FAILED,
        $reason ?: __( '支付失败', 'sunlyvo-nexus' )
    );
}

/**
 * 取消订单
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return bool
 */
function slv_checkout_cancel_order( int $order_id ): bool {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return false;
    }

    if ( get_current_user_id() !== (int) $order['user_id'] && ! current_user_can( 'manage_options' ) ) {
        return false;
    }

    return slv_order_update_status( $order_id, SLV_ORDER_STATUS_CANCELLED, __( '用户取消', 'sunlyvo-nexus' ) );
}