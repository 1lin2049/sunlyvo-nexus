<?php
/**
 * WP-CLI 菜单填充
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

/**
 * 填充菜单项
 *
 * ## EXAMPLES
 *
 *     wp slv fill_menus
 *
 * @param array $args       位置参数
 * @param array $assoc_args 关联参数
 *
 * @return void
 */
function slv_cli_fill_menus( $args, $assoc_args ): void {
    $menus = [
        '主菜单' => [
            [ 'type' => 'page', 'slug' => 'home' ],
            [ 'type' => 'archive', 'post_type' => 'slv_collection', 'title' => '合集' ],
            [ 'type' => 'page', 'slug' => 'blog' ],
            [ 'type' => 'page', 'slug' => 'about' ],
        ],
        '页脚菜单' => [
            [ 'type' => 'page', 'slug' => 'about' ],
            [ 'type' => 'page', 'slug' => 'privacy-policy' ],
            [ 'type' => 'page', 'slug' => 'blog' ],
        ],
    ];

    foreach ( $menus as $menu_name => $items ) {
        $menu = wp_get_nav_menu_object( $menu_name );

        if ( ! $menu ) {
            WP_CLI::warning( "菜单不存在：{$menu_name}" );
            continue;
        }

        $menu_id = (int) $menu->term_id;

        // 清空现有项目
        $existing = wp_get_nav_menu_items( $menu_id );

        if ( is_array( $existing ) ) {
            foreach ( $existing as $item ) {
                wp_delete_post( (int) $item->ID, true );
            }
        }

        $position = 1;

        foreach ( $items as $item ) {
            if ( 'page' === $item['type'] ) {
                $page = get_page_by_path( $item['slug'] );

                if ( ! $page ) {
                    WP_CLI::log( "  · 跳过缺失页面：{$item['slug']}" );
                    continue;
                }

                wp_update_nav_menu_item(
                    $menu_id,
                    0,
                    [
                        'menu-item-title'     => $page->post_title,
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => (int) $page->ID,
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                        'menu-item-position'  => $position++,
                    ]
                );

                WP_CLI::log( "  ✓ {$menu_name} → {$page->post_title}" );

            } elseif ( 'archive' === $item['type'] ) {
                $post_type = (string) $item['post_type'];
                $archive   = get_post_type_archive_link( $post_type );

                if ( ! $archive ) {
                    WP_CLI::log( "  · 跳过无归档的类型：{$post_type}" );
                    continue;
                }

                wp_update_nav_menu_item(
                    $menu_id,
                    0,
                    [
                        'menu-item-title'  => (string) $item['title'],
                        'menu-item-url'    => $archive,
                        'menu-item-type'   => 'custom',
                        'menu-item-status' => 'publish',
                        'menu-item-position' => $position++,
                    ]
                );

                WP_CLI::log( "  ✓ {$menu_name} → {$item['title']}" );
            }
        }
    }

    WP_CLI::success( '菜单填充完成。' );
}

WP_CLI::add_command( 'slv fill_menus', 'slv_cli_fill_menus' );