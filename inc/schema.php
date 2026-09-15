<?php
/**
 * 数据库表创建与迁移
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SLV_INC_DIR . '/schema-commerce.php';

add_action( 'after_switch_theme', 'slv_migrate_db' );
add_action( 'admin_init', 'slv_maybe_upgrade_db' );

/**
 * 创建所有自定义表
 *
 * @since 1.0.0
 * @return void
 */
function slv_create_tables(): void {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $prefix          = $wpdb->prefix;
    $base_prefix     = $wpdb->base_prefix;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // 会员等级
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_member_levels (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        level_name VARCHAR(100) NOT NULL,
        level_slug VARCHAR(100) NOT NULL,
        level_order INT NOT NULL DEFAULT 0,
        required_spent DECIMAL(12,2) DEFAULT 0,
        required_points INT DEFAULT 0,
        discount_rate DECIMAL(5,2) DEFAULT 0,
        points_multiplier DECIMAL(3,2) DEFAULT 1.00,
        benefits TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_level_slug (level_slug)
    ) {$charset_collate};" );

    // 统一配置
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_config_registry (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        config_key VARCHAR(100) NOT NULL,
        config_group VARCHAR(50) NOT NULL,
        config_type VARCHAR(20) NOT NULL,
        default_value TEXT,
        allowed_values TEXT,
        owner_type VARCHAR(20) NOT NULL,
        owner_id BIGINT(20) DEFAULT 0,
        config_value TEXT,
        is_locked TINYINT(1) DEFAULT 0,
        capability_required VARCHAR(100) DEFAULT '',
        description TEXT,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_config_owner (config_key, owner_type, owner_id),
        KEY idx_config_group (config_group)
    ) {$charset_collate};" );

    // 配置权限
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_config_permissions (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        role VARCHAR(50) NOT NULL,
        config_group VARCHAR(50) NOT NULL,
        can_view TINYINT(1) DEFAULT 1,
        can_edit TINYINT(1) DEFAULT 0,
        can_lock TINYINT(1) DEFAULT 0,
        scope VARCHAR(20) DEFAULT 'own',
        PRIMARY KEY (id),
        UNIQUE KEY uniq_role_group (role, config_group)
    ) {$charset_collate};" );

    // 配置审计
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_config_audit_log (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        config_key VARCHAR(100) NOT NULL,
        owner_type VARCHAR(20) NOT NULL,
        owner_id BIGINT(20) NOT NULL,
        old_value TEXT,
        new_value TEXT,
        changed_by BIGINT(20) NOT NULL,
        changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_config_lookup (config_key, owner_type, owner_id),
        KEY idx_changed_at (changed_at)
    ) {$charset_collate};" );

    // 字段定义
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_field_definitions (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        field_key VARCHAR(100) NOT NULL,
        field_group VARCHAR(50) NOT NULL,
        field_type VARCHAR(20) NOT NULL,
        label VARCHAR(200) NOT NULL,
        default_value TEXT,
        allowed_values TEXT,
        is_required TINYINT(1) DEFAULT 0,
        is_translatable TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_field_key (field_key),
        KEY idx_field_group (field_group)
    ) {$charset_collate};" );

    // 积分流水
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_points_log (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        points INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        reference_id BIGINT(20) DEFAULT 0,
        description VARCHAR(255) DEFAULT '',
        expires_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_user_id (user_id),
        KEY idx_action (action),
        KEY idx_expires_at (expires_at)
    ) {$charset_collate};" );

    // 用户购买
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_user_purchases (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        object_type VARCHAR(20) NOT NULL,
        object_id BIGINT(20) NOT NULL,
        order_id BIGINT(20) DEFAULT 0,
        price DECIMAL(12,2) DEFAULT 0,
        points_used INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'completed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_user_object (user_id, object_type, object_id),
        KEY idx_user_id (user_id),
        KEY idx_object (object_type, object_id),
        KEY idx_status (status)
    ) {$charset_collate};" );

    // 分销收益
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_vendor_earnings (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        vendor_id BIGINT(20) NOT NULL,
        order_id BIGINT(20) NOT NULL,
        gross DECIMAL(12,2) NOT NULL,
        commission DECIMAL(12,2) NOT NULL,
        net DECIMAL(12,2) NOT NULL,
        type VARCHAR(20) DEFAULT 'product',
        status VARCHAR(20) DEFAULT 'pending',
        blog_id BIGINT(20) DEFAULT 0,
        settled_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_vendor_id (vendor_id),
        KEY idx_order_id (order_id),
        KEY idx_status (status)
    ) {$charset_collate};" );

    // 提现
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_withdrawals (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        method VARCHAR(50) NOT NULL,
        account VARCHAR(255) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        reviewed_by BIGINT(20) DEFAULT 0,
        reviewed_at DATETIME DEFAULT NULL,
        paid_at DATETIME DEFAULT NULL,
        remark TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_user_id (user_id),
        KEY idx_status (status)
    ) {$charset_collate};" );

    // 内容-商品
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_content_product (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        content_id BIGINT(20) NOT NULL,
        content_type VARCHAR(50) NOT NULL,
        product_id BIGINT(20) NOT NULL,
        referrer_id BIGINT(20) NOT NULL,
        position INT DEFAULT 0,
        context VARCHAR(50) DEFAULT 'inline',
        clicks BIGINT(20) DEFAULT 0,
        conversions BIGINT(20) DEFAULT 0,
        revenue DECIMAL(12,2) DEFAULT 0,
        commission DECIMAL(12,2) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_content_product_referrer (content_id, content_type, product_id, referrer_id),
        KEY idx_product_id (product_id),
        KEY idx_referrer_id (referrer_id)
    ) {$charset_collate};" );

    // 数字资产
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_digital_assets (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        asset_type VARCHAR(50) NOT NULL,
        asset_format VARCHAR(20) NOT NULL,
        file_path VARCHAR(500) DEFAULT '',
        file_size BIGINT(20) DEFAULT 0,
        page_count INT DEFAULT 0,
        duration INT DEFAULT 0,
        is_free TINYINT(1) DEFAULT 0,
        price DECIMAL(12,2) DEFAULT 0,
        member_only TINYINT(1) DEFAULT 0,
        points_cost INT DEFAULT 0,
        preview_type VARCHAR(20) DEFAULT '',
        preview_count INT DEFAULT 0,
        download_limit INT DEFAULT 0,
        expiry_days INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_post_id (post_id),
        KEY idx_asset_type (asset_type),
        KEY idx_is_free (is_free)
    ) {$charset_collate};" );

    // 学习进度
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_learning_progress (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        asset_id BIGINT(20) NOT NULL,
        chapter_id BIGINT(20) DEFAULT 0,
        progress DECIMAL(5,2) DEFAULT 0,
        position INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'not_started',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_user_asset_chapter (user_id, asset_id, chapter_id),
        KEY idx_user_id (user_id),
        KEY idx_asset_id (asset_id)
    ) {$charset_collate};" );

    // 关注
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_user_follows (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        follower_id BIGINT(20) NOT NULL,
        following_id BIGINT(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_follow_pair (follower_id, following_id),
        KEY idx_following_id (following_id)
    ) {$charset_collate};" );

    // 圈子成员
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_group_members (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        group_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        role VARCHAR(20) DEFAULT 'member',
        status VARCHAR(20) DEFAULT 'active',
        joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_group_user (group_id, user_id),
        KEY idx_user_id (user_id)
    ) {$charset_collate};" );

    // 城市配置
    dbDelta( "CREATE TABLE IF NOT EXISTS {$base_prefix}slv_city_config (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        blog_id BIGINT(20) NOT NULL,
        parent_region_id BIGINT(20) DEFAULT 0,
        city_name VARCHAR(100) NOT NULL,
        city_slug VARCHAR(100) NOT NULL,
        country_code VARCHAR(5) NOT NULL,
        language VARCHAR(10) NOT NULL,
        currency VARCHAR(10) NOT NULL,
        timezone VARCHAR(50) DEFAULT '',
        geo_lat DECIMAL(10,7) DEFAULT 0,
        geo_lng DECIMAL(10,7) DEFAULT 0,
        poi_source VARCHAR(50) DEFAULT '',
        station_master_id BIGINT(20) DEFAULT 0,
        commission_rate DECIMAL(5,2) DEFAULT 20.00,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_blog_city (blog_id),
        KEY idx_parent_region (parent_region_id),
        KEY idx_country_code (country_code),
        KEY idx_language (language)
    ) {$charset_collate};" );

    // 同步规则
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_field_sync_rules (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        field_key VARCHAR(100) NOT NULL,
        sync_type VARCHAR(20) DEFAULT 'full',
        sync_mode VARCHAR(20) DEFAULT 'state',
        seo_behavior VARCHAR(50) DEFAULT '',
        owner_type VARCHAR(20) DEFAULT 'platform',
        owner_id BIGINT(20) DEFAULT 0,
        priority INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_field_owner (field_key, owner_type, owner_id)
    ) {$charset_collate};" );

    // 同步队列
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_sync_queue (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        source_blog_id BIGINT(20) NOT NULL,
        target_blog_id BIGINT(20) NOT NULL,
        post_id BIGINT(20) NOT NULL,
        sync_mode VARCHAR(20) NOT NULL,
        payload TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        priority INT DEFAULT 0,
        retry_count INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        processed_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_status_priority (status, priority),
        KEY idx_target_blog (target_blog_id)
    ) {$charset_collate};" );

    // BYOK 服务配置
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_service_configs (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        owner_type VARCHAR(20) NOT NULL,
        owner_id BIGINT(20) NOT NULL,
        service_type VARCHAR(50) NOT NULL,
        provider VARCHAR(50) NOT NULL,
        config_key VARCHAR(100) NOT NULL,
        config_value TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        priority INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_owner_service_key (owner_type, owner_id, service_type, provider, config_key),
        KEY idx_owner_lookup (owner_type, owner_id, service_type)
    ) {$charset_collate};" );

    // BYOK 服务日志
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_service_logs (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        owner_type VARCHAR(20) NOT NULL,
        owner_id BIGINT(20) NOT NULL,
        service_type VARCHAR(50) NOT NULL,
        provider VARCHAR(50) NOT NULL,
        action VARCHAR(100) NOT NULL,
        status VARCHAR(20) NOT NULL,
        tokens_used INT DEFAULT 0,
        cost DECIMAL(10,4) DEFAULT 0,
        latency_ms INT DEFAULT 0,
        error_message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_owner_lookup (owner_type, owner_id, service_type),
        KEY idx_created_at (created_at)
    ) {$charset_collate};" );

    // === 统计系统 3 张表 ===

    // 每篇聚合
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_post_stats (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        views BIGINT(20) DEFAULT 0,
        reads BIGINT(20) DEFAULT 0,
        time_total BIGINT(20) DEFAULT 0,
        p25 BIGINT(20) DEFAULT 0,
        p50 BIGINT(20) DEFAULT 0,
        p75 BIGINT(20) DEFAULT 0,
        p100 BIGINT(20) DEFAULT 0,
        avg_scroll DECIMAL(5,2) DEFAULT 0,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_post_id (post_id)
    ) {$charset_collate};" );

    // 按天聚合
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_post_daily (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        stat_date DATE NOT NULL,
        views BIGINT(20) DEFAULT 0,
        reads BIGINT(20) DEFAULT 0,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_post_date (post_id, stat_date),
        KEY idx_stat_date (stat_date)
    ) {$charset_collate};" );

    // 原始事件
    dbDelta( "CREATE TABLE IF NOT EXISTS {$prefix}slv_track_log (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        event VARCHAR(20) NOT NULL,
        value INT DEFAULT 0,
        visitor_id VARCHAR(64) DEFAULT '',
        user_id BIGINT(20) DEFAULT 0,
        ip_hash VARCHAR(64) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_post_event (post_id, event),
        KEY idx_visitor (visitor_id),
        KEY idx_created_at (created_at)
    ) {$charset_collate};" );

    // 电商表
    slv_create_commerce_tables();

    update_option( 'slv_db_version', SLV_DB_VERSION );
}

function slv_migrate_db(): void {
    $current_version = get_option( 'slv_db_version', '0' );
    if ( version_compare( $current_version, SLV_DB_VERSION, '<' ) ) {
        slv_create_tables();
    }
}

function slv_maybe_upgrade_db(): void {
    $current_version = get_option( 'slv_db_version', '0' );
    if ( version_compare( $current_version, SLV_DB_VERSION, '<' ) ) {
        slv_create_tables();
    }
}