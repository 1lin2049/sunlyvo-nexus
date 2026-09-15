<?php
/**
 * SEO / GEO / AEO 模块
 *
 * - SEO：传统搜索引擎优化
 * - AEO：答案引擎优化（Featured Snippets / Voice）
 * - GEO：生成式引擎优化（AI 引用 / llms.txt）
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/config-defaults.php';
require_once __DIR__ . '/meta-tags.php';
require_once __DIR__ . '/canonical.php';
require_once __DIR__ . '/open-graph.php';
require_once __DIR__ . '/schema-output.php';
require_once __DIR__ . '/faq-snippet.php';
require_once __DIR__ . '/llms-txt.php';
require_once __DIR__ . '/sitemap.php';
require_once __DIR__ . '/robots.php';

if ( is_admin() ) {
    require_once __DIR__ . '/admin.php';
}

// 主题激活时初始化配置
add_action( 'after_switch_theme', 'slv_seo_seed_config_defaults', 30 );