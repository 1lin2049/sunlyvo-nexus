<?php
/**
 * 订单邮件通知
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'slv_order_created', 'slv_commerce_email_order_pending', 10, 1 );
add_action( 'slv_order_completed', 'slv_commerce_email_order_completed', 10, 1 );
add_action( 'slv_order_refunded', 'slv_commerce_email_order_refunded', 10, 1 );

/**
 * 待付款邮件
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return void
 */
function slv_commerce_email_order_pending( int $order_id ): void {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return;
    }

    $billing = is_array( $order['billing_data'] ) ? $order['billing_data'] : [];
    $email   = isset( $billing['email'] ) ? (string) $billing['email'] : '';

    if ( '' === $email ) {
        $user  = get_user_by( 'ID', (int) $order['user_id'] );
        $email = $user ? $user->user_email : '';
    }

    if ( '' === $email ) {
        return;
    }

    $subject = sprintf(
        /* translators: %s: order number */
        __( '[%s] 订单待付款', 'sunlyvo-nexus' ),
        (string) $order['order_number']
    );

    $content = slv_commerce_email_wrap(
        sprintf(
            /* translators: %s: order number */
            __( '你的订单 %s 已创建，请尽快完成支付。', 'sunlyvo-nexus' ),
            (string) $order['order_number']
        ),
        [
            'order' => $order,
            'items' => slv_order_get_items( $order_id ),
        ]
    );

    wp_mail( $email, $subject, $content, [ 'Content-Type: text/html; charset=UTF-8' ] );
}

/**
 * 完成邮件
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return void
 */
function slv_commerce_email_order_completed( int $order_id ): void {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return;
    }

    $billing = is_array( $order['billing_data'] ) ? $order['billing_data'] : [];
    $email   = isset( $billing['email'] ) ? (string) $billing['email'] : '';

    if ( '' === $email ) {
        $user  = get_user_by( 'ID', (int) $order['user_id'] );
        $email = $user ? $user->user_email : '';
    }

    if ( '' === $email ) {
        return;
    }

    $subject = sprintf(
        /* translators: %s: order number */
        __( '[%s] 订单已完成', 'sunlyvo-nexus' ),
        (string) $order['order_number']
    );

    $content = slv_commerce_email_wrap(
        sprintf(
            /* translators: %s: order number */
            __( '感谢你的购买，订单 %s 已完成。现在可以开始阅读已购内容。', 'sunlyvo-nexus' ),
            (string) $order['order_number']
        ),
        [
            'order' => $order,
            'items' => slv_order_get_items( $order_id ),
        ]
    );

    wp_mail( $email, $subject, $content, [ 'Content-Type: text/html; charset=UTF-8' ] );
}

/**
 * 退款邮件
 *
 * @since 1.0.0
 *
 * @param int $order_id 订单ID
 *
 * @return void
 */
function slv_commerce_email_order_refunded( int $order_id ): void {
    $order = slv_order_get( $order_id );

    if ( null === $order ) {
        return;
    }

    $user  = get_user_by( 'ID', (int) $order['user_id'] );
    $email = $user ? $user->user_email : '';

    if ( '' === $email ) {
        return;
    }

    $subject = sprintf(
        /* translators: %s: order number */
        __( '[%s] 订单已退款', 'sunlyvo-nexus' ),
        (string) $order['order_number']
    );

    $content = slv_commerce_email_wrap(
        sprintf(
            /* translators: %s: order number */
            __( '订单 %s 已退款，访问权限已撤销。', 'sunlyvo-nexus' ),
            (string) $order['order_number']
        ),
        [
            'order' => $order,
        ]
    );

    wp_mail( $email, $subject, $content, [ 'Content-Type: text/html; charset=UTF-8' ] );
}

/**
 * 邮件内容包装
 *
 * @since 1.0.0
 *
 * @param string               $message 主消息
 * @param array<string, mixed> $data    附加数据
 *
 * @return string
 */
function slv_commerce_email_wrap( string $message, array $data = [] ): string {
    $order = $data['order'] ?? null;
    $items = $data['items'] ?? [];

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php bloginfo( 'name' ); ?></title>
    </head>
    <body style="font-family:sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;">
        <h1 style="font-size:20px;color:#00a658;border-bottom:2px solid #00a658;padding-bottom:10px;">
            <?php bloginfo( 'name' ); ?>
        </h1>

        <p style="font-size:15px;"><?php echo esc_html( $message ); ?></p>

        <?php if ( is_array( $order ) ) : ?>
            <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;">
                        <strong><?php esc_html_e( '订单号', 'sunlyvo-nexus' ); ?></strong>
                    </td>
                    <td style="padding:8px;border-bottom:1px solid #eee;">
                        <?php echo esc_html( (string) $order['order_number'] ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;">
                        <strong><?php esc_html_e( '金额', 'sunlyvo-nexus' ); ?></strong>
                    </td>
                    <td style="padding:8px;border-bottom:1px solid #eee;">
                        <?php echo esc_html( slv_format_price( (float) $order['total'], (string) $order['currency'] ) ); ?>
                    </td>
                </tr>
            </table>
        <?php endif; ?>

        <?php if ( ! empty( $items ) ) : ?>
            <h2 style="font-size:16px;margin-top:24px;"><?php esc_html_e( '已购内容', 'sunlyvo-nexus' ); ?></h2>
            <ul style="padding-left:20px;">
                <?php foreach ( $items as $item ) : ?>
                    <li style="margin-bottom:8px;">
                        <?php if ( (int) $item['linked_object_id'] > 0 ) : ?>
                            <a href="<?php echo esc_url( (string) get_permalink( (int) $item['linked_object_id'] ) ); ?>" style="color:#00a658;">
                                <?php echo esc_html( (string) $item['title'] ); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html( (string) $item['title'] ); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p style="margin-top:32px;font-size:12px;color:#999;border-top:1px solid #eee;padding-top:12px;">
            <?php echo esc_html( SLV_PROJECT_URI ); ?>
        </p>
    </body>
    </html>
    <?php
    return (string) ob_get_clean();
}