<?php
/**
 * 电商数据表定义
 *
 * 13 张表，覆盖商品、库存、订单、支付、优惠券、购物车全链路。
 *
 * 设计原则：
 * - 支持 product_type 区分数字/物理/服务/订阅
 * - 支持 variant 变体（物理商品的颜色/尺寸）
 * - 支持 warehouse 多仓库
 * - 支持 shipping_zone 配送区域（本轮只建表）
 * - 订单保存商品快照，不依赖商品表
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 创建电商相关表
 *
 * @since 1.0.0
 * @return void
 */
function slv_create_commerce_tables(): void {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $prefix          = $wpdb->prefix;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // -------------------------------------------------------------------------
    // 1. 商品主表
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_products (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        product_type VARCHAR(20) NOT NULL DEFAULT 'digital',
        product_status VARCHAR(20) NOT NULL DEFAULT 'active',
        sku VARCHAR(100) DEFAULT '',
        slug VARCHAR(200) NOT NULL DEFAULT '',
        title VARCHAR(255) NOT NULL,
        subtitle VARCHAR(255) DEFAULT '',
        description LONGTEXT,
        short_description TEXT,
        featured_image_id BIGINT(20) DEFAULT 0,
        gallery_ids TEXT,
        price DECIMAL(12,2) DEFAULT 0,
        compare_price DECIMAL(12,2) DEFAULT 0,
        cost_price DECIMAL(12,2) DEFAULT 0,
        currency VARCHAR(10) DEFAULT 'USD',
        taxable TINYINT(1) DEFAULT 1,
        tax_class VARCHAR(50) DEFAULT '',
        is_virtual TINYINT(1) DEFAULT 1,
        is_downloadable TINYINT(1) DEFAULT 0,
        download_limit INT DEFAULT 0,
        download_expiry_days INT DEFAULT 0,
        weight DECIMAL(10,3) DEFAULT 0,
        length DECIMAL(10,2) DEFAULT 0,
        width DECIMAL(10,2) DEFAULT 0,
        height DECIMAL(10,2) DEFAULT 0,
        shipping_class_id BIGINT(20) DEFAULT 0,
        requires_shipping TINYINT(1) DEFAULT 0,
        stock_status VARCHAR(20) DEFAULT 'in_stock',
        manage_stock TINYINT(1) DEFAULT 0,
        stock_quantity INT DEFAULT 0,
        linked_object_type VARCHAR(50) DEFAULT '',
        linked_object_id BIGINT(20) DEFAULT 0,
        category_ids TEXT,
        menu_order INT DEFAULT 0,
        meta LONGTEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_slug (slug),
        KEY idx_sku (sku),
        KEY idx_type (product_type),
        KEY idx_status (product_status),
        KEY idx_linked (linked_object_type, linked_object_id),
        KEY idx_stock (stock_status),
        KEY idx_menu_order (menu_order)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 2. 商品变体（物理商品用）
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_product_variants (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        product_id BIGINT(20) NOT NULL,
        sku VARCHAR(100) DEFAULT '',
        title VARCHAR(255) NOT NULL DEFAULT '',
        attributes LONGTEXT,
        price DECIMAL(12,2) DEFAULT 0,
        compare_price DECIMAL(12,2) DEFAULT 0,
        cost_price DECIMAL(12,2) DEFAULT 0,
        weight DECIMAL(10,3) DEFAULT 0,
        image_id BIGINT(20) DEFAULT 0,
        stock_quantity INT DEFAULT 0,
        stock_status VARCHAR(20) DEFAULT 'in_stock',
        is_active TINYINT(1) DEFAULT 1,
        menu_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_product_id (product_id),
        KEY idx_sku (sku),
        KEY idx_is_active (is_active)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 3. 仓库（多仓库）
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_warehouses (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        code VARCHAR(50) NOT NULL,
        name VARCHAR(200) NOT NULL,
        address LONGTEXT,
        country_code VARCHAR(5) DEFAULT '',
        province VARCHAR(100) DEFAULT '',
        city VARCHAR(100) DEFAULT '',
        contact_name VARCHAR(100) DEFAULT '',
        contact_phone VARCHAR(50) DEFAULT '',
        priority INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_code (code),
        KEY idx_is_active (is_active)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 4. 库存（按仓库+商品+变体）
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_inventory (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        product_id BIGINT(20) NOT NULL,
        variant_id BIGINT(20) DEFAULT 0,
        warehouse_id BIGINT(20) DEFAULT 0,
        quantity INT DEFAULT 0,
        reserved_quantity INT DEFAULT 0,
        low_stock_threshold INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_inventory (product_id, variant_id, warehouse_id),
        KEY idx_product_id (product_id),
        KEY idx_warehouse_id (warehouse_id)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 5. 配送区域
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_shipping_zones (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        regions LONGTEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        priority INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_is_active (is_active),
        KEY idx_priority (priority)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 6. 配送方式
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_shipping_methods (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        zone_id BIGINT(20) NOT NULL,
        method_type VARCHAR(50) NOT NULL,
        title VARCHAR(200) NOT NULL,
        cost DECIMAL(12,2) DEFAULT 0,
        settings LONGTEXT,
        is_active TINYINT(1) DEFAULT 1,
        menu_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_zone_id (zone_id),
        KEY idx_is_active (is_active)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 7. 订单
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_orders (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        order_number VARCHAR(50) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'pending_payment',
        subtotal DECIMAL(12,2) DEFAULT 0,
        discount_total DECIMAL(12,2) DEFAULT 0,
        shipping_total DECIMAL(12,2) DEFAULT 0,
        tax_total DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(12,2) DEFAULT 0,
        currency VARCHAR(10) DEFAULT 'USD',
        payment_gateway VARCHAR(50) DEFAULT '',
        payment_status VARCHAR(30) DEFAULT 'pending',
        transaction_id VARCHAR(255) DEFAULT '',
        paid_at DATETIME DEFAULT NULL,
        billing_data LONGTEXT,
        shipping_data LONGTEXT,
        customer_note TEXT,
        admin_note TEXT,
        coupon_code VARCHAR(100) DEFAULT '',
        meta LONGTEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        completed_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_order_number (order_number),
        KEY idx_user_id (user_id),
        KEY idx_status (status),
        KEY idx_payment_status (payment_status),
        KEY idx_created_at (created_at)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 8. 订单项（保存商品快照）
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_order_items (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        order_id BIGINT(20) NOT NULL,
        product_id BIGINT(20) NOT NULL,
        variant_id BIGINT(20) DEFAULT 0,
        product_type VARCHAR(20) DEFAULT 'digital',
        sku VARCHAR(100) DEFAULT '',
        title VARCHAR(255) NOT NULL,
        unit_price DECIMAL(12,2) DEFAULT 0,
        quantity INT DEFAULT 1,
        subtotal DECIMAL(12,2) DEFAULT 0,
        discount DECIMAL(12,2) DEFAULT 0,
        tax DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(12,2) DEFAULT 0,
        linked_object_type VARCHAR(50) DEFAULT '',
        linked_object_id BIGINT(20) DEFAULT 0,
        meta LONGTEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_order_id (order_id),
        KEY idx_product_id (product_id),
        KEY idx_linked (linked_object_type, linked_object_id)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 9. 订单状态日志
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_order_status_log (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        order_id BIGINT(20) NOT NULL,
        from_status VARCHAR(30) DEFAULT '',
        to_status VARCHAR(30) NOT NULL,
        note TEXT,
        changed_by BIGINT(20) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_order_id (order_id),
        KEY idx_created_at (created_at)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 10. 支付流水
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_payment_transactions (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        order_id BIGINT(20) NOT NULL,
        gateway VARCHAR(50) NOT NULL,
        gateway_txn_id VARCHAR(255) NOT NULL,
        amount DECIMAL(12,2) DEFAULT 0,
        currency VARCHAR(10) DEFAULT 'USD',
        status VARCHAR(30) NOT NULL,
        raw_request LONGTEXT,
        raw_response LONGTEXT,
        error_message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_gateway_txn (gateway, gateway_txn_id),
        KEY idx_order_id (order_id),
        KEY idx_status (status)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 11. 优惠券
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_coupons (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        code VARCHAR(100) NOT NULL,
        description VARCHAR(255) DEFAULT '',
        discount_type VARCHAR(20) NOT NULL,
        discount_value DECIMAL(12,2) NOT NULL,
        min_order_amount DECIMAL(12,2) DEFAULT 0,
        max_discount DECIMAL(12,2) DEFAULT 0,
        max_uses INT DEFAULT 0,
        max_uses_per_user INT DEFAULT 0,
        used_count INT DEFAULT 0,
        starts_at DATETIME DEFAULT NULL,
        expires_at DATETIME DEFAULT NULL,
        applies_to LONGTEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_code (code),
        KEY idx_is_active (is_active)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 12. 优惠券使用记录
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_coupon_uses (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        coupon_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        order_id BIGINT(20) NOT NULL,
        discount_amount DECIMAL(12,2) DEFAULT 0,
        used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_coupon_id (coupon_id),
        KEY idx_user_id (user_id),
        KEY idx_order_id (order_id)
    ) {$charset_collate};" );

    // -------------------------------------------------------------------------
    // 13. 购物车
    // -------------------------------------------------------------------------
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_carts (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        cart_token VARCHAR(64) NOT NULL,
        user_id BIGINT(20) DEFAULT 0,
        product_id BIGINT(20) NOT NULL,
        variant_id BIGINT(20) DEFAULT 0,
        quantity INT DEFAULT 1,
        meta LONGTEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_cart_item (cart_token, product_id, variant_id),
        KEY idx_user_id (user_id),
        KEY idx_cart_token (cart_token)
    ) {$charset_collate};" );
}