<?php
/**
 * 订单 CRUD + 状态机 + 订单号
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取订单表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_order_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_orders';
}

/**
 * 获取订单项表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_order_item_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_order_items';
}

/**
 * 获取订单状态日志表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_order_status_log_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_order_status_log';
}

/**
 * 所有订单状态定义
 *
 * @since 1.0.0
 * @return array<string, string>
 */
function slv_order_get_statuses(): array {
    return [
        SLV_ORDER_STATUS_PENDING_PAYMENT => esc_html__( '待付款', 'sunlyvo-nexus' ),
        SLV_ORDER_STATUS_PAID            => esc_html__( '已付款', 'sunlyvo-nexus' ),
        SLV_ORDER_STATUS_PROCESSING      => esc_html__( '处理中', 'sunlyvo-nexus' ),
        SLV_ORDER_STATUS_COMPLETED       => esc_html__( '已完成', 'sunlyvo-nexus' ),
        SLV_ORDER_STATUS_CANCELLED       => esc_html__( '已取消', 'sunlyvo-nexus' ),
        SLV_ORDER_STATUS_REFUNDED        => esc_html__( '已退款', 'sunlyvo-nexus' ),
        SLV_ORDER_STATUS_FAILED          => esc_html__( '支付失败', 'sunlyvo-nexus' ),
    ];
}

/**
 * 获取订单状态标签
 *
 * @since 1.0.0
 *
 * @param string $status 状态
 *
 * @return string
 */
function slv_order_get_status_label( string $status ): string {
    $statuses = slv_order_get_statuses();
    return $statuses[ $status ] ?? $status;
}

/**
 * 判断状态转移是否合法
 *
 * @since 1.0.0
 *
 * @param string $from 原状态
 * @param string $to   目标状态
 *
 * @return bool
 */
function slv_order_can_transition( string $from, string $to ): bool {
    if ( $from === $to ) {
        return false;
    }

    $transitions = [
        SLV_ORDER_STATUS_PENDING_PAYMENT => [
            SLV_ORDER_STATUS_PAID,
            SLV_ORDER_STATUS_CANCELLED,
            SLV_ORDER_STATUS_FAILED,
        ],
        SLV_ORDER_STATUS_PAID => [
            SLV_ORDER_STATUS_PROCESSING,
            SLV_ORDER_STATUS_COMPLETED,
            SLV_ORDER_STATUS_REFUNDED,
        ],
        SLV_ORDER_STATUS_PROCESSING => [
            SLV_ORDER_STATUS_COMPLETED,
            SLV_ORDER_STATUS_REFUNDED,
        ],
        SLV_ORDER_STATUS_COMPLETED => [
            SLV_ORDER_STATUS_REFUNDED,
        ],
        SLV_ORDER_STATUS_CANCELLED => [],
        SLV_ORDER_STATUS_REFUNDED  => [],
        SLV_ORDER_STATUS_FAILED    => [
            SLV_ORDER_STATUS_PENDING_PAYMENT,
            SLV_ORDER_STATUS_CANCELLED,
        ],
    ];

    return in_array( $to, $transitions[ $from ] ?? [], true );
}

/**
 * 生成订单号
 *
 * 格式：SLV-YYYYMMDD-XXXXX
 *
 * @since 1.0.0
 * @return string
 */
function slv_order_generate_number(): string {
    global $wpdb;

    $date   = gmdate( 'Ymd' );
    $prefix = 'SLV-' . $date . '-';

    $max = $wpdb->get_var(
        $wpdb->prepare(
            'SELECT MAX(CAST(SUBSTRING(order_number, %d) AS UNSIGNED))
             FROM ' . slv_order_table() . '
             WHERE order_number LIKE %s',
            strlen( $prefix ) + 1,
            $prefix . '%'
        )
    );

    $next = (int) $max + 1;

    for ( $i = 0; $i < 10; $i++ ) {
        $number = $prefix . str_pad( (string) $next, 5, '0', STR_PAD_LEFT );

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . slv_order_table() . ' WHERE order_number = %s LIMIT 1',
                $number
            )
        );

        if ( null === $exists ) {
            return $number;
        }

        $next++;
    }

    return $prefix . str_pad( (string) $next, 5, '0', STR_PAD_LEFT ) . '-' . wp_generate_password( 4, false, false );
}

