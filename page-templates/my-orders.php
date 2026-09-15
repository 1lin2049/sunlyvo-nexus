<?php
/**
 * Template Name: 我的订单
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

get_header();

$slv_user_id = get_current_user_id();
$slv_orders  = slv_order_list(
    [
        'user_id' => $slv_user_id,
        'limit'   => 50,
    ]
);
?>

<main id="slv-main" class="slv-main slv-my-orders" role="main">
    <div class="slv-container">

        <header class="slv-page-header">
            <h1 class="slv-page-title"><?php the_title(); ?></h1>
        </header>

        <?php if ( empty( $slv_orders ) ) : ?>

            <section class="slv-no-results">
                <p><?php esc_html_e( '还没有订单。', 'sunlyvo-nexus' ); ?></p>
            </section>

        <?php else : ?>

            <table class="slv-orders-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '订单号', 'sunlyvo-nexus' ); ?></th>
                        <th><?php esc_html_e( '日期', 'sunlyvo-nexus' ); ?></th>
                        <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                        <th style="text-align:right;"><?php esc_html_e( '总额', 'sunlyvo-nexus' ); ?></th>
                        <th><?php esc_html_e( '操作', 'sunlyvo-nexus' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $slv_orders as $slv_order ) : ?>
                        <tr>
                            <td>
                                <code><?php echo esc_html( (string) $slv_order['order_number'] ); ?></code>
                            </td>
                            <td><?php echo esc_html( (string) $slv_order['created_at'] ); ?></td>
                            <td>
                                <span class="slv-order-status slv-order-status--<?php echo esc_attr( (string) $slv_order['status'] ); ?>">
                                    <?php echo esc_html( slv_order_get_status_label( (string) $slv_order['status'] ) ); ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <?php echo esc_html( slv_format_price( (float) $slv_order['total'], (string) $slv_order['currency'] ) ); ?>
                            </td>
                            <td>
                                <?php
                                $slv_view_url = add_query_arg(
                                    'order',
                                    (int) $slv_order['id'],
                                    get_permalink()
                                );
                                ?>
                                <a href="<?php echo esc_url( $slv_view_url ); ?>">
                                    <?php esc_html_e( '查看', 'sunlyvo-nexus' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();