<?php
/**
 * 商品 CRUD
 *
 * 商品是独立实体，通过 linked_object_type / linked_object_id 关联内容。
 * 未来物理商品可独立存在（linked_object_id = 0）。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取商品表名
 *
 * @since 1.0.0
 * @return string
 */
function slv_product_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'slv_products';
}

/**
 * 创建商品
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $data 商品数据
 *
 * @return int 商品ID，失败返回 0
 */
function slv_product_create( array $data ): int {
    global $wpdb;

    $title = isset( $data['title'] ) ? sanitize_text_field( (string) $data['title'] ) : '';

    if ( '' === $title ) {
        return 0;
    }

    $slug = isset( $data['slug'] ) ? sanitize_title( (string) $data['slug'] ) : sanitize_title( $title );

    $slug = slv_product_unique_slug( $slug );

    $product_type   = isset( $data['product_type'] ) ? sanitize_key( (string) $data['product_type'] ) : 'digital';
    $product_status = isset( $data['product_status'] ) ? sanitize_key( (string) $data['product_status'] ) : 'active';

    if ( ! in_array( $product_type, [ 'digital', 'physical', 'service', 'subscription' ], true ) ) {
        $product_type = 'digital';
    }

    $is_virtual        = 'digital' === $product_type || 'service' === $product_type || 'subscription' === $product_type;
    $requires_shipping = 'physical' === $product_type;

    // 字段顺序必须与 $formats 数组完全一致
    $record = [
        'product_type'         => $product_type,                                              // 1  %s
        'product_status'       => $product_status,                                            // 2  %s
        'sku'                  => isset( $data['sku'] ) ? sanitize_text_field( (string) $data['sku'] ) : '',  // 3  %s
        'slug'                 => $slug,                                                      // 4  %s
        'title'                => $title,                                                     // 5  %s
        'subtitle'             => isset( $data['subtitle'] ) ? sanitize_text_field( (string) $data['subtitle'] ) : '',  // 6  %s
        'description'          => isset( $data['description'] ) ? wp_kses_post( (string) $data['description'] ) : '',    // 7  %s
        'short_description'    => isset( $data['short_description'] ) ? sanitize_textarea_field( (string) $data['short_description'] ) : '',  // 8  %s
        'featured_image_id'    => isset( $data['featured_image_id'] ) ? (int) $data['featured_image_id'] : 0,  // 9  %d
        'gallery_ids'          => isset( $data['gallery_ids'] ) && is_array( $data['gallery_ids'] ) ? wp_json_encode( array_map( 'intval', $data['gallery_ids'] ) ) : null,  // 10 %s
        'price'                => isset( $data['price'] ) ? (float) $data['price'] : 0.0,     // 11 %f
        'compare_price'        => isset( $data['compare_price'] ) ? (float) $data['compare_price'] : 0.0,  // 12 %f
        'cost_price'           => isset( $data['cost_price'] ) ? (float) $data['cost_price'] : 0.0,        // 13 %f
        'currency'             => isset( $data['currency'] ) ? sanitize_text_field( (string) $data['currency'] ) : (string) slv_get_config( 'general.currency', SLV_DEFAULT_CURRENCY ),  // 14 %s
        'taxable'              => isset( $data['taxable'] ) ? (int) (bool) $data['taxable'] : 1,  // 15 %d
        'tax_class'            => isset( $data['tax_class'] ) ? sanitize_key( (string) $data['tax_class'] ) : '',  // 16 %s
        'is_virtual'           => $is_virtual ? 1 : 0,                                        // 17 %d
        'is_downloadable'      => isset( $data['is_downloadable'] ) ? (int) (bool) $data['is_downloadable'] : ( 'digital' === $product_type ? 1 : 0 ),  // 18 %d
        'download_limit'       => isset( $data['download_limit'] ) ? (int) $data['download_limit'] : 0,           // 19 %d
        'download_expiry_days' => isset( $data['download_expiry_days'] ) ? (int) $data['download_expiry_days'] : 0,  // 20 %d
        'weight'               => isset( $data['weight'] ) ? (float) $data['weight'] : 0.0,   // 21 %f
        'length'               => isset( $data['length'] ) ? (float) $data['length'] : 0.0,   // 22 %f
        'width'                => isset( $data['width'] ) ? (float) $data['width'] : 0.0,     // 23 %f
        'height'               => isset( $data['height'] ) ? (float) $data['height'] : 0.0,   // 24 %f
        'shipping_class_id'    => isset( $data['shipping_class_id'] ) ? (int) $data['shipping_class_id'] : 0,  // 25 %d
        'requires_shipping'    => $requires_shipping ? 1 : 0,                                 // 26 %d
        'stock_status'         => isset( $data['stock_status'] ) ? sanitize_key( (string) $data['stock_status'] ) : 'in_stock',  // 27 %s
        'manage_stock'         => isset( $data['manage_stock'] ) ? (int) (bool) $data['manage_stock'] : 0,  // 28 %d
        'stock_quantity'       => isset( $data['stock_quantity'] ) ? (int) $data['stock_quantity'] : 0,     // 29 %d
        'linked_object_type'   => isset( $data['linked_object_type'] ) ? sanitize_key( (string) $data['linked_object_type'] ) : '',  // 30 %s
        'linked_object_id'     => isset( $data['linked_object_id'] ) ? (int) $data['linked_object_id'] : 0,  // 31 %d
        'category_ids'         => isset( $data['category_ids'] ) && is_array( $data['category_ids'] ) ? wp_json_encode( array_map( 'intval', $data['category_ids'] ) ) : null,  // 32 %s
        'menu_order'           => isset( $data['menu_order'] ) ? (int) $data['menu_order'] : 0,  // 33 %d
        'meta'                 => isset( $data['meta'] ) && is_array( $data['meta'] ) ? wp_json_encode( $data['meta'] ) : null,  // 34 %s
    ];

    // 与 $record 一一对应的格式数组（34 个）
    $formats = [
        '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',   // 1-8
        '%d', '%s',                                        // 9-10
        '%f', '%f', '%f',                                  // 11-13
        '%s', '%d', '%s',                                  // 14-16
        '%d', '%d', '%d', '%d',                            // 17-20
        '%f', '%f', '%f', '%f',                            // 21-24
        '%d', '%d', '%s', '%d', '%d',                      // 25-29
        '%s', '%d', '%s', '%d', '%s',                      // 30-34
    ];

    $inserted = $wpdb->insert( slv_product_table(), $record, $formats );

    if ( false === $inserted ) {
        slv_log( 'Product create failed: ' . $wpdb->last_error, 'slv_commerce' );
        return 0;
    }

    $product_id = (int) $wpdb->insert_id;

    /**
     * 商品创建后触发
     *
     * @since 1.0.0
     *
     * @param int   $product_id 商品ID
     * @param array $data       商品数据
     */
    do_action( 'slv_product_created', $product_id, $data );

    return $product_id;
}

