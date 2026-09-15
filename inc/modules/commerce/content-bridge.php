<?php
/**
 * 内容 ↔ 商品桥接
 *
 * 章节/合集发布时自动创建对应商品。
 * 购买 URL 由本模块提供。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'save_post_slv_chapter', 'slv_commerce_sync_chapter_product', 20, 3 );
add_action( 'save_post_slv_collection', 'slv_commerce_sync_collection_product', 20, 3 );
add_action( 'before_delete_post', 'slv_commerce_on_content_delete' );

// 替换购买 URL
add_filter( 'slv_chapter_purchase_url', 'slv_commerce_filter_chapter_purchase_url', 10, 2 );

/**
 * 章节保存时同步商品
 *
 * @since 1.0.0
 *
 * @param int     $post_id 章节ID
 * @param WP_Post $post    文章对象
 * @param bool    $update  是否更新
 *
 * @return void
 */
function slv_commerce_sync_chapter_product( int $post_id, $post, bool $update ): void {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    if ( 'slv_chapter' !== $post->post_type ) {
        return;
    }

    $config = slv_get_chapter_access_config( $post_id );

    // 只处理需购买的章节
    if ( 'purchase' !== $config['type'] || (float) $config['price'] <= 0 ) {
        slv_commerce_deactivate_product( $post_id, 'chapter' );
        return;
    }

    slv_commerce_upsert_product(
        $post_id,
        'chapter',
        (string) $post->post_title,
        (float) $config['price'],
        (string) $post->post_status,
        (string) $post->post_excerpt
    );
}

/**
 * 合集保存时同步商品
 *
 * @since 1.0.0
 *
 * @param int     $post_id 合集ID
 * @param WP_Post $post    文章对象
 * @param bool    $update  是否更新
 *
 * @return void
 */
function slv_commerce_sync_collection_product( int $post_id, $post, bool $update ): void {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    if ( 'slv_collection' !== $post->post_type ) {
        return;
    }

    $price = (float) get_post_meta( $post_id, '_slv_collection_price', true );

    if ( $price <= 0 ) {
        slv_commerce_deactivate_product( $post_id, 'collection' );
        return;
    }

    slv_commerce_upsert_product(
        $post_id,
        'collection',
        (string) $post->post_title,
        $price,
        (string) $post->post_status,
        (string) $post->post_excerpt
    );
}

/**
 * 创建或更新商品
 *
 * @since 1.0.0
 *
 * @param int    $content_id   内容ID
 * @param string $content_type chapter | collection
 * @param string $title        标题
 * @param float  $price        价格
 * @param string $post_status  内容状态
 * @param string $excerpt      描述
 *
 * @return int 商品ID
 */
function slv_commerce_upsert_product( int $content_id, string $content_type, string $title, float $price, string $post_status, string $excerpt = '' ): int {
    if ( $content_id <= 0 || $price <= 0 ) {
        return 0;
    }

    $existing = slv_product_get_by_linked_object( $content_type, $content_id );

    $product_status = 'publish' === $post_status ? 'active' : 'draft';

    $data = [
        'title'              => $title,
        'short_description'  => $excerpt,
        'price'              => $price,
        'product_type'       => SLV_PRODUCT_TYPE_DIGITAL,
        'product_status'     => $product_status,
        'linked_object_type' => $content_type,
        'linked_object_id'   => $content_id,
        'is_virtual'         => true,
        'requires_shipping'  => false,
    ];

    if ( null !== $existing ) {
        // 更新已有商品
        slv_product_update( (int) $existing['id'], $data );
        return (int) $existing['id'];
    }

    // 创建新商品
    $data['slug'] = slv_commerce_generate_product_slug( $content_type, $content_id );

    return slv_product_create( $data );
}

/**
 * 生成商品 slug
 *
 * @since 1.0.0
 *
 * @param string $content_type 内容类型
 * @param int    $content_id   内容ID
 *
 * @return string
 */
function slv_commerce_generate_product_slug( string $content_type, int $content_id ): string {
    $post = get_post( $content_id );

    if ( $post ) {
        return $content_type . '-' . $post->post_name;
    }

    return $content_type . '-' . $content_id;
}

/**
 * 停用商品
 *
 * @since 1.0.0
 *
 * @param int    $content_id   内容ID
 * @param string $content_type 内容类型
 *
 * @return void
 */
function slv_commerce_deactivate_product( int $content_id, string $content_type ): void {
    $existing = slv_product_get_by_linked_object( $content_type, $content_id );

    if ( null === $existing ) {
        return;
    }

    slv_product_update(
        (int) $existing['id'],
        [ 'product_status' => 'archived' ]
    );
}

/**
 * 内容删除时同步删除商品
 *
 * @since 1.0.0
 *
 * @param int $post_id 内容ID
 *
 * @return void
 */
function slv_commerce_on_content_delete( int $post_id ): void {
    $post = get_post( $post_id );

    if ( ! $post || ! in_array( $post->post_type, [ 'slv_chapter', 'slv_collection' ], true ) ) {
        return;
    }

    $content_type = 'slv_chapter' === $post->post_type ? 'chapter' : 'collection';

    $existing = slv_product_get_by_linked_object( $content_type, $post_id );

    if ( null !== $existing ) {
        slv_product_delete( (int) $existing['id'] );
    }
}

/**
 * 替换购买 URL
 *
 * @since 1.0.0
 *
 * @param string $url        默认 URL
 * @param int    $chapter_id 章节ID
 *
 * @return string
 */
function slv_commerce_filter_chapter_purchase_url( string $url, int $chapter_id ): string {
    $user_id = get_current_user_id();

    // 已购 → 返回阅读页
    if ( $user_id > 0 && slv_user_has_purchased_chapter( $user_id, $chapter_id ) ) {
        return (string) get_permalink( $chapter_id );
    }

    $product = slv_product_get_by_linked_object( 'chapter', $chapter_id );

    if ( null === $product || ! slv_product_is_purchasable( $product ) ) {
        return $url;
    }

    // 加购 URL
    return add_query_arg(
        'slv_add_to_cart',
        (int) $product['id'],
        slv_commerce_get_cart_url()
    );
}

/**
 * 获取购物车页面 URL
 *
 * @since 1.0.0
 * @return string
 */
function slv_commerce_get_cart_url(): string {
    $page_id = (int) slv_get_config( 'commerce.cart_page_id', 0 );

    if ( $page_id > 0 ) {
        return (string) get_permalink( $page_id );
    }

    return home_url( '/cart/' );
}

/**
 * 获取结算页面 URL
 *
 * @since 1.0.0
 * @return string
 */
function slv_commerce_get_checkout_url(): string {
    $page_id = (int) slv_get_config( 'commerce.checkout_page_id', 0 );

    if ( $page_id > 0 ) {
        return (string) get_permalink( $page_id );
    }

    return home_url( '/checkout/' );
}

/**
 * 处理加购请求
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_handle_add_to_cart(): void {
    if ( ! isset( $_GET['slv_add_to_cart'] ) ) {
        return;
    }

    $product_id = (int) $_GET['slv_add_to_cart'];

    if ( $product_id <= 0 ) {
        return;
    }

    $result = slv_cart_add( $product_id, 1 );

    $redirect = slv_commerce_get_cart_url();

    if ( $result ) {
        $redirect = add_query_arg( 'slv_cart_added', '1', $redirect );
    } else {
        $redirect = add_query_arg( 'slv_cart_error', '1', $redirect );
    }

    wp_safe_redirect( $redirect );
    exit;
}

add_action( 'template_redirect', 'slv_commerce_handle_add_to_cart' );