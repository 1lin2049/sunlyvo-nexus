<?php
/**
 * WP-CLI 初始化命令
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
 * 一键初始化主题
 *
 * ## EXAMPLES
 *
 *     wp slv setup
 *
 * @param array $args       位置参数
 * @param array $assoc_args 关联参数
 *
 * @return void
 */
function slv_cli_setup( $args, $assoc_args ): void {
    WP_CLI::log( '开始初始化 SunLyvo Nexus…' );

    // 1. 创建菜单
    slv_cli_setup_menus();

    // 2. 创建基础页面
    slv_cli_setup_pages();

    // 3. 设置静态首页
    slv_cli_setup_front_page();

    // 4. 设置文章页
    slv_cli_setup_posts_page();

    // 5. 创建示例内容
    slv_cli_setup_content();

    // 6. 刷新 rewrite
    flush_rewrite_rules();

    WP_CLI::success( '初始化完成。' );
}

/**
 * 创建菜单
 *
 * @return void
 */
function slv_cli_setup_menus(): void {
    $locations = [
        'primary' => '主菜单',
        'footer'  => '页脚菜单',
    ];

    foreach ( $locations as $location => $name ) {
        $menu = wp_get_nav_menu_object( $name );

        if ( ! $menu ) {
            $menu_id = wp_create_nav_menu( $name );

            if ( is_wp_error( $menu_id ) ) {
                WP_CLI::warning( "创建菜单失败：{$name}" );
                continue;
            }
        } else {
            $menu_id = (int) $menu->term_id;
        }

        // 获取菜单位置
        $theme_locations           = get_theme_mod( 'nav_menu_locations', [] );
        $theme_locations[ $location ] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $theme_locations );

        WP_CLI::log( "✓ 菜单 {$name}（ID: {$menu_id}）" );
    }
}

/**
 * 创建基础页面
 *
 * @return void
 */
function slv_cli_setup_pages(): void {
    $pages = [
        [ 'title' => '首页',     'slug' => 'home' ],
        [ 'title' => '博客',     'slug' => 'blog' ],
        [ 'title' => '关于',     'slug' => 'about' ],
        [ 'title' => '购物车',   'slug' => 'cart',         'template' => 'page-templates/cart.php' ],
        [ 'title' => '结算',     'slug' => 'checkout',     'template' => 'page-templates/checkout.php' ],
        [ 'title' => '我的订单', 'slug' => 'my-orders',    'template' => 'page-templates/my-orders.php' ],
        [ 'title' => '订单详情', 'slug' => 'order-view',   'template' => 'page-templates/order-view.php' ],
        [ 'title' => '用户中心', 'slug' => 'account',      'template' => 'page-templates/account.php' ],
        [ 'title' => '分销中心', 'slug' => 'affiliate',    'template' => 'page-templates/affiliate.php' ],
        [ 'title' => '试读目录', 'slug' => 'preview',      'template' => 'page-templates/preview.php' ],
    ];

    foreach ( $pages as $page ) {
        $existing = get_page_by_path( $page['slug'] );

        if ( $existing ) {
            if ( isset( $page['template'] ) ) {
                update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
            }
            WP_CLI::log( "· 页面已存在：{$page['title']}（ID: {$existing->ID}）" );
            continue;
        }

        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $page['title'],
                'post_name'    => $page['slug'],
                'post_content' => '',
            ]
        );

        if ( is_wp_error( $page_id ) ) {
            WP_CLI::warning( "创建失败：{$page['title']}" );
            continue;
        }

        if ( isset( $page['template'] ) ) {
            update_post_meta( $page_id, '_wp_page_template', $page['template'] );
        }

        WP_CLI::log( "✓ 页面 {$page['title']}（ID: {$page_id}）" );
    }
}

/**
 * 设置静态首页
 *
 * @return void
 */
function slv_cli_setup_front_page(): void {
    $home = get_page_by_path( 'home' );

    if ( ! $home ) {
        WP_CLI::warning( '未找到首页。' );
        return;
    }

    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', (int) $home->ID );

    WP_CLI::log( "✓ 静态首页：{$home->post_title}" );
}

/**
 * 设置文章页
 *
 * @return void
 */
function slv_cli_setup_posts_page(): void {
    $blog = get_page_by_path( 'blog' );

    if ( ! $blog ) {
        WP_CLI::warning( '未找到博客页。' );
        return;
    }

    update_option( 'page_for_posts', (int) $blog->ID );

    WP_CLI::log( "✓ 文章页：{$blog->post_title}" );
}

/**
 * 创建示例内容
 *
 * @return void
 */
function slv_cli_setup_content(): void {
    // 创建示例博客
    $existing = get_page_by_path( 'hello-sunlyvo', OBJECT, 'post' );

    if ( ! $existing ) {
        $post_id = wp_insert_post(
            [
                'post_type'    => 'post',
                'post_status'  => 'publish',
                'post_title'   => '欢迎来到 SunLyvo Nexus',
                'post_name'    => 'hello-sunlyvo',
                'post_content' => '<p>这是 SunLyvo Nexus 的第一篇文章。内容电商平台已就绪，开始你的创作之旅。</p>',
                'post_excerpt' => '这是 SunLyvo Nexus 的第一篇文章。',
            ]
        );

        if ( ! is_wp_error( $post_id ) ) {
            WP_CLI::log( "✓ 示例文章（ID: {$post_id}）" );
        }
    }
}

WP_CLI::add_command( 'slv setup', 'slv_cli_setup' );