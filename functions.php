<?php
/**
 * SunLyvo Nexus 主题入口
 *
 * 只做模块加载，禁止业务逻辑
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 *
 * 项目开发者：李咏燊
 * 开发者微信：getthink-info
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 常量定义
require_once get_template_directory() . '/inc/constants.php';

// i18n 优先加载
require_once SLV_INC_DIR . '/i18n/loader.php';

// 主题设置
require_once SLV_INC_DIR . '/menus.php';

// 布局系统
require_once SLV_INC_DIR . '/layout.php';

// 核心基础设施
require_once SLV_INC_DIR . '/schema.php';
require_once SLV_INC_DIR . '/roles.php';
require_once SLV_INC_DIR . '/permissions.php';
require_once SLV_INC_DIR . '/config.php';
require_once SLV_INC_DIR . '/seed.php';
require_once SLV_INC_DIR . '/cpt.php';
require_once SLV_INC_DIR . '/taxonomy.php';
require_once SLV_INC_DIR . '/content.php';
require_once SLV_INC_DIR . '/access.php';
require_once SLV_INC_DIR . '/enqueue.php';
require_once SLV_INC_DIR . '/helpers.php';

// 后台管理
if ( is_admin() ) {
    $slv_admin_init = SLV_INC_DIR . '/admin/admin-init.php';
    if ( file_exists( $slv_admin_init ) ) {
        require_once $slv_admin_init;
    }
    unset( $slv_admin_init );
}

// 业务模块
$slv_modules = [
    'stats',          // 统计系统（P0-3）
    'membership',
    'points',
    'purchase',
    'commerce',
    'affiliate',
    'warehouse',
    'store-profile',
    'sync',
    'seo',
    'ai',
    'acp',
    'security',
    'performance',
];

foreach ( $slv_modules as $slv_module ) {
    $slv_module_file = SLV_INC_DIR . "/modules/{$slv_module}/module.php";
    if ( file_exists( $slv_module_file ) ) {
        require_once $slv_module_file;
    }
}

unset( $slv_modules, $slv_module, $slv_module_file );

// WP-CLI
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    foreach ( [ 'cli.php', 'cli-setup.php', 'cli-menus.php' ] as $slv_cli_extra ) {
        $slv_cli_path = SLV_INC_DIR . '/' . $slv_cli_extra;
        if ( file_exists( $slv_cli_path ) ) {
            require_once $slv_cli_path;
        }
    }
    unset( $slv_cli_extra, $slv_cli_path );
}