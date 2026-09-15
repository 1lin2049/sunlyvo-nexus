<?php
/**
 * 全局常量定义
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 版本
define( 'SLV_VERSION', '1.1.0' );
define( 'SLV_DB_VERSION', '1.4.0' );

// 路径
define( 'SLV_THEME_DIR', get_template_directory() );
define( 'SLV_THEME_URI', get_template_directory_uri() );
define( 'SLV_INC_DIR', SLV_THEME_DIR . '/inc' );
define( 'SLV_ASSETS_URI', SLV_THEME_URI . '/assets' );

// 项目 URI
define( 'SLV_PROJECT_URI', 'https://nexus.getthink.info' );

// 文本域
define( 'SLV_TEXTDOMAIN', 'sunlyvo-nexus' );

// REST
define( 'SLV_REST_NAMESPACE', 'slv/v1' );

// 缓存
define( 'SLV_CACHE_GROUP', 'slv' );
define( 'SLV_CONFIG_CACHE_TTL', 3600 );
define( 'SLV_QUERY_CACHE_TTL', 300 );

// 默认值
define( 'SLV_DEFAULT_COMMISSION_RATE', 10.00 );
define( 'SLV_DEFAULT_POINTS_RATE', 1.00 );
define( 'SLV_DEFAULT_CURRENCY', 'USD' );

// 电商常量
define( 'SLV_ORDER_STATUS_PENDING_PAYMENT', 'pending_payment' );
define( 'SLV_ORDER_STATUS_PAID',            'paid' );
define( 'SLV_ORDER_STATUS_PROCESSING',      'processing' );
define( 'SLV_ORDER_STATUS_COMPLETED',       'completed' );
define( 'SLV_ORDER_STATUS_CANCELLED',       'cancelled' );
define( 'SLV_ORDER_STATUS_REFUNDED',        'refunded' );
define( 'SLV_ORDER_STATUS_FAILED',          'failed' );

define( 'SLV_PRODUCT_TYPE_DIGITAL',      'digital' );
define( 'SLV_PRODUCT_TYPE_PHYSICAL',     'physical' );
define( 'SLV_PRODUCT_TYPE_SERVICE',      'service' );
define( 'SLV_PRODUCT_TYPE_SUBSCRIPTION', 'subscription' );

define( 'SLV_CART_TOKEN_COOKIE', 'slv_cart_token' );
define( 'SLV_CART_TOKEN_TTL',    MONTH_IN_SECONDS );

// 布局系统
define( 'SLV_LAYOUT_DEFAULT', 'wide' );
define( 'SLV_LAYOUT_OPTIONS', [
    'fullscreen'    => '全屏',
    'wide'          => '全宽 1200px',
    'narrow'        => '窄栏 820px',
    'sidebar-left'  => '左侧栏',
    'sidebar-right' => '右侧栏',
    'custom'        => '自定义宽度',
] );

// 环境
define( 'SLV_MIN_PHP_VERSION', '8.2' );
define( 'SLV_MIN_WP_VERSION', '6.4' );

// 开发者
define( 'SLV_DEVELOPER_NAME', '李咏燊' );
define( 'SLV_DEVELOPER_WECHAT', 'getthink-info' );