/**
 * 创建订单
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $data 订单数据
 *
 * @return int 订单ID，失败返回 0
 */
function slv_order_create( array $data ): int {
    $user_id = isset( $data['user_id'] ) ? (int) $data['user_id'] : get_current_user_id();

    if ( $user_id <= 0 ) {
        return 0;
    }

    global $wpdb;

    $order_number = isset( $data['order_number'] ) && '' !== $data['order_number']
        ? sanitize_text_field( (string) $data['order_number'] )
        : slv_order_generate_number();

    $record = [
        'order_number'    => $order_number,
        'user_id'         => $user_id,
        'status'          => isset( $data['status'] ) ? sanitize_key( (string) $data['status'] ) : SLV_ORDER_STATUS_PENDING_PAYMENT,
        'subtotal'        => isset( $data['subtotal'] ) ? (float) $data['subtotal'] : 0.0,
        'discount_total'  => isset( $data['discount_total'] ) ? (float) $data['discount_total'] : 0.0,
        'shipping_total'  => isset( $data['shipping_total'] ) ? (float) $data['shipping_total'] : 0.0,
        'tax_total'       => isset( $data['tax_total'] ) ? (float) $data['tax_total'] : 0.0,
        'total'           => isset( $data['total'] ) ? (float) $data['total'] : 0.0,
        'currency'        => isset( $data['currency'] ) ? sanitize_text_field( (string) $data['currency'] ) : (string) slv_get_config( 'general.currency', SLV_DEFAULT_CURRENCY ),
        'payment_gateway' => isset( $data['payment_gateway'] ) ? sanitize_key( (string) $data['payment_gateway'] ) : '',
        'payment_status'  => isset( $data['payment_status'] ) ? sanitize_key( (string) $data['payment_status'] ) : 'pending',
        'transaction_id'  => isset( $data['transaction_id'] ) ? sanitize_text_field( (string) $data['transaction_id'] ) : '',
        'billing_data'    => isset( $data['billing_data'] ) && is_array( $data['billing_data'] ) ? wp_json_encode( $data['billing_data'] ) : null,
        'shipping_data'   => isset( $data['shipping_data'] ) && is_array( $data['shipping_data'] ) ? wp_json_encode( $data['shipping_data'] ) : null,
        'customer_note'   => isset( $data['customer_note'] ) ? sanitize_textarea_field( (string) $data['customer_note'] ) : '',
        'admin_note'      => isset( $data['admin_note'] ) ? sanitize_textarea_field( (string) $data['admin_note'] ) : '',
        'coupon_code'     => isset( $data['coupon_code'] ) ? sanitize_text_field( (string) $data['coupon_code'] ) : '',
        'meta'            => isset( $data['meta'] ) && is_array( $data['meta'] ) ? wp_json_encode( $data['meta'] ) : null,
    ];

    $formats = [
        '%s', '%d', '%s',
        '%f', '%f', '%f', '%f', '%f',
        '%s', '%s', '%s', '%s',
        '%s', '%s',
        '%s', '%s',
        '%s',
        '%s',
    ];

    $inserted = $wpdb->insert( slv_order_table(), $record, $formats );

    if ( false === $inserted ) {
        slv_log( 'Order create failed: ' . $wpdb->last_error, 'slv_commerce' );
        return 0;
    }

    $order_id = (int) $wpdb->insert_id;

    slv_order_log_status_change( $order_id, '', $record['status'], __( '订单创建', 'sunlyvo-nexus' ), $user_id );

    do_action( 'slv_order_created', $order_id, $data );

    return $order_id;
}

/**
 * 获取订单
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return array<string, mixed>|null
 */
function slv_order_get( int $order_id ): ?array {
    if ( $order_id <= 0 ) {
        return null;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_order_table() . ' WHERE id = %d LIMIT 1',
            $order_id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        return null;
    }

    return slv_order_cast( $row );
}