/**
 * 更新商品
 *
 * @since 1.0.0
 *
 * @param int                  $product_id 商品ID
 * @param array<string, mixed> $data       更新数据
 *
 * @return bool
 */
function slv_product_update( int $product_id, array $data ): bool {
    if ( $product_id <= 0 ) {
        return false;
    }

    global $wpdb;

    $existing = slv_product_get( $product_id );

    if ( null === $existing ) {
        return false;
    }

    $update = [];
    $formats = [];

    $string_fields = [ 'title', 'subtitle', 'sku', 'tax_class', 'currency', 'stock_status', 'product_status', 'product_type' ];
    foreach ( $string_fields as $field ) {
        if ( isset( $data[ $field ] ) ) {
            $update[ $field ] = sanitize_text_field( (string) $data[ $field ] );
            $formats[] = '%s';
        }
    }

    if ( isset( $data['description'] ) ) {
        $update['description'] = wp_kses_post( (string) $data['description'] );
        $formats[] = '%s';
    }

    if ( isset( $data['short_description'] ) ) {
        $update['short_description'] = sanitize_textarea_field( (string) $data['short_description'] );
        $formats[] = '%s';
    }

    $int_fields = [ 'featured_image_id', 'download_limit', 'download_expiry_days', 'shipping_class_id', 'stock_quantity', 'linked_object_id', 'menu_order' ];
    foreach ( $int_fields as $field ) {
        if ( isset( $data[ $field ] ) ) {
            $update[ $field ] = (int) $data[ $field ];
            $formats[] = '%d';
        }
    }

    $float_fields = [ 'price', 'compare_price', 'cost_price', 'weight', 'length', 'width', 'height' ];
    foreach ( $float_fields as $field ) {
        if ( isset( $data[ $field ] ) ) {
            $update[ $field ] = (float) $data[ $field ];
            $formats[] = '%f';
        }
    }

    $bool_fields = [ 'taxable', 'is_virtual', 'is_downloadable', 'requires_shipping', 'manage_stock' ];
    foreach ( $bool_fields as $field ) {
        if ( isset( $data[ $field ] ) ) {
            $update[ $field ] = (int) (bool) $data[ $field ];
            $formats[] = '%d';
        }
    }

    if ( isset( $data['linked_object_type'] ) ) {
        $update['linked_object_type'] = sanitize_key( (string) $data['linked_object_type'] );
        $formats[] = '%s';
    }

    if ( isset( $data['gallery_ids'] ) && is_array( $data['gallery_ids'] ) ) {
        $update['gallery_ids'] = wp_json_encode( array_map( 'intval', $data['gallery_ids'] ) );
        $formats[] = '%s';
    }

    if ( isset( $data['category_ids'] ) && is_array( $data['category_ids'] ) ) {
        $update['category_ids'] = wp_json_encode( array_map( 'intval', $data['category_ids'] ) );
        $formats[] = '%s';
    }

    if ( isset( $data['meta'] ) && is_array( $data['meta'] ) ) {
        $update['meta'] = wp_json_encode( $data['meta'] );
        $formats[] = '%s';
    }

    if ( empty( $update ) ) {
        return true;
    }

    $updated = $wpdb->update(
        slv_product_table(),
        $update,
        [ 'id' => $product_id ],
        $formats,
        [ '%d' ]
    );

    if ( false === $updated ) {
        return false;
    }

    /**
     * 商品更新后触发
     *
     * @since 1.0.0
     *
     * @param int   $product_id 商品ID
     * @param array $data       更新数据
     */
    do_action( 'slv_product_updated', $product_id, $data );

    return true;
}

