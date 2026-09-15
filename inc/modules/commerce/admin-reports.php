<?php
/**
 * 后台简单报表
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'slv_commerce_admin_reports_menu', 30 );

/**
 * 注册报表菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_reports_menu(): void {
    add_submenu_page(
        'slv-orders',
        esc_html__( '报表', 'sunlyvo-nexus' ),
        esc_html__( '报表', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-reports',
        'slv_commerce_admin_reports_page'
    );
}

/**
 * 报表页
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_reports_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    $stats = slv_commerce_get_stats();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '电商报表', 'sunlyvo-nexus' ); ?></h1>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:20px;">

            <div style="background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:4px;">
                <div style="font-size:12px;color:#666;"><?php esc_html_e( '总订单数', 'sunlyvo-nexus' ); ?></div>
                <div style="font-size:28px;font-weight:600;"><?php echo esc_html( (string) $stats['total_orders'] ); ?></div>
            </div>

            <div style="background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:4px;">
                <div style="font-size:12px;color:#666;"><?php esc_html_e( '总营收', 'sunlyvo-nexus' ); ?></div>
                <div style="font-size:28px;font-weight:600;">
                    <?php echo esc_html( slv_format_price( (float) $stats['total_revenue'] ) ); ?>
                </div>
            </div>

            <div style="background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:4px;">
                <div style="font-size:12px;color:#666;"><?php esc_html_e( '本月订单', 'sunlyvo-nexus' ); ?></div>
                <div style="font-size:28px;font-weight:600;"><?php echo esc_html( (string) $stats['month_orders'] ); ?></div>
            </div>

            <div style="background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:4px;">
                <div style="font-size:12px;color:#666;"><?php esc_html_e( '本月营收', 'sunlyvo-nexus' ); ?></div>
                <div style="font-size:28px;font-weight:600;">
                    <?php echo esc_html( slv_format_price( (float) $stats['month_revenue'] ) ); ?>
                </div>
            </div>

        </div>

        <h2 style="margin-top:32px;"><?php esc_html_e( '各状态订单', 'sunlyvo-nexus' ); ?></h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '数量', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '金额', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $stats['by_status'] as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( slv_order_get_status_label( (string) $row['status'] ) ); ?></td>
                        <td><?php echo esc_html( (string) $row['count'] ); ?></td>
                        <td><?php echo esc_html( slv_format_price( (float) $row['total'] ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 style="margin-top:32px;"><?php esc_html_e( '最近 30 天', 'sunlyvo-nexus' ); ?></h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( '日期', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '订单数', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '营收', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $stats['daily'] ) ) : ?>
                    <tr><td colspan="3"><?php esc_html_e( '无数据。', 'sunlyvo-nexus' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $stats['daily'] as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( (string) $row['day'] ); ?></td>
                            <td><?php echo esc_html( (string) $row['count'] ); ?></td>
                            <td><?php echo esc_html( slv_format_price( (float) $row['total'] ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * 获取统计数据
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_commerce_get_stats(): array {
    global $wpdb;

    $table = $wpdb->prefix . 'slv_orders';

    $total_orders = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

    $total_revenue = (float) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COALESCE(SUM(total), 0) FROM {$table} WHERE status IN (%s, %s, %s)",
            SLV_ORDER_STATUS_PAID,
            SLV_ORDER_STATUS_PROCESSING,
            SLV_ORDER_STATUS_COMPLETED
        )
    );

    $month_start = gmdate( 'Y-m-01 00:00:00' );

    $month_orders = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s",
            $month_start
        )
    );

    $month_revenue = (float) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COALESCE(SUM(total), 0) FROM {$table}
             WHERE created_at >= %s
             AND status IN (%s, %s, %s)",
            $month_start,
            SLV_ORDER_STATUS_PAID,
            SLV_ORDER_STATUS_PROCESSING,
            SLV_ORDER_STATUS_COMPLETED
        )
    );

    $by_status = $wpdb->get_results(
        "SELECT status, COUNT(*) as count, COALESCE(SUM(total), 0) as total
         FROM {$table}
         GROUP BY status
         ORDER BY count DESC",
        ARRAY_A
    );

    $thirty_days_ago = gmdate( 'Y-m-d H:i:s', time() - ( 30 * DAY_IN_SECONDS ) );

    $daily = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DATE(created_at) as day, COUNT(*) as count, COALESCE(SUM(total), 0) as total
             FROM {$table}
             WHERE created_at >= %s
             AND status IN (%s, %s, %s)
             GROUP BY DATE(created_at)
             ORDER BY day DESC",
            $thirty_days_ago,
            SLV_ORDER_STATUS_PAID,
            SLV_ORDER_STATUS_PROCESSING,
            SLV_ORDER_STATUS_COMPLETED
        ),
        ARRAY_A
    );

    return [
        'total_orders'  => $total_orders,
        'total_revenue' => $total_revenue,
        'month_orders'  => $month_orders,
        'month_revenue' => $month_revenue,
        'by_status'     => is_array( $by_status ) ? $by_status : [],
        'daily'         => is_array( $daily ) ? $daily : [],
    ];
}