/**
 * 根据订单号获取订单
 *
 * @since 1.0.0
 *
 * @param string $order_number 订单号
 *
 * @return array<string, mixed>|null
 */
function slv_order_get_by_number( string $order_number ): ?array {
    if ( '' === $order_number ) {
        return null;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_order_table() . ' WHERE order_number = %s LIMIT 1',
            $order_number
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        return null;
    }

    return slv_order_cast( $row );
}

/**
 * 订单行数据转换
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $row 数据库行
 *
 * @return array<string, mixed>
 */
function slv_order_cast( array $row ): array {
    $row['id']              = (int) $row['id'];
    $row['user_id']         = (int) $row['user_id'];
    $row['subtotal']        = (float) $row['subtotal'];
    $row['discount_total']  = (float) $row['discount_total'];
    $row['shipping_total']  = (float) $row['shipping_total'];
    $row['tax_total']       = (float) $row['tax_total'];
    $row['total']           = (float) $row['total'];

    $row['billing_data']  = slv_product_decode_json_object( $row['billing_data'] ?? null );
    $row['shipping_data'] = slv_product_decode_json_object( $row['shipping_data'] ?? null );
    $row['meta']          = slv_product_decode_json_object( $row['meta'] ?? null );

    return $row;
}

/**
 * 更新订单状态
 *
 * @since 1.0.0
 *
 * @param int    $order_id 订单ID
 * @param string $to       目标状态
 * @param string $note     备注
 *
 * @return bool
 */
function slv_order_update_status( int $order_id, string $to, string $note = '' ): bool {
    if ( $order_id <= 0 ) {
        slv_log( 'Order status update: invalid order_id', 'slv_commerce' );
        return false;
    }

    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        slv_log( "Order status update: order #{$order_id} not found", 'slv_commerce' );
        return false;
    }

    $from = (string) $order['status'];

    if ( ! slv_order_can_transition( $from, $to ) ) {
        slv_log( "Invalid order transition: {$from} -> {$to} (order #{$order_id})", 'slv_commerce' );
        return false;
    }

    global $wpdb;

    $update  = [ 'status' => $to ];
    $formats = [ '%s' ];

    if ( SLV_ORDER_STATUS_COMPLETED === $to ) {
        $update['completed_at'] = current_time( 'mysql' );
        $formats[] = '%s';
    }

    if ( SLV_ORDER_STATUS_PAID === $to ) {
        $update['paid_at']       = current_time( 'mysql' );
        $update['payment_status'] = 'paid';
        $formats[] = '%s';
        $formats[] = '%s';
    }

    $updated = $wpdb->update(
        slv_order_table(),
        $update,
        [ 'id' => $order_id ],
        $formats,
        [ '%d' ]
    );

    if ( false === $updated ) {
        slv_log( 'Order status update failed: ' . $wpdb->last_error, 'slv_commerce' );
        return false;
    }

    slv_order_log_status_change( $order_id, $from, $to, $note );

    do_action( 'slv_order_status_changed', $order_id, $from, $to );

    if ( SLV_ORDER_STATUS_COMPLETED === $to ) {
        do_action( 'slv_order_completed', $order_id );
    }

    if ( SLV_ORDER_STATUS_REFUNDED === $to ) {
        do_action( 'slv_order_refunded', $order_id );
    }

    return true;
}

/**
 * 更新订单字段
 *
 * @since 1.0.0
 *
 * @param int                  $order_id 订单ID
 * @param array<string, mixed> $data     更新数据
 *
 * @return bool
 */
