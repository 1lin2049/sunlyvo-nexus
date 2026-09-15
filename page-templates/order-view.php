<?php
/**
 * Template Name: 订单详情
 *
 * 通过 URL 参数 ?order=N 显示订单详情。
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

$slv_order_id = isset( $_GET['order'] ) ? (int) $_GET['order'] : 0;
$slv_order    = $slv_order_id > 0 ? slv_order_get( $slv_order_id ) : null;

get_header();
?>

<main id="slv-main" class="slv-main slv-order-view" role="main">
    <div class="slv-container slv-container--narrow">

        <?php if ( null === $slv_order || (int) $slv_order['user_id'] !== get_current_user_id() ) : ?>

            <section class="slv-no-results">
                <h1><?php esc_html_e( '订单不存在', 'sunlyvo-nexus' ); ?></h1>
                <p><?php esc_html_e( '可能链接已失效，或者你无权访问。', 'sunlyvo-nexus' ); ?></p>
                <a class="slv-button" href="<?php echo esc_url( get_permalink() ); ?>">
                    <?php esc_html_e( '返回订单列表', 'sunlyvo-nexus' ); ?>
                </a>
            </section>

        <?php else : ?>

            <?php
            $slv_items = slv_order_get_items( (int) $slv_order['id'] );
            $slv_log   = slv_order_get_status_log( (int) $slv_order['id'] );
            ?>

            <header class="slv-page-header">
                <h1 class="slv-page-title">
                    <?php
                    printf(
                        /* translators: %s: order number */
                        esc_html__( '订单 %s', 'sunlyvo-nexus' ),
                        esc_html( (string) $slv_order['order_number'] )
                    );
                    ?>
                </h1>
                <p class="slv-order-meta">
                    <?php echo esc_html( (string) $slv_order['created_at'] ); ?>
                    ·
                    <span class="slv-order-status slv-order-status--<?php echo esc_attr( (string) $slv_order['status'] ); ?>">
                        <?php echo esc_html( slv_order_get_status_label( (string) $slv_order['status'] ) ); ?>
                    </span>
                </p>
            </header>

            <section class="slv-order-section">
                <h2><?php esc_html_e( '已购内容', 'sunlyvo-nexus' ); ?></h2>

                <ul class="slv-order-items">
                    <?php foreach ( $slv_items as $slv_item ) : ?>
                        <li class="slv-order-item">
                            <div class="slv-order-item__main">
                                <?php if ( (int) $slv_item['linked_object_id'] > 0 ) : ?>
                                    <a href="<?php echo esc_url( (string) get_permalink( (int) $slv_item['linked_object_id'] ) ); ?>">
                                        <?php echo esc_html( (string) $slv_item['title'] ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo esc_html( (string) $slv_item['title'] ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="slv-order-item__price">
                                <?php echo esc_html( slv_format_price( (float) $slv_item['total'], (string) $slv_order['currency'] ) ); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="slv-order-total">
                    <span><?php esc_html_e( '合计', 'sunlyvo-nexus' ); ?></span>
                    <span><?php echo esc_html( slv_format_price( (float) $slv_order['total'], (string) $slv_order['currency'] ) ); ?></span>
                </div>
            </section>

            <section class="slv-order-section">
                <h2><?php esc_html_e( '订单状态', 'sunlyvo-nexus' ); ?></h2>

                <ul class="slv-order-timeline">
                    <?php foreach ( $slv_log as $slv_entry ) : ?>
                        <li class="slv-order-timeline__item">
                            <div class="slv-order-timeline__date"><?php echo esc_html( (string) $slv_entry['created_at'] ); ?></div>
                            <div class="slv-order-timeline__label">
                                <?php echo esc_html( slv_order_get_status_label( (string) $slv_entry['to_status'] ) ); ?>
                            </div>
                            <?php if ( ! empty( $slv_entry['note'] ) ) : ?>
                                <div class="slv-order-timeline__note"><?php echo esc_html( (string) $slv_entry['note'] ); ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();