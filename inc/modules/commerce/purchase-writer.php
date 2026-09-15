<?php
/**
 * 订单完成 → 写入购买记录 → 触发访问控制
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'slv_order_completed', 'slv_purchase_writer_on_order_completed', 10, 1 );

/**
 * 订单完成时写入购买记录
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return void
 */
function slv_purchase_writer_on_order_completed( int $order_id ): void {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return;
    }

    $user_id = (int) $order['user_id'];

    if ( $user_id <= 0 ) {
        return;
    }

    $items = slv_order_get_items( $order_id );

    if ( empty( $items ) ) {
        return;
    }

    foreach ( $items as $item ) {
        $object_type = (string) $item['linked_object_type'];
        $object_id   = (int) $item['linked_object_id'];

        if ( '' === $object_type || $object_id <= 0 ) {
            continue;
        }

        // 只处理内容和订阅类型
        if ( ! in_array( $object_type, [ 'chapter', 'collection' ], true ) ) {
            continue;
        }

        slv_record_purchase(
            $user_id,
            $object_type,
            $object_id,
            (float) $item['total'],
            $order_id
        );
    }

    /**
     * 购买记录写入完成
     *
     * @since 1.0.0
     *
     * @param int   $order_id 订单ID
     * @param int   $user_id  用户ID
     * @param array $items    订单项
     */
    do_action( 'slv_purchase_written', $order_id, $user_id, $items );
}

add_action( 'slv_order_refunded', 'slv_purchase_writer_on_order_refunded', 10, 1 );

/**
 * 订单退款时撤销购买记录
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return void
 */
function slv_purchase_writer_on_order_refunded( int $order_id ): void {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return;
    }

    $user_id = (int) $order['user_id'];

    if ( $user_id <= 0 ) {
        return;
    }

    global $wpdb;

    $table = $wpdb->prefix . 'slv_user_purchases';

    foreach ( slv_order_get_items( $order_id ) as $item ) {
        $object_type = (string) $item['linked_object_type'];
        $object_id   = (int) $item['linked_object_id'];

        if ( '' === $object_type || $object_id <= 0 ) {
            continue;
        }

        $wpdb->update(
            $table,
            [ 'status' => 'refunded' ],
            [
                'user_id'     => $user_id,
                'object_type' => $object_type,
                'object_id'   => $object_id,
            ],
            [ '%s' ],
            [ '%d', '%s', '%d' ]
        );

        /**
         * 购买记录被撤销
         *
         * @since 1.0.0
         *
         * @param int    $user_id     用户ID
         * @param string $object_type 对象类型
         * @param int    $object_id   对象ID
         */
        do_action( 'slv_purchase_revoked', $user_id, $object_type, $object_id );
    }
}