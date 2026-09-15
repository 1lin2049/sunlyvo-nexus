<?php
/**
 * 电商短代码
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'slv_cart_count', 'slv_commerce_shortcode_cart_count' );
add_shortcode( 'slv_cart_link', 'slv_commerce_shortcode_cart_link' );
add_shortcode( 'slv_buy_button', 'slv_commerce_shortcode_buy_button' );

/**
 * 购物车数量
 *
 * 用法：[slv_cart_count]
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $atts 属性
 *
 * @return string
 */
function slv_commerce_shortcode_cart_count( $atts ): string {
    $count = slv_cart_get_count();
    return '<span class="slv-cart-count">' . esc_html( (string) $count ) . '</span>';
}

/**
 * 购物车链接
 *
 * 用法：[slv_cart_link text="购物车"]
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $atts 属性
 *
 * @return string
 */
function slv_commerce_shortcode_cart_link( $atts ): string {
    $atts = shortcode_atts(
        [
            'text'  => __( '购物车', 'sunlyvo-nexus' ),
            'class' => 'slv-cart-link',
        ],
        $atts,
        'slv_cart_link'
    );

    $url   = slv_commerce_get_cart_url();
    $count = slv_cart_get_count();

    return sprintf(
        '<a href="%s" class="%s">%s <span class="slv-cart-count">%d</span></a>',
        esc_url( $url ),
        esc_attr( (string) $atts['class'] ),
        esc_html( (string) $atts['text'] ),
        $count
    );
}

/**
 * 购买按钮
 *
 * 用法：[slv_buy_button id="25"]
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $atts 属性
 *
 * @return string
 */
function slv_commerce_shortcode_buy_button( $atts ): string {
    $atts = shortcode_atts(
        [
            'id'   => 0,
            'text' => __( '购买', 'sunlyvo-nexus' ),
        ],
        $atts,
        'slv_buy_button'
    );

    $post_id = (int) $atts['id'];

    if ( $post_id <= 0 ) {
        return '';
    }

    $post = get_post( $post_id );

    if ( ! $post ) {
        return '';
    }

    $content_type = 'slv_chapter' === $post->post_type ? 'chapter' : ( 'slv_collection' === $post->post_type ? 'collection' : '' );

    if ( '' === $content_type ) {
        return '';
    }

    $product = slv_product_get_by_linked_object( $content_type, $post_id );

    if ( null === $product || ! slv_product_is_purchasable( $product ) ) {
        return '';
    }

    $url = add_query_arg( 'slv_add_to_cart', (int) $product['id'], slv_commerce_get_cart_url() );

    return sprintf(
        '<a href="%s" class="slv-button slv-button--primary">%s · %s</a>',
        esc_url( $url ),
        esc_html( (string) $atts['text'] ),
        esc_html( slv_product_formatted_price( $product ) )
    );
}