<?php
/**
 * Template Name: 结算页
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

$slv_items   = slv_cart_get_items();
$slv_totals  = slv_cart_get_totals();
$slv_user    = wp_get_current_user();
$slv_gateways = slv_gateway_get_available();

$slv_coupon_code = isset( $_GET['coupon'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['coupon'] ) ) : '';
$slv_discount    = 0.0;

if ( '' !== $slv_coupon_code ) {
    $slv_coupon_result = slv_coupon_calculate_discount( $slv_coupon_code, (float) $slv_totals['subtotal'] );
    if ( $slv_coupon_result['valid'] ) {
        $slv_discount = (float) $slv_coupon_result['discount'];
    }
}

$slv_member_discount = slv_checkout_calculate_member_discount( (float) $slv_totals['subtotal'] );
$slv_total_discount  = $slv_discount + $slv_member_discount;
$slv_grand_total     = max( 0, (float) $slv_totals['subtotal'] - $slv_total_discount );
?>

<main id="slv-main" class="slv-main slv-checkout-page" role="main">
    <div class="slv-container slv-container--narrow">

        <header class="slv-checkout-header">
            <h1 class="slv-checkout-title"><?php the_title(); ?></h1>
        </header>

        <?php if ( isset( $_GET['slv_status'] ) && 'success' === $_GET['slv_status'] ) : ?>
            <div class="slv-notice slv-notice--info">
                <?php esc_html_e( '支付成功，感谢你的购买。', 'sunlyvo-nexus' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( empty( $slv_items ) ) : ?>

            <section class="slv-cart-empty">
                <p><?php esc_html_e( '购物车是空的，无法结算。', 'sunlyvo-nexus' ); ?></p>
                <a class="slv-button slv-button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( '继续浏览', 'sunlyvo-nexus' ); ?>
                </a>
            </section>

        <?php else : ?>

            <div class="slv-checkout-grid">

                <div class="slv-checkout-main">

                    <form id="slv-checkout-form" class="slv-checkout-form" method="post" novalidate>

                        <?php wp_nonce_field( 'slv_checkout', 'slv_checkout_nonce' ); ?>

                        <section class="slv-checkout-section">
                            <h2 class="slv-checkout-section__title">
                                <?php esc_html_e( '账单信息', 'sunlyvo-nexus' ); ?>
                            </h2>

                            <div class="slv-form-row">
                                <label for="slv_billing_email"><?php esc_html_e( '邮箱', 'sunlyvo-nexus' ); ?> *</label>
                                <input type="email"
                                       id="slv_billing_email"
                                       name="billing[email]"
                                       value="<?php echo esc_attr( (string) $slv_user->user_email ); ?>"
                                       required>
                            </div>

                            <div class="slv-form-row slv-form-row--half">
                                <div>
                                    <label for="slv_billing_first_name"><?php esc_html_e( '名字', 'sunlyvo-nexus' ); ?></label>
                                    <input type="text"
                                           id="slv_billing_first_name"
                                           name="billing[first_name]"
                                           value="<?php echo esc_attr( (string) get_user_meta( $slv_user->ID, 'billing_first_name', true ) ); ?>">
                                </div>
                                <div>
                                    <label for="slv_billing_last_name"><?php esc_html_e( '姓氏', 'sunlyvo-nexus' ); ?></label>
                                    <input type="text"
                                           id="slv_billing_last_name"
                                           name="billing[last_name]"
                                           value="<?php echo esc_attr( (string) get_user_meta( $slv_user->ID, 'billing_last_name', true ) ); ?>">
                                </div>
                            </div>

                            <div class="slv-form-row">
                                <label for="slv_billing_country"><?php esc_html_e( '国家/地区', 'sunlyvo-nexus' ); ?></label>
                                <input type="text"
                                       id="slv_billing_country"
                                       name="billing[country]"
                                       value="<?php echo esc_attr( (string) get_user_meta( $slv_user->ID, 'billing_country', true ) ); ?>">
                            </div>
                        </section>

                        <?php if ( ! empty( $slv_gateways ) ) : ?>
                            <section class="slv-checkout-section">
                                <h2 class="slv-checkout-section__title">
                                    <?php esc_html_e( '支付方式', 'sunlyvo-nexus' ); ?>
                                </h2>

                                <div class="slv-payment-methods">
                                    <?php
                                    $slv_first = true;
                                    foreach ( $slv_gateways as $slv_gid => $slv_gateway ) :
                                        ?>
                                        <label class="slv-payment-method">
                                            <input type="radio"
                                                   name="gateway"
                                                   value="<?php echo esc_attr( $slv_gid ); ?>"
                                                <?php checked( $slv_first ); ?>>
                                            <span class="slv-payment-method__info">
                                                <strong><?php echo esc_html( $slv_gateway->get_title() ); ?></strong>
                                                <?php if ( '' !== $slv_gateway->get_description() ) : ?>
                                                    <small><?php echo esc_html( $slv_gateway->get_description() ); ?></small>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                        <?php
                                        $slv_first = false;
                                    endforeach;
                                    ?>
                                </div>
                            </section>
                        <?php endif; ?>

                        <section class="slv-checkout-section">
                            <h2 class="slv-checkout-section__title">
                                <?php esc_html_e( '订单备注', 'sunlyvo-nexus' ); ?>
                            </h2>
                            <textarea name="customer_note" rows="3" class="slv-form-textarea"
                                      placeholder="<?php esc_attr_e( '可选，如配送说明', 'sunlyvo-nexus' ); ?>"></textarea>
                        </section>

                    </form>

                </div>

                <aside class="slv-checkout-side">

                    <h2 class="slv-checkout-section__title">
                        <?php esc_html_e( '订单预览', 'sunlyvo-nexus' ); ?>
                    </h2>

                    <ul class="slv-checkout-items">
                        <?php foreach ( $slv_items as $slv_item ) : ?>
                            <?php $slv_product = $slv_item['product']; ?>
                            <li class="slv-checkout-item">
                                <div class="slv-checkout-item__title">
                                    <?php echo esc_html( (string) $slv_product['title'] ); ?>
                                    <span class="slv-checkout-item__qty">×<?php echo esc_html( (string) $slv_item['quantity'] ); ?></span>
                                </div>
                                <div class="slv-checkout-item__price">
                                    <?php
                                    echo esc_html(
                                        slv_format_price(
                                            (float) $slv_product['price'] * (int) $slv_item['quantity'],
                                            (string) $slv_product['currency']
                                        )
                                    );
                                    ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="slv-checkout-totals">
                        <div class="slv-checkout-total-row">
                            <span><?php esc_html_e( '小计', 'sunlyvo-nexus' ); ?></span>
                            <span><?php echo esc_html( slv_format_price( (float) $slv_totals['subtotal'], (string) $slv_totals['currency'] ) ); ?></span>
                        </div>

                        <?php if ( $slv_member_discount > 0 ) : ?>
                            <div class="slv-checkout-total-row slv-checkout-total-row--discount">
                                <span><?php esc_html_e( '会员折扣', 'sunlyvo-nexus' ); ?></span>
                                <span>-<?php echo esc_html( slv_format_price( $slv_member_discount, (string) $slv_totals['currency'] ) ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ( $slv_discount > 0 ) : ?>
                            <div class="slv-checkout-total-row slv-checkout-total-row--discount">
                                <span><?php esc_html_e( '优惠券', 'sunlyvo-nexus' ); ?></span>
                                <span>-<?php echo esc_html( slv_format_price( $slv_discount, (string) $slv_totals['currency'] ) ); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="slv-checkout-total-row slv-checkout-total-row--grand">
                            <span><?php esc_html_e( '合计', 'sunlyvo-nexus' ); ?></span>
                            <span><?php echo esc_html( slv_format_price( $slv_grand_total, (string) $slv_totals['currency'] ) ); ?></span>
                        </div>
                    </div>

                    <div class="slv-checkout-coupon">
                        <input type="text"
                               id="slv-coupon-input"
                               placeholder="<?php esc_attr_e( '优惠码', 'sunlyvo-nexus' ); ?>"
                               value="<?php echo esc_attr( $slv_coupon_code ); ?>">
                        <button type="button" id="slv-coupon-apply" class="slv-button slv-button--small">
                            <?php esc_html_e( '应用', 'sunlyvo-nexus' ); ?>
                        </button>
                    </div>

                    <button type="button"
                            id="slv-place-order"
                            class="slv-button slv-button--primary slv-button--block">
                        <?php
                        printf(
                            /* translators: %s: total */
                            esc_html__( '提交订单 · %s', 'sunlyvo-nexus' ),
                            esc_html( slv_format_price( $slv_grand_total, (string) $slv_totals['currency'] ) )
                        );
                        ?>
                    </button>

                    <div id="slv-checkout-message" class="slv-checkout-message" role="alert"></div>

                </aside>

            </div>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();