<?php
/**
 * 会员等级 CRUD
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取所有会员等级
 *
 * @since 1.0.0
 *
 * @param bool $active_only 仅返回启用状态
 *
 * @return array<int, array<string, mixed>>
 */
function slv_get_member_levels( bool $active_only = true ): array {
    global $wpdb;

    $where = $active_only ? 'WHERE is_active = 1' : '';

    $rows = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}slv_member_levels {$where} ORDER BY level_order ASC, id ASC",
        ARRAY_A
    );

    return is_array( $rows ) ? $rows : [];
}

/**
 * 获取单个会员等级
 *
 * @since 1.0.0
 *
 * @param string $slug 等级 slug
 *
 * @return array<string, mixed>|null
 */
function slv_get_member_level( string $slug ): ?array {
    if ( '' === $slug ) {
        return null;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}slv_member_levels WHERE level_slug = %s AND is_active = 1 LIMIT 1",
            $slug
        ),
        ARRAY_A
    );

    return $row ?: null;
}

/**
 * 获取默认等级（最低 order）
 *
 * @since 1.0.0
 * @return array<string, mixed>|null
 */
function slv_get_default_member_level(): ?array {
    global $wpdb;

    $row = $wpdb->get_row(
        "SELECT * FROM {$wpdb->prefix}slv_member_levels WHERE is_active = 1 ORDER BY level_order ASC, id ASC LIMIT 1",
        ARRAY_A
    );

    return $row ?: null;
}

/**
 * 保存会员等级（新增或更新）
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $data 等级数据
 *
 * @return int 等级ID，失败返回 0
 */
function slv_save_member_level( array $data ): int {
    global $wpdb;

    $slug = isset( $data['level_slug'] ) ? sanitize_key( (string) $data['level_slug'] ) : '';

    if ( '' === $slug ) {
        return 0;
    }

    $record = [
        'level_name'        => isset( $data['level_name'] ) ? sanitize_text_field( (string) $data['level_name'] ) : '',
        'level_slug'        => $slug,
        'level_order'       => isset( $data['level_order'] ) ? (int) $data['level_order'] : 0,
        'required_spent'    => isset( $data['required_spent'] ) ? (float) $data['required_spent'] : 0,
        'required_points'   => isset( $data['required_points'] ) ? (int) $data['required_points'] : 0,
        'discount_rate'     => isset( $data['discount_rate'] ) ? (float) $data['discount_rate'] : 0,
        'points_multiplier' => isset( $data['points_multiplier'] ) ? (float) $data['points_multiplier'] : 1.0,
        'benefits'          => isset( $data['benefits'] ) ? sanitize_textarea_field( (string) $data['benefits'] ) : '',
        'is_active'         => isset( $data['is_active'] ) ? (int) $data['is_active'] : 1,
    ];

    $formats = [ '%s', '%s', '%d', '%f', '%d', '%f', '%f', '%s', '%d' ];

    $existing_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}slv_member_levels WHERE level_slug = %s",
            $slug
        )
    );

    if ( $existing_id > 0 ) {
        $wpdb->update(
            "{$wpdb->prefix}slv_member_levels",
            $record,
            [ 'id' => $existing_id ],
            $formats,
            [ '%d' ]
        );
        return $existing_id;
    }

    $wpdb->insert( "{$wpdb->prefix}slv_member_levels", $record, $formats );

    return (int) $wpdb->insert_id;
}

/**
 * 删除会员等级
 *
 * @since 1.0.0
 *
 * @param int $level_id 等级ID
 *
 * @return bool
 */
function slv_delete_member_level( int $level_id ): bool {
    if ( $level_id <= 0 ) {
        return false;
    }

    global $wpdb;

    $result = $wpdb->delete(
        "{$wpdb->prefix}slv_member_levels",
        [ 'id' => $level_id ],
        [ '%d' ]
    );

    return false !== $result;
}

/**
 * 根据累计消费和积分计算应得等级
 *
 * @since 1.0.0
 *
 * @param float $total_spent 累计消费
 * @param int   $total_points 累计积分
 *
 * @return array<string, mixed>|null
 */
function slv_calculate_member_level( float $total_spent, int $total_points ): ?array {
    $levels = slv_get_member_levels();

    if ( empty( $levels ) ) {
        return null;
    }

    // 从高到低查找第一个满足条件的等级
    $levels_sorted = array_reverse( $levels );

    foreach ( $levels_sorted as $level ) {
        $need_spent  = (float) $level['required_spent'];
        $need_points = (int) $level['required_points'];

        // 满足任一条件即可
        if ( $total_spent >= $need_spent && $need_spent > 0 ) {
            return $level;
        }

        if ( $total_points >= $need_points && $need_points > 0 ) {
            return $level;
        }

        // 默认等级（0/0）
        if ( 0.0 === $need_spent && 0 === $need_points ) {
            return $level;
        }
    }

    return $levels[0] ?? null;
}