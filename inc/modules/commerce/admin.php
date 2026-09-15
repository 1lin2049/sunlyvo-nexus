<?php
/**
 * 电商后台管理
 *
 * 订单列表 + 订单详情 + 状态变更
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/admin-order-actions.php';

add_action( 'admin_menu', 'slv_commerce_admin_menu', 25 );

/**
 * 注册后台菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_menu(): void {
    add_menu_page(
        esc_html__( 'SunLyvo 订单', 'sunlyvo-nexus' ),
        esc_html__( 'SunLyvo 订单', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-orders',
        'slv_commerce_admin_orders_page',
        'dashicons-cart',
        26
    );
}

/**
 * 订单列表页
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_orders_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    $order_id = isset( $_GET['order'] ) ? (int) $_GET['order'] : 0;

    if ( $order_id > 0 ) {
        slv_commerce_admin_order_detail( $order_id );
        return;
    }

    $status_filter = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( (string) $_GET['status'] ) ) : '';
    $paged         = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
    $per_page      = 20;

    $orders = slv_order_list(
        [
            'status' => $status_filter,
            'limit'  => $per_page,
            'offset' => ( $paged - 1 ) * $per_page,
        ]
    );

    $statuses = slv_order_get_statuses();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e( 'SunLyvo 订单', 'sunlyvo-nexus' ); ?></h1>
        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=slv-orders' ) ); ?>"
                   class="<?php echo '' === $status_filter ? 'current' : ''; ?>">
                    <?php esc_html_e( '全部', 'sunlyvo-nexus' ); ?>
                </a> |
            </li>
            <?php
            $i = 0;
            $total = count( $statuses );
            foreach ( $statuses as $key => $label ) :
                $i++;
                ?>
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=slv-orders&status=' . $key ) ); ?>"
                       class="<?php echo $status_filter === $key ? 'current' : ''; ?>">
                        <?php echo esc_html( $label ); ?>
                    </a>
                    <?php echo $i < $total ? ' |' : ''; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:60px;"><?php esc_html_e( 'ID', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '订单号', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '用户', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                    <th style="text-align:right;"><?php esc_html_e( '金额', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '支付方式', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '时间', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $orders ) ) : ?>
                    <tr><td colspan="7"><?php esc_html_e( '无订单。', 'sunlyvo-nexus' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $orders as $order ) : ?>
                        <?php
                        $user  = get_user_by( 'ID', (int) $order['user_id'] );
                        $uname = $user ? $user->user_login : '—';
                        $url   = admin_url( 'admin.php?page=slv-orders&order=' . (int) $order['id'] );
                        ?>
                        <tr>
                            <td><?php echo esc_html( (string) $order['id'] ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( $url ); ?>">
                                    <strong><?php echo esc_html( (string) $order['order_number'] ); ?></strong>
                                </a>
                            </td>
                            <td><?php echo esc_html( $uname ); ?></td>
                            <td>
                                <span class="slv-order-status slv-order-status--<?php echo esc_attr( (string) $order['status'] ); ?>">
                                    <?php echo esc_html( slv_order_get_status_label( (string) $order['status'] ) ); ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <?php echo esc_html( slv_format_price( (float) $order['total'], (string) $order['currency'] ) ); ?>
                            </td>
                            <td><?php echo esc_html( (string) $order['payment_gateway'] ); ?></td>
                            <td><?php echo esc_html( (string) $order['created_at'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * 订单详情页
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return void
 */
