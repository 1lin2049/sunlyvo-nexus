<?php
/**
 * Template Name: 购物车
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$slv_items = slv_cart_get_items();
$slv_totals = slv_cart_get_totals();
?>

<main id="slv-main" class="slv-main slv-cart-page" role="main">
    <div class="slv-container slv-container--narrow">

        <header class="slv-cart-header">
            <h1 class="slv-cart-title"><?php the_title(); ?></h1>
        </header>

        <?php if ( isset( $_GET['slv_cart_added'] ) ) : ?>
            <div class="slv-notice slv-notice--info">
                <?php esc_html_e( '已加入购物车。', 'sunlyvo-nexus' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['slv_cart_error'] ) ) : ?>
            <div class="slv-notice slv-notice--warning">
                <?php esc_html_e( '加入购物车失败，请重试。', 'sunlyvo-nexus' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( empty( $slv_items ) ) : ?>

            <section class="slv-cart-empty">
                <p><?php esc_html_e( '购物车是空的。', 'sunlyvo-nexus' ); ?></p>
                <a class="slv-button slv-button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( '继续浏览', 'sunlyvo-nexus' ); ?>
                </a>
            </section>

        <?php else : ?>

            <table class="slv-cart-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '商品', 'sunlyvo-nexus' ); ?></th>
                        <th><?php esc_html_e( '单价', 'sunlyvo-nexus' ); ?></th>
                        <th><?php esc_html_e( '数量', 'sunlyvo-nexus' ); ?></th>
                        <th><?php esc_html_e( '小计', 'sunlyvo-nexus' ); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $slv_items as $slv_item ) : ?>
                        <?php
                        $slv_product = $slv_item['product'];
                        $slv_qty     = (int) $slv_item['quantity'];
                        $slv_price   = (float) $slv_product['price'];
                        $slv_line    = $slv_price * $slv_qty;
                        ?>
                        <tr class="slv-cart-item" data-item-id="<?php echo esc_attr( (string) $slv_item['cart_item_id'] ); ?>">
                            <td class="slv-cart-item__product">
                                <div class="slv-cart-item__title">
                                    <?php echo esc_html( (string) $slv_product['title'] ); ?>
                                </div>
                                <?php if ( ! empty( $slv_product['linked_object_id'] ) ) : ?>
                                    <a class="slv-cart-item__link"
                                       href="<?php echo esc_url( (string) get_permalink( (int) $slv_product['linked_object_id'] ) ); ?>">
                                        <?php esc_html_e( '查看内容', 'sunlyvo-nexus' ); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="slv-cart-item__price">
                                <?php echo esc_html( slv_format_price( $slv_price, (string) $slv_product['currency'] ) ); ?>
                            </td>
                            <td class="slv-cart-item__qty">
                                <input type="number"
                                       class="slv-cart-qty"
                                       value="<?php echo esc_attr( (string) $slv_qty ); ?>"
                                       min="1"
                                       data-item-id="<?php echo esc_attr( (string) $slv_item['cart_item_id'] ); ?>">
                            </td>
                            <td class="slv-cart-item__subtotal">
                                <?php echo esc_html( slv_format_price( $slv_line, (string) $slv_product['currency'] ) ); ?>
                            </td>
                            <td class="slv-cart-item__remove">
                                <button type="button"
                                        class="slv-cart-remove"
                                        data-item-id="<?php echo esc_attr( (string) $slv_item['cart_item_id'] ); ?>">
                                    <?php esc_html_e( '移除', 'sunlyvo-nexus' ); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="slv-cart-total-label">
                            <?php esc_html_e( '合计', 'sunlyvo-nexus' ); ?>
                        </td>
                        <td class="slv-cart-total-value">
                            <?php echo esc_html( slv_format_price( (float) $slv_totals['subtotal'], (string) $slv_totals['currency'] ) ); ?>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="slv-cart-actions">
                <a class="slv-button"
                   href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( '继续浏览', 'sunlyvo-nexus' ); ?>
                </a>

                <a class="slv-button slv-button--primary"
                   href="<?php echo esc_url( slv_commerce_get_checkout_url() ); ?>">
                    <?php esc_html_e( '去结算', 'sunlyvo-nexus' ); ?>
                </a>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();