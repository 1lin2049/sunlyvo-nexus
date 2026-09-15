<?php
/**
 * SunLyvo 控制面板 (完全隔离版)
 *
 * 不依赖任何电商模块常量或函数，确保后台始终可访问。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'slv_admin_dashboard_menu', 22 );

function slv_admin_dashboard_menu(): void {
    add_menu_page(
        esc_html__( 'SunLyvo 控制面板', 'sunlyvo-nexus' ),
        esc_html__( 'SunLyvo', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-dashboard',
        'slv_admin_dashboard_page',
        'dashicons-superhero-alt',
        25
    );
}

function slv_admin_dashboard_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    $stats = slv_admin_dashboard_get_stats();

    $project_uri = defined( 'SLV_PROJECT_URI' ) ? SLV_PROJECT_URI : 'https://nexus.getthink.info';
    $developer   = defined( 'SLV_DEVELOPER_NAME' ) ? SLV_DEVELOPER_NAME : '李咏燊';
    $slv_version = defined( 'SLV_VERSION' ) ? SLV_VERSION : '1.0.0';
    ?>
    <div class="wrap slv-dashboard-wrap">
        <h1 class="slv-dashboard-title">
            <span class="dashicons dashicons-superhero-alt"></span>
            <?php esc_html_e( 'SunLyvo Nexus 控制面板', 'sunlyvo-nexus' ); ?>
        </h1>

        <p class="slv-dashboard-subtitle">
            <?php
            printf(
                esc_html__( '项目：%1$s · 开发者：%2$s', 'sunlyvo-nexus' ),
                '<a href="' . esc_url( $project_uri ) . '" target="_blank" rel="noopener">' . esc_html( $project_uri ) . '</a>',
                esc_html( $developer )
            );
            ?>
        </p>

        <div class="slv-dash-grid slv-dash-grid--stats">
            <?php
            $cards = [
                [
                    'label' => esc_html__( '商品总数', 'sunlyvo-nexus' ),
                    'value' => number_format_i18n( (int) $stats['products'] ),
                    'sub'   => sprintf( esc_html__( '在售 %d', 'sunlyvo-nexus' ), (int) $stats['products_active'] ),
                    'icon'  => 'dashicons-products',
                    'link'  => admin_url( 'admin.php?page=slv-products' ),
                ],
                [
                    'label' => esc_html__( '订单总数', 'sunlyvo-nexus' ),
                    'value' => number_format_i18n( (int) $stats['orders'] ),
                    'sub'   => sprintf( esc_html__( '待付款 %d', 'sunlyvo-nexus' ), (int) $stats['orders_pending'] ),
                    'icon'  => 'dashicons-cart',
                    'link'  => admin_url( 'admin.php?page=slv-orders' ),
                ],
                [
                    'label' => esc_html__( '总营收', 'sunlyvo-nexus' ),
                    'value' => slv_admin_format_price( (float) $stats['revenue'] ),
                    'sub'   => sprintf( esc_html__( '本月 %s', 'sunlyvo-nexus' ), slv_admin_format_price( (float) $stats['revenue_month'] ) ),
                    'icon'  => 'dashicons-chart-line',
                    'link'  => admin_url( 'admin.php?page=slv-reports' ),
                ],
                [
                    'label' => esc_html__( '用户总数', 'sunlyvo-nexus' ),
                    'value' => number_format_i18n( (int) $stats['users'] ),
                    'sub'   => sprintf( esc_html__( '本月新增 %d', 'sunlyvo-nexus' ), (int) $stats['users_month'] ),
                    'icon'  => 'dashicons-groups',
                    'link'  => admin_url( 'users.php' ),
                ],
            ];

            foreach ( $cards as $card ) :
                ?>
                <div class="slv-dash-card">
                    <div class="slv-dash-card__icon">
                        <span class="dashicons <?php echo esc_attr( $card['icon'] ); ?>"></span>
                    </div>
                    <div class="slv-dash-card__body">
                        <div class="slv-dash-card__label"><?php echo esc_html( $card['label'] ); ?></div>
                        <div class="slv-dash-card__value"><?php echo esc_html( $card['value'] ); ?></div>
                        <div class="slv-dash-card__sub"><?php echo esc_html( $card['sub'] ); ?></div>
                    </div>
                    <a href="<?php echo esc_url( $card['link'] ); ?>" class="slv-dash-card__link">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
                <?php
            endforeach;
            ?>
        </div>

        <h2 class="slv-dash-section-title">
            <span class="dashicons dashicons-admin-post"></span>
            <?php esc_html_e( '内容概览', 'sunlyvo-nexus' ); ?>
        </h2>

        <div class="slv-dash-grid slv-dash-grid--content">
            <?php
            $content_cards = [
                [ 'label' => esc_html__( '合集', 'sunlyvo-nexus' ), 'value' => number_format_i18n( (int) $stats['collections'] ), 'link' => admin_url( 'edit.php?post_type=slv_collection' ) ],
                [ 'label' => esc_html__( '章节', 'sunlyvo-nexus' ), 'value' => number_format_i18n( (int) $stats['chapters'] ), 'link' => admin_url( 'edit.php?post_type=slv_chapter' ) ],
                [ 'label' => esc_html__( '博客文章', 'sunlyvo-nexus' ), 'value' => number_format_i18n( (int) $stats['posts'] ), 'link' => admin_url( 'edit.php' ) ],
                [ 'label' => esc_html__( '页面', 'sunlyvo-nexus' ), 'value' => number_format_i18n( (int) $stats['pages'] ), 'link' => admin_url( 'edit.php?post_type=page' ) ],
                [ 'label' => esc_html__( '优惠券', 'sunlyvo-nexus' ), 'value' => number_format_i18n( (int) $stats['coupons'] ), 'link' => admin_url( 'admin.php?page=slv-coupons' ) ],
                [ 'label' => esc_html__( '待审提现', 'sunlyvo-nexus' ), 'value' => number_format_i18n( (int) $stats['withdrawals_pending'] ), 'link' => admin_url( 'admin.php?page=slv-withdrawals' ) ],
            ];

            foreach ( $content_cards as $card ) :
                ?>
                <a href="<?php echo esc_url( $card['link'] ); ?>" class="slv-dash-stat">
                    <div class="slv-dash-stat__label"><?php echo esc_html( $card['label'] ); ?></div>
                    <div class="slv-dash-stat__value"><?php echo esc_html( $card['value'] ); ?></div>
                </a>
                <?php
            endforeach;
            ?>
        </div>

        <h2 class="slv-dash-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php esc_html_e( '系统状态', 'sunlyvo-nexus' ); ?>
        </h2>

        <div class="slv-dash-status">
            <?php
            $status_items = [
                [ 'label' => esc_html__( '数据库版本', 'sunlyvo-nexus' ), 'value' => (string) get_option( 'slv_db_version', '未知' ), 'ok' => true ],
                [ 'label' => esc_html__( '主题版本', 'sunlyvo-nexus' ), 'value' => $slv_version, 'ok' => true ],
                [ 'label' => esc_html__( 'PHP 版本', 'sunlyvo-nexus' ), 'value' => PHP_VERSION, 'ok' => version_compare( PHP_VERSION, '8.2', '>=' ) ],
                [ 'label' => esc_html__( 'WordPress 版本', 'sunlyvo-nexus' ), 'value' => (string) get_bloginfo( 'version' ), 'ok' => version_compare( (string) get_bloginfo( 'version' ), '6.4', '>=' ) ],
                [ 'label' => esc_html__( '站点语言', 'sunlyvo-nexus' ), 'value' => function_exists( 'determine_locale' ) ? determine_locale() : get_locale(), 'ok' => true ],
                [ 'label' => esc_html__( '对象缓存', 'sunlyvo-nexus' ), 'value' => wp_using_ext_object_cache() ? esc_html__( '已启用', 'sunlyvo-nexus' ) : esc_html__( '未启用', 'sunlyvo-nexus' ), 'ok' => wp_using_ext_object_cache() ],
            ];

            foreach ( $status_items as $item ) :
                ?>
                <div class="slv-dash-status__item">
                    <span class="slv-dash-status__dot <?php echo $item['ok'] ? 'is-ok' : 'is-warn'; ?>"></span>
                    <span class="slv-dash-status__label"><?php echo esc_html( $item['label'] ); ?></span>
                    <span class="slv-dash-status__value"><?php echo esc_html( $item['value'] ); ?></span>
                </div>
                <?php
            endforeach;
            ?>
        </div>

        <h2 class="slv-dash-section-title">
            <span class="dashicons dashicons-admin-links"></span>
            <?php esc_html_e( '快速入口', 'sunlyvo-nexus' ); ?>
        </h2>

        <div class="slv-dash-quick">
            <?php
            $quick_links = [
                [ 'label' => esc_html__( 'SEO / GEO / AEO', 'sunlyvo-nexus' ), 'desc' => esc_html__( '搜索引擎与 AI 优化', 'sunlyvo-nexus' ), 'link' => admin_url( 'admin.php?page=slv-seo' ), 'icon' => 'dashicons-search' ],
                [ 'label' => esc_html__( '订单管理', 'sunlyvo-nexus' ), 'desc' => esc_html__( '查看和处理订单', 'sunlyvo-nexus' ), 'link' => admin_url( 'admin.php?page=slv-orders' ), 'icon' => 'dashicons-cart' ],
                [ 'label' => esc_html__( '商品管理', 'sunlyvo-nexus' ), 'desc' => esc_html__( '管理数字/物理商品', 'sunlyvo-nexus' ), 'link' => admin_url( 'admin.php?page=slv-products' ), 'icon' => 'dashicons-products' ],
                [ 'label' => esc_html__( '优惠券', 'sunlyvo-nexus' ), 'desc' => esc_html__( '创建和管理优惠券', 'sunlyvo-nexus' ), 'link' => admin_url( 'admin.php?page=slv-coupons' ), 'icon' => 'dashicons-tag' ],
                [ 'label' => esc_html__( '报表', 'sunlyvo-nexus' ), 'desc' => esc_html__( '销售与流量统计', 'sunlyvo-nexus' ), 'link' => admin_url( 'admin.php?page=slv-reports' ), 'icon' => 'dashicons-chart-bar' ],
                [ 'label' => esc_html__( '会员等级', 'sunlyvo-nexus' ), 'desc' => esc_html__( '配置会员权益', 'sunlyvo-nexus' ), 'link' => admin_url( 'options-general.php?page=slv-membership-levels' ), 'icon' => 'dashicons-awards' ],
                [ 'label' => esc_html__( '外观设置', 'sunlyvo-nexus' ), 'desc' => esc_html__( '菜单、小工具、主题设置', 'sunlyvo-nexus' ), 'link' => admin_url( 'themes.php' ), 'icon' => 'dashicons-admin-appearance' ],
                [ 'label' => esc_html__( '菜单管理', 'sunlyvo-nexus' ), 'desc' => esc_html__( '配置站点导航', 'sunlyvo-nexus' ), 'link' => admin_url( 'nav-menus.php' ), 'icon' => 'dashicons-menu-alt' ],
            ];

            foreach ( $quick_links as $link ) :
                ?>
                <a href="<?php echo esc_url( $link['link'] ); ?>" class="slv-dash-quick__item">
                    <span class="dashicons <?php echo esc_attr( $link['icon'] ); ?>"></span>
                    <span class="slv-dash-quick__body">
                        <span class="slv-dash-quick__label"><?php echo esc_html( $link['label'] ); ?></span>
                        <span class="slv-dash-quick__desc"><?php echo esc_html( $link['desc'] ); ?></span>
                    </span>
                    <span class="dashicons dashicons-arrow-right-alt2 slv-dash-quick__arrow"></span>
                </a>
                <?php
            endforeach;
            ?>
        </div>

        <div class="slv-dash-footer">
            <p>
                <?php
                printf(
                    esc_html__( 'SunLyvo Nexus · 版本 %s', 'sunlyvo-nexus' ),
                    esc_html( $slv_version )
                );
                ?>
                ·
                <a href="<?php echo esc_url( $project_uri ); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e( '项目主页', 'sunlyvo-nexus' ); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
}

/**
 * 价格格式化（本地安全版）
 */
