<?php
/**
 * 后台订单操作
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_post_slv_order_action', 'slv_commerce_admin_handle_order_action' );

/**
 * 处理后台订单操作
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_handle_order_action(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    $order_id = isset( $_GET['order_id'] ) ? (int) $_GET['order_id'] : 0;
    $action   = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( (string) $_GET['do'] ) ) : '';

    if ( $order_id <= 0 || '' === $action ) {
        wp_safe_redirect( admin_url( 'admin.php?page=slv-orders&slv_error=invalid_params' ) );
        exit;
    }

    check_admin_referer( 'slv_order_action_' . $order_id . '_' . $action );

    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        wp_safe_redirect( admin_url( 'admin.php?page=slv-orders&slv_error=order_not_found' ) );
        exit;
    }

    $note = sprintf(
        /* translators: %s: admin user login */
        __( '由管理员 %s 操作', 'sunlyvo-nexus' ),
        wp_get_current_user()->user_login
    );

    $target_status = '';
    $success       = false;

    switch ( $action ) {
        case 'mark_paid':
            $target_status = SLV_ORDER_STATUS_PAID;
            break;

        case 'mark_processing':
            $target_status = SLV_ORDER_STATUS_PROCESSING;
            break;

        case 'mark_completed':
            $target_status = SLV_ORDER_STATUS_COMPLETED;
            break;

        case 'mark_cancelled':
            $target_status = SLV_ORDER_STATUS_CANCELLED;
            break;

        case 'mark_refunded':
            $target_status = SLV_ORDER_STATUS_REFUNDED;
            break;

        case 'mark_failed':
            $target_status = SLV_ORDER_STATUS_FAILED;
            break;

        default:
            wp_safe_redirect( admin_url( 'admin.php?page=slv-orders&order=' . $order_id . '&slv_error=unknown_action' ) );
            exit;
    }

    if ( '' !== $target_status ) {
        $success = slv_order_update_status( $order_id, $target_status, $note );
    }

    if ( $success ) {
        wp_safe_redirect( admin_url( 'admin.php?page=slv-orders&order=' . $order_id . '&slv_message=updated' ) );
    } else {
        $current = (string) $order['status'];

        wp_safe_redirect(
            admin_url(
                'admin.php?page=slv-orders&order=' . $order_id
                . '&slv_error=invalid_transition'
                . '&from=' . urlencode( $current )
                . '&to=' . urlencode( $target_status )
            )
        );
    }
    exit;
}

/**
 * 输出订单操作按钮
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $order 订单
 *
 * @return void
 */
function slv_commerce_admin_render_order_actions( array $order ): void {
    $order_id = (int) $order['id'];
    $status   = (string) $order['status'];

    $actions = [];

    if ( SLV_ORDER_STATUS_PENDING_PAYMENT === $status ) {
        $actions[] = [ 'mark_paid', __( '标记已付款', 'sunlyvo-nexus' ), 'primary', '' ];
        $actions[] = [ 'mark_cancelled', __( '取消订单', 'sunlyvo-nexus' ), 'danger', __( '确定取消？', 'sunlyvo-nexus' ) ];
    } elseif ( SLV_ORDER_STATUS_PAID === $status ) {
        $actions[] = [ 'mark_processing', __( '标记处理中', 'sunlyvo-nexus' ), '', '' ];
        $actions[] = [ 'mark_completed', __( '标记已完成', 'sunlyvo-nexus' ), 'primary', '' ];
        $actions[] = [ 'mark_refunded', __( '退款', 'sunlyvo-nexus' ), 'danger', __( '确定退款？此操作将撤销访问权限。', 'sunlyvo-nexus' ) ];
    } elseif ( SLV_ORDER_STATUS_PROCESSING === $status ) {
        $actions[] = [ 'mark_completed', __( '标记已完成', 'sunlyvo-nexus' ), 'primary', '' ];
        $actions[] = [ 'mark_refunded', __( '退款', 'sunlyvo-nexus' ), 'danger', __( '确定退款？', 'sunlyvo-nexus' ) ];
    } elseif ( SLV_ORDER_STATUS_COMPLETED === $status ) {
        $actions[] = [ 'mark_refunded', __( '退款', 'sunlyvo-nexus' ), 'danger', __( '确定退款？此操作将撤销访问权限。', 'sunlyvo-nexus' ) ];
    } elseif ( SLV_ORDER_STATUS_FAILED === $status ) {
        $actions[] = [ 'mark_paid', __( '标记已付款', 'sunlyvo-nexus' ), 'primary', '' ];
    }

    if ( empty( $actions ) ) {
        return;
    }

    echo '<div class="slv-admin-order-actions">';

    foreach ( $actions as $action_data ) {
        [ $do, $label, $variant, $confirm ] = $action_data;

        $url = wp_nonce_url(
            admin_url( 'admin-post.php?action=slv_order_action&order_id=' . $order_id . '&do=' . $do ),
            'slv_order_action_' . $order_id . '_' . $do
        );

        $class = 'slv-admin-order-action';

        if ( 'danger' === $variant ) {
            $class .= ' slv-admin-order-action--danger';
        } elseif ( 'primary' === $variant ) {
            $class .= ' slv-admin-order-action--primary';
        }

        printf(
            '<a href="%s" class="%s" data-confirm="%s">%s</a>',
            esc_url( $url ),
            esc_attr( $class ),
            esc_attr( $confirm ),
            esc_html( $label )
        );
    }

    echo '</div>';
}