function slv_order_update( int $order_id, array $data ): bool {
    if ( $order_id <= 0 ) {
        return false;
    }

    global $wpdb;

    $update  = [];
    $formats = [];

    $string_fields = [ 'order_number', 'status', 'currency', 'payment_gateway', 'payment_status', 'transaction_id', 'customer_note', 'admin_note', 'coupon_code' ];
    foreach ( $string_fields as $field ) {
        if ( isset( $data[ $field ] ) ) {
            $update[ $field ] = sanitize_text_field( (string) $data[ $field ] );
            $formats[] = '%s';
        }
    }

    $float_fields = [ 'subtotal', 'discount_total', 'shipping_total', 'tax_total', 'total' ];
    foreach ( $float_fields as $field ) {
        if ( isset( $data[ $field ] ) ) {
            $update[ $field ] = (float) $data[ $field ];
            $formats[] = '%f';
        }
    }

    if ( isset( $data['billing_data'] ) && is_array( $data['billing_data'] ) ) {
        $update['billing_data'] = wp_json_encode( $data['billing_data'] );
        $formats[] = '%s';
    }

    if ( isset( $data['shipping_data'] ) && is_array( $data['shipping_data'] ) ) {
        $update['shipping_data'] = wp_json_encode( $data['shipping_data'] );
        $formats[] = '%s';
    }

    if ( isset( $data['meta'] ) && is_array( $data['meta'] ) ) {
        $update['meta'] = wp_json_encode( $data['meta'] );
        $formats[] = '%s';
    }

    if ( empty( $update ) ) {
        return true;
    }

    $updated = $wpdb->update(
        slv_order_table(),
        $update,
        [ 'id' => $order_id ],
        $formats,
        [ '%d' ]
    );

    return false !== $updated;
}

/**
 * 记录订单状态变更
 *
 * @since 1.0.0
 *
 * @param int    $order_id   订单ID
 * @param string $from       原状态
 * @param string $to         目标状态
 * @param string $note       备注
 * @param int    $changed_by 操作人ID
 *
 * @return void
 */
function slv_order_log_status_change( int $order_id, string $from, string $to, string $note = '', int $changed_by = 0 ): void {
    if ( $order_id <= 0 || '' === $to ) {
        return;
    }

    global $wpdb;

    $wpdb->insert(
        slv_order_status_log_table(),
        [
            'order_id'    => $order_id,
            'from_status' => $from,
            'to_status'   => $to,
            'note'        => $note,
            'changed_by'  => $changed_by > 0 ? $changed_by : get_current_user_id(),
        ],
        [ '%d', '%s', '%s', '%s', '%d' ]
    );
}

/**
 * 获取订单状态变更记录
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return array<int, array<string, mixed>>
 */
function slv_order_get_status_log( int $order_id ): array {
    if ( $order_id <= 0 ) {
        return [];
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_order_status_log_table() . ' WHERE order_id = %d ORDER BY id ASC',
            $order_id
        ),
        ARRAY_A
    );

    return is_array( $rows ) ? $rows : [];
}

/**
 * 获取订单项
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return array<int, array<string, mixed>>
 */
function slv_order_get_items( int $order_id ): array {
    if ( $order_id <= 0 ) {
        return [];
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_order_item_table() . ' WHERE order_id = %d ORDER BY id ASC',
            $order_id
        ),
        ARRAY_A
    );

    if ( ! is_array( $rows ) ) {
        return [];
    }

    return array_map(
        static function ( array $row ): array {
            $row['id']              = (int) $row['id'];
            $row['order_id']        = (int) $row['order_id'];
            $row['product_id']      = (int) $row['product_id'];
            $row['variant_id']      = (int) $row['variant_id'];
            $row['quantity']        = (int) $row['quantity'];
            $row['unit_price']      = (float) $row['unit_price'];
            $row['subtotal']        = (float) $row['subtotal'];
            $row['discount']        = (float) $row['discount'];
            $row['tax']             = (float) $row['tax'];
            $row['total']           = (float) $row['total'];
            $row['linked_object_id'] = (int) $row['linked_object_id'];
            $row['meta']            = slv_product_decode_json_object( $row['meta'] ?? null );

            return $row;
        },
        $rows
    );
}

/**
 * 添加订单项
 *
 * @since 1.0.0
 *
 * @param int                  $order_id 订单ID
 * @param array<string, mixed> $data     订单项数据
 *
 * @return int 订单项ID，失败返回 0
 */
