<?php
/**
 * 全站头部
 *
 * 结构：
 * - 顶部公告条（可选）
 * - Logo + 主导航 + 搜索 + 购物车 + 用户菜单
 * - 移动端抽屉菜单
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$slv_cart_count = function_exists( 'slv_cart_get_count' ) ? slv_cart_get_count() : 0;
$slv_cart_url   = function_exists( 'slv_commerce_get_cart_url' ) ? slv_commerce_get_cart_url() : home_url( '/cart/' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'slv-body' ); ?>>
<?php wp_body_open(); ?>

<a class="slv-skip-link screen-reader-text" href="#slv-main">
    <?php esc_html_e( '跳转到主内容', 'sunlyvo-nexus' ); ?>
</a>

<div id="slv-page" class="slv-page">

    <header id="slv-masthead" class="slv-header" role="banner">
        <div class="slv-header__inner">

            <!-- Logo -->
            <div class="slv-header__brand">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a class="slv-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- 一级导航 -->
            <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <nav class="slv-header__nav" role="navigation"
                     aria-label="<?php esc_attr_e( '主菜单', 'sunlyvo-nexus' ); ?>">
                    <?php
                    wp_nav_menu(
                        [
                            'theme_location' => 'primary',
                            'menu_id'        => 'slv-primary-menu',
                            'menu_class'     => 'slv-header__nav-list',
                            'container'      => false,
                            'depth'          => 2,
                            'fallback_cb'    => false,
                        ]
                    );
                    ?>
                </nav>
            <?php endif; ?>

            <!-- 辅助菜单 -->
            <div class="slv-header__actions">

                <!-- 搜索 -->
                <button type="button"
                        class="slv-header__action slv-header__search-toggle"
                        aria-label="<?php esc_attr_e( '搜索', 'sunlyvo-nexus' ); ?>"
                        aria-expanded="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </button>

                <!-- 购物车 -->
                <?php if ( function_exists( 'slv_commerce_get_cart_url' ) ) : ?>
                    <a class="slv-header__action slv-header__cart"
                       href="<?php echo esc_url( $slv_cart_url ); ?>"
                       aria-label="<?php esc_attr_e( '购物车', 'sunlyvo-nexus' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <?php if ( $slv_cart_count > 0 ) : ?>
                            <span class="slv-header__cart-count"><?php echo esc_html( (string) $slv_cart_count ); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- 用户菜单 -->
                <?php if ( is_user_logged_in() ) : ?>
                    <?php
                    $slv_user     = wp_get_current_user();
                    $slv_account  = function_exists( 'slv_commerce_get_checkout_url' )
                        ? get_permalink( (int) get_option( 'commerce.account_page_id', 0 ) )
                        : home_url( '/account/' );
                    $slv_account  = $slv_account ?: home_url( '/account/' );
                    ?>
                    <div class="slv-header__user">
                        <button type="button"
                                class="slv-header__action slv-header__user-toggle"
                                aria-label="<?php esc_attr_e( '用户菜单', 'sunlyvo-nexus' ); ?>"
                                aria-expanded="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="slv-header__user-menu" role="menu">
                            <div class="slv-header__user-name">
                                <?php echo esc_html( $slv_user->display_name ); ?>
                            </div>
                            <a href="<?php echo esc_url( $slv_account ); ?>" role="menuitem">
                                <?php esc_html_e( '我的账户', 'sunlyvo-nexus' ); ?>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( 'post_type', 'page', home_url( '/my-orders/' ) ) ); ?>" role="menuitem">
                                <?php esc_html_e( '我的订单', 'sunlyvo-nexus' ); ?>
                            </a>
                            <a href="<?php echo esc_url( home_url( '/affiliate/' ) ); ?>" role="menuitem">
                                <?php esc_html_e( '分销中心', 'sunlyvo-nexus' ); ?>
                            </a>
                            <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" role="menuitem" class="slv-header__user-logout">
                                <?php esc_html_e( '退出登录', 'sunlyvo-nexus' ); ?>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <a class="slv-header__action slv-header__login"
                       href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>">
                        <?php esc_html_e( '登录', 'sunlyvo-nexus' ); ?>
                    </a>
                <?php endif; ?>

                <!-- 移动端菜单按钮 -->
                <button type="button"
                        class="slv-header__action slv-header__menu-toggle"
                        aria-label="<?php esc_attr_e( '菜单', 'sunlyvo-nexus' ); ?>"
                        aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>

            </div>
        </div>

        <!-- 搜索面板（默认隐藏） -->
        <div class="slv-header__search-panel" aria-hidden="true">
            <div class="slv-header__search-inner">
                <?php get_search_form(); ?>
            </div>
        </div>

        <!-- 移动端抽屉菜单 -->
        <div class="slv-header__mobile-nav" aria-hidden="true">
            <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <?php
                wp_nav_menu(
                    [
                        'theme_location' => 'primary',
                        'menu_class'     => 'slv-header__mobile-list',
                        'container'      => false,
                        'depth'          => 2,
                        'fallback_cb'    => false,
                    ]
                );
                ?>
            <?php endif; ?>

            <?php if ( has_nav_menu( 'footer' ) ) : ?>
                <div class="slv-header__mobile-footer">
                    <?php
                    wp_nav_menu(
                        [
                            'theme_location' => 'footer',
                            'menu_class'     => 'slv-header__mobile-footer-list',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ]
                    );
                    ?>
                </div>
            <?php endif; ?>
        </div>

    </header>