function slv_commerce_admin_order_detail( int $order_id ): void {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        echo '<div class="wrap"><h1>' . esc_html__( '订单不存在', 'sunlyvo-nexus' ) . '</h1></div>';
        return;
    }

    $items    = slv_order_get_items( $order_id );
    $log      = slv_order_get_status_log( $order_id );
    $user     = get_user_by( 'ID', (int) $order['user_id'] );
    $uname    = $user ? $user->user_login : '—';
    $back_url = admin_url( 'admin.php?page=slv-orders' );

    $error   = isset( $_GET['slv_error'] ) ? sanitize_key( wp_unslash( (string) $_GET['slv_error'] ) ) : '';
    $message = isset( $_GET['slv_message'] ) ? sanitize_key( wp_unslash( (string) $_GET['slv_message'] ) ) : '';
    ?>
    <div class="wrap">
        <h1>
            <?php
            printf(
                /* translators: %s: order number */
                esc_html__( '订单 %s', 'sunlyvo-nexus' ),
                esc_html( (string) $order['order_number'] )
            );
            ?>
            <a href="<?php echo esc_url( $back_url ); ?>" class="page-title-action">
                <?php esc_html_e( '返回列表', 'sunlyvo-nexus' ); ?>
            </a>
        </h1>

        <?php if ( 'updated' === $message ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( '订单状态已更新。', 'sunlyvo-nexus' ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( 'invalid_transition' === $error ) : ?>
            <?php
            $from = isset( $_GET['from'] ) ? sanitize_key( wp_unslash( (string) $_GET['from'] ) ) : '';
            $to   = isset( $_GET['to'] ) ? sanitize_key( wp_unslash( (string) $_GET['to'] ) ) : '';
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: 1: from status 2: to status */
                        esc_html__( '状态转移不合法：%1$s → %2$s。', 'sunlyvo-nexus' ),
                        esc_html( slv_order_get_status_label( $from ) ),
                        esc_html( slv_order_get_status_label( $to ) )
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ( 'order_not_found' === $error ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><?php esc_html_e( '订单不存在。', 'sunlyvo-nexus' ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( 'invalid_params' === $error ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><?php esc_html_e( '参数无效。', 'sunlyvo-nexus' ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( 'unknown_action' === $error ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><?php esc_html_e( '未知操作。', 'sunlyvo-nexus' ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( function_exists( 'slv_commerce_admin_render_order_actions' ) ) : ?>
            <?php slv_commerce_admin_render_order_actions( $order ); ?>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-top:20px;">

            <div>
                <h2><?php esc_html_e( '订单项', 'sunlyvo-nexus' ); ?></h2>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '商品', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '单价', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '数量', 'sunlyvo-nexus' ); ?></th>
                            <th style="text-align:right;"><?php esc_html_e( '小计', 'sunlyvo-nexus' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty( $items ) ) : ?>
                            <tr><td colspan="4"><?php esc_html_e( '无订单项。', 'sunlyvo-nexus' ); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ( $items as $item ) : ?>
                                <tr>
                                    <td>
                                        <?php echo esc_html( (string) $item['title'] ); ?>
                                        <?php if ( $item['linked_object_id'] > 0 ) : ?>
                                            <br>
                                            <a href="<?php echo esc_url( (string) get_edit_post_link( (int) $item['linked_object_id'] ) ); ?>">
                                                <?php echo esc_html( '#' . (string) $item['linked_object_id'] . ' ' . (string) $item['linked_object_type'] ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html( slv_format_price( (float) $item['unit_price'], (string) $order['currency'] ) ); ?></td>
                                    <td><?php echo esc_html( (string) $item['quantity'] ); ?></td>
                                    <td style="text-align:right;"><?php echo esc_html( slv_format_price( (float) $item['total'], (string) $order['currency'] ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align:right;"><?php esc_html_e( '小计', 'sunlyvo-nexus' ); ?></th>
                            <th style="text-align:right;"><?php echo esc_html( slv_format_price( (float) $order['subtotal'], (string) $order['currency'] ) ); ?></th>
                        </tr>
                        <?php if ( (float) $order['discount_total'] > 0 ) : ?>
                            <tr>
                                <th colspan="3" style="text-align:right;"><?php esc_html_e( '折扣', 'sunlyvo-nexus' ); ?></th>
                                <th style="text-align:right;">-<?php echo esc_html( slv_format_price( (float) $order['discount_total'], (string) $order['currency'] ) ); ?></th>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th colspan="3" style="text-align:right;"><?php esc_html_e( '合计', 'sunlyvo-nexus' ); ?></th>
                            <th style="text-align:right;font-weight:bold;"><?php echo esc_html( slv_format_price( (float) $order['total'], (string) $order['currency'] ) ); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <h2 style="margin-top:20px;"><?php esc_html_e( '状态变更日志', 'sunlyvo-nexus' ); ?></h2>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:180px;"><?php esc_html_e( '时间', 'sunlyvo-nexus' ); ?></th>
                            <th style="width:120px;"><?php esc_html_e( '从', 'sunlyvo-nexus' ); ?></th>
                            <th style="width:120px;"><?php esc_html_e( '到', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '备注', 'sunlyvo-nexus' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $log as $entry ) : ?>
                            <tr>
                                <td><?php echo esc_html( (string) $entry['created_at'] ); ?></td>
                                <td><?php echo esc_html( '' !== (string) $entry['from_status'] ? slv_order_get_status_label( (string) $entry['from_status'] ) : '—' ); ?></td>
                                <td><?php echo esc_html( slv_order_get_status_label( (string) $entry['to_status'] ) ); ?></td>
                                <td><?php echo esc_html( (string) $entry['note'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div>
                <h2><?php esc_html_e( '订单信息', 'sunlyvo-nexus' ); ?></h2>

                <table class="widefat fixed">
                    <tbody>
                        <tr><th><?php esc_html_e( '订单号', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['order_number'] ); ?></td></tr>
                        <tr><th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( slv_order_get_status_label( (string) $order['status'] ) ); ?></td></tr>
                        <tr><th><?php esc_html_e( '用户', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( $uname ); ?></td></tr>
                        <tr><th><?php esc_html_e( '用户ID', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['user_id'] ); ?></td></tr>
                        <tr><th><?php esc_html_e( '货币', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['currency'] ); ?></td></tr>
                        <tr><th><?php esc_html_e( '支付方式', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['payment_gateway'] ); ?></td></tr>
                        <tr><th><?php esc_html_e( '支付状态', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['payment_status'] ); ?></td></tr>
                        <tr><th><?php esc_html_e( '交易ID', 'sunlyvo-nexus' ); ?></th><td><code><?php echo esc_html( (string) $order['transaction_id'] ); ?></code></td></tr>
                        <tr><th><?php esc_html_e( '创建时间', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['created_at'] ); ?></td></tr>
                        <?php if ( ! empty( $order['paid_at'] ) ) : ?>
                            <tr><th><?php esc_html_e( '支付时间', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['paid_at'] ); ?></td></tr>
                        <?php endif; ?>
                        <?php if ( ! empty( $order['completed_at'] ) ) : ?>
                            <tr><th><?php esc_html_e( '完成时间', 'sunlyvo-nexus' ); ?></th><td><?php echo esc_html( (string) $order['completed_at'] ); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <?php
}