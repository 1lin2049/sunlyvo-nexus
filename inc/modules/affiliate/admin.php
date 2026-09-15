<?php
/**
 * 分销后台管理
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'slv_affiliate_admin_menu', 40 );

/**
 * 注册分销菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_affiliate_admin_menu(): void {
    add_submenu_page(
        'slv-orders',
        esc_html__( '提现申请', 'sunlyvo-nexus' ),
        esc_html__( '提现申请', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-withdrawals',
        'slv_affiliate_admin_withdrawals_page'
    );
}

/**
 * 提现管理页
 *
 * @since 1.0.0
 * @return void
 */
function slv_affiliate_admin_withdrawals_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    // 处理操作
    if ( isset( $_GET['action'], $_GET['id'] ) ) {
        $action = sanitize_key( wp_unslash( (string) $_GET['action'] ) );
        $wid    = (int) $_GET['id'];

        check_admin_referer( 'slv_process_withdrawal_' . $wid );

        if ( in_array( $action, [ 'approve', 'reject' ], true ) ) {
            slv_affiliate_process_withdrawal( $wid, $action );

            wp_safe_redirect( admin_url( 'admin.php?page=slv-withdrawals&processed=1' ) );
            exit;
        }
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT w.*, u.user_login
         FROM {$wpdb->prefix}slv_withdrawals w
         LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID
         ORDER BY
             CASE w.status WHEN 'pending' THEN 0 ELSE 1 END,
             w.created_at DESC
         LIMIT 100",
        ARRAY_A
    );

    $rows = is_array( $rows ) ? $rows : [];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '提现申请', 'sunlyvo-nexus' ); ?></h1>

        <?php if ( isset( $_GET['processed'] ) ) : ?>
            <div class="notice notice-success"><p><?php esc_html_e( '操作完成。', 'sunlyvo-nexus' ); ?></p></div>
        <?php endif; ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th><?php esc_html_e( '用户', 'sunlyvo-nexus' ); ?></th>
                    <th style="text-align:right;"><?php esc_html_e( '金额', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '方式', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '账户', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '时间', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '操作', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $rows ) ) : ?>
                    <tr><td colspan="8"><?php esc_html_e( '暂无提现申请。', 'sunlyvo-nexus' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $rows as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( (string) $row['id'] ); ?></td>
                            <td><?php echo esc_html( (string) ( $row['user_login'] ?? '—' ) ); ?></td>
                            <td style="text-align:right;"><?php echo esc_html( slv_format_price( (float) $row['amount'] ) ); ?></td>
                            <td><?php echo esc_html( (string) $row['method'] ); ?></td>
                            <td><code><?php echo esc_html( (string) $row['account'] ); ?></code></td>
                            <td><?php echo esc_html( (string) $row['status'] ); ?></td>
                            <td><?php echo esc_html( (string) $row['created_at'] ); ?></td>
                            <td>
                                <?php if ( 'pending' === $row['status'] ) : ?>
                                    <?php
                                    $approve_url = wp_nonce_url(
                                        admin_url( 'admin.php?page=slv-withdrawals&action=approve&id=' . (int) $row['id'] ),
                                        'slv_process_withdrawal_' . (int) $row['id']
                                    );

                                    $reject_url = wp_nonce_url(
                                        admin_url( 'admin.php?page=slv-withdrawals&action=reject&id=' . (int) $row['id'] ),
                                        'slv_process_withdrawal_' . (int) $row['id']
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary button-small">
                                        <?php esc_html_e( '批准', 'sunlyvo-nexus' ); ?>
                                    </a>
                                    <a href="<?php echo esc_url( $reject_url ); ?>" class="button button-small">
                                        <?php esc_html_e( '拒绝', 'sunlyvo-nexus' ); ?>
                                    </a>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}