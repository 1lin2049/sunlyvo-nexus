<?php
/**
 * 购买记录
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 记录购买
 *
 * @since 1.0.0
 *
 * @param int    $user_id     用户ID
 * @param string $object_type chapter | collection
 * @param int    $object_id   对象ID
 * @param float  $price       价格
 * @param int    $order_id    订单ID（0 为直接购买）
 * @param int    $points_used 使用积分
 * @param int    $expire_days 过期天数
 *
 * @return bool
 */
function slv_record_purchase( int $user_id, string $object_type, int $object_id, float $price = 0.0, int $order_id = 0, int $points_used = 0, int $expire_days = 0 ): bool {
    if ( $user_id <= 0 || '' === $object_type || $object_id <= 0 ) {
        return false;
    }

    if ( ! in_array( $object_type, [ 'chapter', 'collection' ], true ) ) {
        return false;
    }

    global $wpdb;

    $expires_at = null;

    if ( $expire_days > 0 ) {
        $expires_at = gmdate( 'Y-m-d H:i:s', time() + ( $expire_days * DAY_IN_SECONDS ) );
    }

    // 先尝试更新已有记录
    $existing_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}slv_user_purchases
             WHERE user_id = %d AND object_type = %s AND object_id = %d",
            $user_id,
            $object_type,
            $object_id
        )
    );

    if ( $existing_id > 0 ) {
        $wpdb->update(
            "{$wpdb->prefix}slv_user_purchases",
            [
                'price'       => $price,
                'status'      => 'completed',
                'expires_at'  => $expires_at,
            ],
            [ 'id' => $existing_id ],
            [ '%f', '%s', '%s' ],
            [ '%d' ]
        );
    } else {
        $wpdb->insert(
            "{$wpdb->prefix}slv_user_purchases",
            [
                'user_id'      => $user_id,
                'object_type'  => $object_type,
                'object_id'    => $object_id,
                'order_id'     => $order_id,
                'price'        => $price,
                'points_used'  => $points_used,
                'status'       => 'completed',
                'expires_at'   => $expires_at,
            ],
            [ '%d', '%s', '%d', '%d', '%f', '%d', '%s', '%s' ]
        );
    }

    /**
     * 购买记录创建后触发
     *
     * @since 1.0.0
     *
     * @param int    $user_id     用户ID
     * @param string $object_type 对象类型
     * @param int    $object_id   对象ID
     * @param float  $price       价格
     */
    do_action( 'slv_purchase_recorded', $user_id, $object_type, $object_id, $price );

    // 触发购买完成钩子（会员、积分模块监听）
    do_action(
        'slv_purchase_completed',
        $user_id,
        $order_id,
        [
            'amount'      => $price,
            'object_type' => $object_type,
            'object_id'   => $object_id,
        ]
    );

    return true;
}

/**
 * 检查用户是否购买过指定对象
 *
 * @since 1.0.0
 *
 * @param int    $user_id     用户ID
 * @param string $object_type 对象类型
 * @param int    $object_id   对象ID
 *
 * @return bool
 */
function slv_user_has_purchase( int $user_id, string $object_type, int $object_id ): bool {
    if ( $user_id <= 0 || $object_id <= 0 ) {
        return false;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, expires_at FROM {$wpdb->prefix}slv_user_purchases
             WHERE user_id = %d AND object_type = %s AND object_id = %d AND status = 'completed'
             LIMIT 1",
            $user_id,
            $object_type,
            $object_id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        return false;
    }

    if ( ! empty( $row['expires_at'] ) ) {
        $expires_ts = strtotime( (string) $row['expires_at'] );

        if ( $expires_ts && $expires_ts < time() ) {
            return false;
        }
    }

    return true;
}

/**
 * 获取用户所有购买记录
 *
 * @since 1.0.0
 *
 * @param int    $user_id     用户ID
 * @param string $object_type 对象类型（空表示全部）
 * @param int    $limit       条数
 * @param int    $offset      偏移
 *
 * @return array<int, array<string, mixed>>
 */
function slv_get_user_purchases( int $user_id, string $object_type = '', int $limit = 20, int $offset = 0 ): array {
    if ( $user_id <= 0 ) {
        return [];
    }

    global $wpdb;

    if ( '' !== $object_type ) {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}slv_user_purchases
                 WHERE user_id = %d AND object_type = %s
                 ORDER BY created_at DESC, id DESC
                 LIMIT %d OFFSET %d",
                $user_id,
                $object_type,
                $limit,
                $offset
            ),
            ARRAY_A
        );
    } else {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}slv_user_purchases
                 WHERE user_id = %d
                 ORDER BY created_at DESC, id DESC
                 LIMIT %d OFFSET %d",
                $user_id,
                $limit,
                $offset
            ),
            ARRAY_A
        );
    }

    return is_array( $rows ) ? $rows : [];
}

/**
 * 统计用户购买数量
 *
 * @since 1.0.0
 *
 * @param int    $user_id     用户ID
 * @param string $object_type 对象类型
 *
 * @return int
 */
function slv_count_user_purchases( int $user_id, string $object_type = '' ): int {
    if ( $user_id <= 0 ) {
        return 0;
    }

    global $wpdb;

    if ( '' !== $object_type ) {
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}slv_user_purchases
                 WHERE user_id = %d AND object_type = %s AND status = 'completed'",
                $user_id,
                $object_type
            )
        );
    }

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}slv_user_purchases
             WHERE user_id = %d AND status = 'completed'",
            $user_id
        )
    );
}

/**
 * 模拟购买（用于开发/测试，不涉及真实支付）
 *
 * @since 1.0.0
 *
 * @param int    $user_id     用户ID
 * @param string $object_type 对象类型
 * @param int    $object_id   对象ID
 *
 * @return bool
 */
function slv_simulate_purchase( int $user_id, string $object_type, int $object_id ): bool {
    if ( 'chapter' === $object_type ) {
        $config = slv_get_chapter_access_config( $object_id );
        $price  = (float) $config['price'];
    } elseif ( 'collection' === $object_type ) {
        $price = (float) get_post_meta( $object_id, '_slv_collection_price', true );
    } else {
        return false;
    }

    return slv_record_purchase( $user_id, $object_type, $object_id, $price );
}