/**
 * 获取商品
 *
 * @since 1.0.0
 *
 * @param int $product_id 商品ID
 *
 * @return array<string, mixed>|null
 */
function slv_product_get( int $product_id ): ?array {
    if ( $product_id <= 0 ) {
        return null;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_product_table() . ' WHERE id = %d LIMIT 1',
            $product_id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        return null;
    }

    return slv_product_cast( $row );
}

/**
 * 根据 slug 获取商品
 *
 * @since 1.0.0
 *
 * @param string $slug 商品 slug
 *
 * @return array<string, mixed>|null
 */
function slv_product_get_by_slug( string $slug ): ?array {
    if ( '' === $slug ) {
        return null;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_product_table() . ' WHERE slug = %s LIMIT 1',
            $slug
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        return null;
    }

    return slv_product_cast( $row );
}

/**
 * 根据关联对象获取商品
 *
 * @since 1.0.0
 *
 * @param string $object_type 对象类型（chapter / collection / ...）
 * @param int    $object_id   对象ID
 *
 * @return array<string, mixed>|null
 */
function slv_product_get_by_linked_object( string $object_type, int $object_id ): ?array {
    if ( '' === $object_type || $object_id <= 0 ) {
        return null;
    }

    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT * FROM ' . slv_product_table() . '
             WHERE linked_object_type = %s AND linked_object_id = %d
             AND product_status != %s
             LIMIT 1',
            $object_type,
            $object_id,
            'archived'
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        return null;
    }

    return slv_product_cast( $row );
}

/**
 * 删除商品
 *
 * @since 1.0.0
 *
 * @param int $product_id 商品ID
 *
 * @return bool
 */
function slv_product_delete( int $product_id ): bool {
    if ( $product_id <= 0 ) {
        return false;
    }

    global $wpdb;

    $deleted = $wpdb->delete(
        slv_product_table(),
        [ 'id' => $product_id ],
        [ '%d' ]
    );

    if ( false === $deleted ) {
        return false;
    }

    do_action( 'slv_product_deleted', $product_id );

    return true;
}

/**
 * 获取商品列表
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $args 查询参数
 *
 * @return array<int, array<string, mixed>>
 */
function slv_product_list( array $args = [] ): array {
    global $wpdb;

    $defaults = [
        'product_type'   => '',
        'product_status' => 'active',
        'linked_type'    => '',
        'limit'          => 20,
        'offset'         => 0,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ];

    $args = wp_parse_args( $args, $defaults );

    $where  = [ '1=1' ];
    $params = [];

    if ( '' !== $args['product_type'] ) {
        $where[]  = 'product_type = %s';
        $params[] = (string) $args['product_type'];
    }

    if ( '' !== $args['product_status'] ) {
        $where[]  = 'product_status = %s';
        $params[] = (string) $args['product_status'];
    }

    if ( '' !== $args['linked_type'] ) {
        $where[]  = 'linked_object_type = %s';
        $params[] = (string) $args['linked_type'];
    }

    $orderby_whitelist = [ 'id', 'title', 'price', 'menu_order', 'created_at', 'updated_at' ];
    $orderby = in_array( $args['orderby'], $orderby_whitelist, true ) ? $args['orderby'] : 'menu_order';
    $order   = 'DESC' === strtoupper( (string) $args['order'] ) ? 'DESC' : 'ASC';

    $limit  = max( 1, min( 500, (int) $args['limit'] ) );
    $offset = max( 0, (int) $args['offset'] );

    $sql = 'SELECT * FROM ' . slv_product_table() . ' WHERE ' . implode( ' AND ', $where )
         . " ORDER BY {$orderby} {$order}, id ASC LIMIT %d OFFSET %d";

    $params[] = $limit;
    $params[] = $offset;

    $sql = $wpdb->prepare( $sql, $params );

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    if ( ! is_array( $rows ) ) {
        return [];
    }

    return array_map( 'slv_product_cast', $rows );
}