function slv_admin_format_price( float $amount, string $currency = '' ): string {
    if ( function_exists( 'slv_format_price' ) ) {
        return slv_format_price( $amount, $currency );
    }
    if ( '' === $currency ) {
        $currency = 'USD';
    }
    return number_format( $amount, 2 ) . ' ' . $currency;
}

/**
 * 收集统计数据（所有表操作前先检查表是否存在）
 */
function slv_admin_dashboard_get_stats(): array {
    global $wpdb;

    $stats = [
        'products' => 0, 'products_active' => 0,
        'orders' => 0, 'orders_pending' => 0,
        'revenue' => 0.0, 'revenue_month' => 0.0,
        'users' => 0, 'users_month' => 0,
        'collections' => 0, 'chapters' => 0, 'posts' => 0, 'pages' => 0,
        'coupons' => 0, 'withdrawals_pending' => 0,
    ];

    // 商品
    $products_table = $wpdb->prefix . 'slv_products';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $products_table ) ) === $products_table ) {
        $stats['products'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$products_table}" );
        $stats['products_active'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$products_table} WHERE product_status = %s", 'active' ) );
    }

    // 订单
    $orders_table = $wpdb->prefix . 'slv_orders';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $orders_table ) ) === $orders_table ) {
        $stats['orders'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$orders_table}" );
        $stats['orders_pending'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$orders_table} WHERE status = %s", 'pending_payment' ) );
        $stats['revenue'] = (float) $wpdb->get_var( "SELECT COALESCE(SUM(total), 0) FROM {$orders_table} WHERE status IN ('paid', 'processing', 'completed')" );
        $month_start = gmdate( 'Y-m-01 00:00:00' );
        $stats['revenue_month'] = (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(total), 0) FROM {$orders_table} WHERE created_at >= %s AND status IN ('paid', 'processing', 'completed')", $month_start ) );
    }

    // 用户
    $stats['users'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
    $stats['users_month'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered >= %s", gmdate( 'Y-m-01 00:00:00' ) ) );

    // 内容
    $counts = wp_count_posts( 'slv_collection' ); $stats['collections'] = isset( $counts->publish ) ? (int) $counts->publish : 0;
    $counts = wp_count_posts( 'slv_chapter' ); $stats['chapters'] = isset( $counts->publish ) ? (int) $counts->publish : 0;
    $counts = wp_count_posts( 'post' ); $stats['posts'] = isset( $counts->publish ) ? (int) $counts->publish : 0;
    $counts = wp_count_posts( 'page' ); $stats['pages'] = isset( $counts->publish ) ? (int) $counts->publish : 0;

    // 优惠券
    $coupons_table = $wpdb->prefix . 'slv_coupons';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $coupons_table ) ) === $coupons_table ) {
        $stats['coupons'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$coupons_table}" );
    }

    // 待审提现
    $withdrawals_table = $wpdb->prefix . 'slv_withdrawals';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $withdrawals_table ) ) === $withdrawals_table ) {
        $stats['withdrawals_pending'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$withdrawals_table} WHERE status = %s", 'pending' ) );
    }

    return $stats;
}