<?php
/**
 * 统一权限判断
 *
 * slv_user_can() 是所有业务权限的唯一入口
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取能力与角色的映射
 *
 * @since 1.0.0
 * @return array<string, array<int, string>>
 */
function slv_get_capability_role_map(): array {
    $map = [
        'view_wholesale_price'   => [
            'administrator',
            'region_admin',
            'station_master',
            'company_admin',
            'company_buyer',
            'company_viewer',
            'wholesale_customer',
        ],
        'manage_vendor_products' => [
            'administrator',
            'vendor',
            'vendor_staff',
        ],
        'place_order'            => [
            'administrator',
            'customer',
            'wholesale_customer',
            'company_admin',
            'company_buyer',
        ],
        'access_asset'           => [
            'administrator',
            'customer',
            'wholesale_customer',
            'company_admin',
            'company_buyer',
            'company_viewer',
            'creator',
        ],
        'download_document'      => [
            'administrator',
            'customer',
        ],
        'watch_video'            => [
            'administrator',
            'customer',
        ],
        'create_wiki'            => [
            'administrator',
            'creator',
        ],
        'join_campaign'          => [
            'administrator',
            'customer',
        ],
        'manage_city_site'       => [
            'administrator',
            'region_admin',
            'station_master',
        ],
        'create_campaign'        => [
            'administrator',
            'creator',
            'station_master',
        ],
        'review_content'         => [
            'administrator',
            'region_admin',
            'station_master',
        ],
        'manage_service_config'  => [
            'administrator',
            'region_admin',
            'station_master',
            'vendor',
        ],
        'manage_config'          => [
            'administrator',
            'region_admin',
            'station_master',
            'vendor',
        ],
        'manage_warehouse'       => [
            'administrator',
            'region_admin',
            'station_master',
        ],
        'manage_listing_rules'   => [
            'administrator',
            'station_master',
        ],
    ];

    /**
     * 过滤能力-角色映射
     *
     * @since 1.0.0
     *
     * @param array $map 能力-角色映射
     */
    return (array) apply_filters( 'slv_capability_role_map', $map );
}

/**
 * 检查用户是否拥有指定能力
 *
 * 这是所有业务权限判断的唯一入口。
 *
 * @since 1.0.0
 *
 * @param string     $capability 能力标识
 * @param array|null $context    上下文，支持 owner_id / owner_type
 *
 * @return bool
 */
function slv_user_can( string $capability, ?array $context = null ): bool {
    if ( '' === $capability ) {
        return false;
    }

    $user_id = get_current_user_id();

    if ( ! $user_id ) {
        return false;
    }

    if ( is_super_admin( $user_id ) ) {
        return true;
    }

    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    if ( empty( $roles ) ) {
        return false;
    }

    $map           = slv_get_capability_role_map();
    $allowed_roles = $map[ $capability ] ?? [];

    if ( empty( $allowed_roles ) ) {
        return false;
    }

    if ( empty( array_intersect( $roles, $allowed_roles ) ) ) {
        return false;
    }

    if ( is_array( $context ) && isset( $context['owner_id'] ) ) {
        return slv_check_resource_ownership( $user_id, $capability, $context );
    }

    return true;
}

/**
 * 检查资源所有权
 *
 * 默认返回 true；模块通过 filter 注入具体判断逻辑
 *
 * @since 1.0.0
 *
 * @param int    $user_id    用户ID
 * @param string $capability 能力标识
 * @param array  $context    上下文
 *
 * @return bool
 */
function slv_check_resource_ownership( int $user_id, string $capability, array $context ): bool {
    $owner_id   = (int) ( $context['owner_id'] ?? 0 );
    $owner_type = (string) ( $context['owner_type'] ?? '' );

    if ( $owner_id <= 0 || '' === $owner_type ) {
        return true;
    }

    /**
     * 过滤资源所有权检查结果
     *
     * @since 1.0.0
     *
     * @param bool   $allowed    默认结果
     * @param int    $user_id    用户ID
     * @param string $capability 能力标识
     * @param int    $owner_id   资源所有者ID
     * @param string $owner_type 资源所有者类型
     * @param array  $context    上下文
     */
    return (bool) apply_filters(
        'slv_check_resource_ownership',
        true,
        $user_id,
        $capability,
        $owner_id,
        $owner_type,
        $context
    );
}

/**
 * 判断当前用户是否为指定角色
 *
 * @since 1.0.0
 *
 * @param string|array $roles 角色或角色数组
 *
 * @return bool
 */
function slv_user_has_role( $roles ): bool {
    $user = wp_get_current_user();

    if ( ! $user->exists() ) {
        return false;
    }

    $user_roles = (array) $user->roles;
    $check      = (array) $roles;

    return ! empty( array_intersect( $user_roles, $check ) );
}