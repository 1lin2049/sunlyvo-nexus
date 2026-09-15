<?php
/**
 * 角色注册
 *
 * 12+ 角色定义与注册，主题激活时执行
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取所有角色定义
 *
 * @since 1.0.0
 * @return array<string, array{name:string, capabilities:array<string, bool>}>
 */
function slv_get_role_definitions(): array {
    $roles = [
        'region_admin' => [
            'name'         => '区域管理员',
            'capabilities' => [
                'read'                     => true,
                'slv_manage_region'        => true,
                'slv_manage_station'       => true,
                'slv_manage_config'        => true,
                'slv_review_content'       => true,
                'slv_manage_warehouse'     => true,
                'slv_manage_listing_rules' => true,
            ],
        ],
        'station_master' => [
            'name'         => '站长',
            'capabilities' => [
                'read'                     => true,
                'upload_files'             => true,
                'slv_manage_station'       => true,
                'slv_manage_listing_rules' => true,
                'slv_manage_warehouse'     => true,
                'slv_create_campaign'      => true,
                'slv_review_content'       => true,
                'slv_manage_config'        => true,
            ],
        ],
        'vendor' => [
            'name'         => '商户',
            'capabilities' => [
                'read'                       => true,
                'upload_files'               => true,
                'edit_posts'                 => true,
                'publish_posts'              => true,
                'slv_manage_vendor_products' => true,
                'slv_manage_vendor_orders'   => true,
                'slv_view_vendor_earnings'   => true,
                'slv_manage_config'          => true,
            ],
        ],
        'vendor_staff' => [
            'name'         => '商户员工',
            'capabilities' => [
                'read'                       => true,
                'upload_files'               => true,
                'slv_manage_vendor_products' => true,
                'slv_view_vendor_earnings'   => true,
            ],
        ],
        'creator' => [
            'name'         => '创作者',
            'capabilities' => [
                'read'             => true,
                'upload_files'     => true,
                'edit_posts'       => true,
                'publish_posts'    => true,
                'slv_create_wiki'  => true,
                'slv_create_campaign' => true,
            ],
        ],
        'company_admin' => [
            'name'         => '企业管理员',
            'capabilities' => [
                'read'                      => true,
                'slv_manage_company'        => true,
                'slv_manage_sub_accounts'   => true,
                'slv_place_order'           => true,
                'slv_view_wholesale_price'  => true,
            ],
        ],
        'company_buyer' => [
            'name'         => '企业采购员',
            'capabilities' => [
                'read'                      => true,
                'slv_place_order'           => true,
                'slv_view_wholesale_price'  => true,
            ],
        ],
        'company_viewer' => [
            'name'         => '企业查看员',
            'capabilities' => [
                'read'                      => true,
                'slv_view_wholesale_price'  => true,
            ],
        ],
        'wholesale_customer' => [
            'name'         => '批发客户',
            'capabilities' => [
                'read'                      => true,
                'slv_view_wholesale_price'  => true,
                'slv_place_order'           => true,
            ],
        ],
        'pending_wholesale' => [
            'name'         => '待审批批发',
            'capabilities' => [
                'read' => true,
            ],
        ],
        'customer' => [
            'name'         => '普通客户',
            'capabilities' => [
                'read'             => true,
                'slv_place_order'  => true,
                'slv_access_asset' => true,
            ],
        ],
    ];

    /**
     * 过滤角色定义
     *
     * @since 1.0.0
     *
     * @param array $roles 角色定义
     */
    return (array) apply_filters( 'slv_role_definitions', $roles );
}

/**
 * 注册所有自定义角色
 *
 * 已存在的角色不覆盖，避免管理员手动调整被重置
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_roles(): void {
    foreach ( slv_get_role_definitions() as $slug => $def ) {
        $existing = get_role( $slug );

        if ( null !== $existing ) {
            continue;
        }

        add_role( $slug, $def['name'], $def['capabilities'] );
    }
}

/**
 * 移除所有自定义角色
 *
 * 仅用于测试或卸载
 *
 * @since 1.0.0
 * @return void
 */
function slv_unregister_roles(): void {
    foreach ( array_keys( slv_get_role_definitions() ) as $slug ) {
        remove_role( $slug );
    }
}

/**
 * 强制刷新角色权限
 *
 * 用于角色定义更新后同步到已存在的角色
 *
 * @since 1.0.0
 * @return void
 */
function slv_refresh_roles(): void {
    foreach ( slv_get_role_definitions() as $slug => $def ) {
        $role = get_role( $slug );

        if ( null === $role ) {
            continue;
        }

        foreach ( $def['capabilities'] as $cap => $grant ) {
            if ( $grant ) {
                $role->add_cap( $cap );
            } else {
                $role->remove_cap( $cap );
            }
        }
    }
}

// 主题激活时注册
add_action( 'after_switch_theme', 'slv_register_roles', 10 );