/**
 * 商品行数据转换
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $row 数据库行
 *
 * @return array<string, mixed>
 */
function slv_product_cast( array $row ): array {
    $row['id']                = (int) $row['id'];
    $row['price']             = (float) $row['price'];
    $row['compare_price']     = (float) $row['compare_price'];
    $row['cost_price']        = (float) $row['cost_price'];
    $row['weight']            = (float) $row['weight'];
    $row['length']            = (float) $row['length'];
    $row['width']             = (float) $row['width'];
    $row['height']            = (float) $row['height'];
    $row['taxable']           = (bool) $row['taxable'];
    $row['is_virtual']        = (bool) $row['is_virtual'];
    $row['is_downloadable']   = (bool) $row['is_downloadable'];
    $row['requires_shipping'] = (bool) $row['requires_shipping'];
    $row['manage_stock']      = (bool) $row['manage_stock'];
    $row['stock_quantity']    = (int) $row['stock_quantity'];
    $row['linked_object_id']  = (int) $row['linked_object_id'];
    $row['featured_image_id'] = (int) $row['featured_image_id'];
    $row['menu_order']        = (int) $row['menu_order'];

    $row['gallery_ids']  = slv_product_decode_json_array( $row['gallery_ids'] ?? null );
    $row['category_ids'] = slv_product_decode_json_array( $row['category_ids'] ?? null );
    $row['meta']         = slv_product_decode_json_object( $row['meta'] ?? null );

    return $row;
}

/**
 * JSON 数组解码
 *
 * @since 1.0.0
 *
 * @param mixed $value 原值
 *
 * @return array<int, int>
 */
function slv_product_decode_json_array( $value ): array {
    if ( ! is_string( $value ) || '' === $value ) {
        return [];
    }

    $decoded = json_decode( $value, true );

    if ( ! is_array( $decoded ) ) {
        return [];
    }

    return array_map( 'intval', $decoded );
}

/**
 * JSON 对象解码
 *
 * @since 1.0.0
 *
 * @param mixed $value 原值
 *
 * @return array<string, mixed>
 */
function slv_product_decode_json_object( $value ): array {
    if ( ! is_string( $value ) || '' === $value ) {
        return [];
    }

    $decoded = json_decode( $value, true );

    return is_array( $decoded ) ? $decoded : [];
}

/**
 * 生成唯一 slug
 *
 * @since 1.0.0
 *
 * @param string $slug       原始 slug
 * @param int    $exclude_id 排除的商品ID
 *
 * @return string
 */
function slv_product_unique_slug( string $slug, int $exclude_id = 0 ): string {
    global $wpdb;

    $base   = $slug;
    $suffix = 1;

    while ( true ) {
        $sql    = 'SELECT id FROM ' . slv_product_table() . ' WHERE slug = %s';
        $params = [ $slug ];

        if ( $exclude_id > 0 ) {
            $sql .= ' AND id != %d';
            $params[] = $exclude_id;
        }

        $sql .= ' LIMIT 1';

        $existing = $wpdb->get_var( $wpdb->prepare( $sql, $params ) );

        if ( null === $existing ) {
            return $slug;
        }

        $suffix++;
        $slug = $base . '-' . $suffix;
    }
}

/**
 * 判断商品是否可购买
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $product 商品
 *
 * @return bool
 */
function slv_product_is_purchasable( array $product ): bool {
    if ( 'active' !== ( $product['product_status'] ?? '' ) ) {
        return false;
    }

    if ( 'out_of_stock' === ( $product['stock_status'] ?? '' ) ) {
        return false;
    }

    if ( (float) ( $product['price'] ?? 0 ) <= 0 ) {
        return false;
    }

    return true;
}

/**
 * 获取商品格式化价格
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $product 商品
 *
 * @return string
 */
function slv_product_formatted_price( array $product ): string {
    return slv_format_price( (float) ( $product['price'] ?? 0 ), (string) ( $product['currency'] ?? '' ) );
}