function slv_order_add_item( int $order_id, array $data ): int {
    if ( $order_id <= 0 ) {
        return 0;
    }

    global $wpdb;

    $record = [
        'order_id'           => $order_id,
        'product_id'         => isset( $data['product_id'] ) ? (int) $data['product_id'] : 0,
        'variant_id'         => isset( $data['variant_id'] ) ? (int) $data['variant_id'] : 0,
        'product_type'       => isset( $data['product_type'] ) ? sanitize_key( (string) $data['product_type'] ) : 'digital',
        'sku'                => isset( $data['sku'] ) ? sanitize_text_field( (string) $data['sku'] ) : '',
        'title'              => isset( $data['title'] ) ? sanitize_text_field( (string) $data['title'] ) : '',
        'unit_price'         => isset( $data['unit_price'] ) ? (float) $data['unit_price'] : 0.0,
        'quantity'           => isset( $data['quantity'] ) ? max( 1, (int) $data['quantity'] ) : 1,
        'subtotal'           => isset( $data['subtotal'] ) ? (float) $data['subtotal'] : 0.0,
        'discount'           => isset( $data['discount'] ) ? (float) $data['discount'] : 0.0,
        'tax'                => isset( $data['tax'] ) ? (float) $data['tax'] : 0.0,
        'total'              => isset( $data['total'] ) ? (float) $data['total'] : 0.0,
        'linked_object_type' => isset( $data['linked_object_type'] ) ? sanitize_key( (string) $data['linked_object_type'] ) : '',
        'linked_object_id'   => isset( $data['linked_object_id'] ) ? (int) $data['linked_object_id'] : 0,
        'meta'               => isset( $data['meta'] ) && is_array( $data['meta'] ) ? wp_json_encode( $data['meta'] ) : null,
    ];

    $formats = [
        '%d', '%d', '%d', '%s', '%s', '%s',
        '%f', '%d', '%f', '%f', '%f', '%f',
        '%s', '%d', '%s',
    ];

    $inserted = $wpdb->insert( slv_order_item_table(), $record, $formats );

    if ( false === $inserted ) {
        slv_log( 'Order item create failed: ' . $wpdb->last_error, 'slv_commerce' );
        return 0;
    }

    return (int) $wpdb->insert_id;
}

/**
 * 列出订单
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $args 查询参数
 *
 * @return array<int, array<string, mixed>>
 */
function slv_order_list( array $args = [] ): array {
    global $wpdb;

    $defaults = [
        'user_id' => 0,
        'status'  => '',
        'limit'   => 20,
        'offset'  => 0,
        'orderby' => 'created_at',
        'order'   => 'DESC',
    ];

    $args = wp_parse_args( $args, $defaults );

    $where  = [ '1=1' ];
    $params = [];

    if ( (int) $args['user_id'] > 0 ) {
        $where[]  = 'user_id = %d';
        $params[] = (int) $args['user_id'];
    }

    if ( '' !== (string) $args['status'] ) {
        $where[]  = 'status = %s';
        $params[] = (string) $args['status'];
    }

    $orderby_whitelist = [ 'id', 'created_at', 'total', 'status' ];
    $orderby = in_array( $args['orderby'], $orderby_whitelist, true ) ? $args['orderby'] : 'created_at';
    $order   = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';

    $limit  = max( 1, min( 500, (int) $args['limit'] ) );
    $offset = max( 0, (int) $args['offset'] );

    $sql = 'SELECT * FROM ' . slv_order_table() . ' WHERE ' . implode( ' AND ', $where )
         . " ORDER BY {$orderby} {$order}, id DESC LIMIT %d OFFSET %d";

    $params[] = $limit;
    $params[] = $offset;

    $rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

    if ( ! is_array( $rows ) ) {
        return [];
    }

    return array_map( 'slv_order_cast', $rows );
}

/**
 * 统计用户订单数
 *
 * @since 1.0.0
 *
 * @param int    $user_id 用户ID
 * @param string $status  状态筛选
 *
 * @return int
 */
function slv_order_count_user_orders( int $user_id, string $status = '' ): int {
    if ( $user_id <= 0 ) {
        return 0;
    }

    global $wpdb;

    if ( '' !== $status ) {
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . slv_order_table() . ' WHERE user_id = %d AND status = %s',
                $user_id,
                $status
            )
        );
    }

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            'SELECT COUNT(*) FROM ' . slv_order_table() . ' WHERE user_id = %d',
            $user_id
        )
    );
}