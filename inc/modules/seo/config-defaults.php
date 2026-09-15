<?php
/**
 * SEO / GEO / AEO 配置项定义
 *
 * 所有配置集中于此，后台、前端、CLI 共用。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取所有 SEO 配置项定义
 *
 * 结构：
 * [
 *   'tab'     => 'basic' | 'schema' | 'social' | 'geo' | 'robots' | 'advanced',
 *   'key'     => 'seo.xxx',       // config_key
 *   'label'   => '中文标签',
 *   'type'    => 'text' | 'textarea' | 'checkbox' | 'select' | 'image' | 'number',
 *   'default' => 默认值,
 *   'desc'    => 说明,
 *   'options' => [value => label] （select 用）
 * ]
 *
 * @since 1.0.0
 * @return array<int, array<string, mixed>>
 */
function slv_seo_get_config_definitions(): array {
    $definitions = [

        // ==================== Tab 1：基础 ====================
        [
            'tab'     => 'basic',
            'key'     => 'seo.site_verification_google',
            'label'   => 'Google Search Console 验证码',
            'type'    => 'text',
            'default' => '',
            'desc'    => '格式：content 属性值，例如 "abc123..."',
        ],
        [
            'tab'     => 'basic',
            'key'     => 'seo.site_verification_bing',
            'label'   => 'Bing Webmaster 验证码',
            'type'    => 'text',
            'default' => '',
            'desc'    => 'msvalidate.01 的值',
        ],
        [
            'tab'     => 'basic',
            'key'     => 'seo.title_separator',
            'label'   => '标题分隔符',
            'type'    => 'text',
            'default' => ' · ',
            'desc'    => '用于「页面标题 · 站点名称」之间的分隔',
        ],
        [
            'tab'     => 'basic',
            'key'     => 'seo.enable_schema',
            'label'   => '输出结构化数据（Schema.org）',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '关闭后将停止所有 Schema 输出',
        ],
        [
            'tab'     => 'basic',
            'key'     => 'seo.enable_open_graph',
            'label'   => '输出 Open Graph / Twitter Card',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '关闭后将停止社交分享标签输出',
        ],
        [
            'tab'     => 'basic',
            'key'     => 'seo.enable_canonical',
            'label'   => '输出 Canonical URL',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '关闭后将使用 WordPress 默认 canonical',
        ],

        // ==================== Tab 2：Schema ====================
        [
            'tab'     => 'schema',
            'key'     => 'seo.schema_organization_name',
            'label'   => '组织名称',
            'type'    => 'text',
            'default' => 'SunLyvo',
            'desc'    => '用于 Publisher / Organization Schema',
        ],
        [
            'tab'     => 'schema',
            'key'     => 'seo.schema_organization_logo',
            'label'   => '组织 Logo',
            'type'    => 'image',
            'default' => '',
            'desc'    => '建议 512x512，PNG 或 SVG',
        ],
        [
            'tab'     => 'schema',
            'key'     => 'seo.schema_output_website',
            'label'   => '输出 WebSite Schema',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '站点的搜索功能声明',
        ],
        [
            'tab'     => 'schema',
            'key'     => 'seo.schema_output_breadcrumb',
            'label'   => '输出 BreadcrumbList Schema',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '面包屑导航',
        ],
        [
            'tab'     => 'schema',
            'key'     => 'seo.schema_output_faq',
            'label'   => '自动提取 FAQ Schema',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '从内容的 H2 问句 + 段落自动提取',
        ],

        // ==================== Tab 3：社交 ====================
        [
            'tab'     => 'social',
            'key'     => 'seo.default_og_image',
            'label'   => '默认社交分享图片',
            'type'    => 'image',
            'default' => '',
            'desc'    => '当文章没有特色图像时使用，建议 1200x630',
        ],
        [
            'tab'     => 'social',
            'key'     => 'seo.twitter_site',
            'label'   => 'Twitter / X 账号',
            'type'    => 'text',
            'default' => '',
            'desc'    => '格式：@username',
        ],
        [
            'tab'     => 'social',
            'key'     => 'seo.facebook_app_id',
            'label'   => 'Facebook App ID',
            'type'    => 'text',
            'default' => '',
            'desc'    => '可选，用于 Facebook 分享分析',
        ],

        // ==================== Tab 4：GEO / AI 优化 ====================
        [
            'tab'     => 'geo',
            'key'     => 'seo.enable_llms_txt',
            'label'   => '启用 /llms.txt',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '为 AI 提供精简内容索引',
        ],
        [
            'tab'     => 'geo',
            'key'     => 'seo.enable_llms_full_txt',
            'label'   => '启用 /llms-full.txt',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '为 AI 提供完整内容文本',
        ],
        [
            'tab'     => 'geo',
            'key'     => 'seo.llms_max_chapters',
            'label'   => 'llms-full.txt 最大章节数',
            'type'    => 'number',
            'default' => '100',
            'desc'    => '避免文件过大，超过部分不输出',
        ],
        [
            'tab'     => 'geo',
            'key'     => 'seo.allow_ai_crawlers',
            'label'   => '允许 AI 爬虫',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '允许 GPTBot / ClaudeBot / PerplexityBot 等抓取',
        ],

        // ==================== Tab 5：robots.txt ====================
        [
            'tab'     => 'robots',
            'key'     => 'seo.robots_custom',
            'label'   => '自定义 robots.txt 内容',
            'type'    => 'textarea',
            'default' => '',
            'desc'    => '附加在默认 robots.txt 之后。留空使用默认规则。',
        ],
        [
            'tab'     => 'robots',
            'key'     => 'seo.robots_disallow_search',
            'label'   => '禁止收录搜索结果',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => 'Disallow: /?s=',
        ],
        [
            'tab'     => 'robots',
            'key'     => 'seo.robots_disallow_account',
            'label'   => '禁止收录账户相关页面',
            'type'    => 'checkbox',
            'default' => '1',
            'desc'    => '购物车 / 结算 / 我的订单 / 账户页',
        ],
    ];

    /**
     * 过滤 SEO 配置项定义
     *
     * @since 1.0.0
     *
     * @param array $definitions 配置项列表
     */
    return (array) apply_filters( 'slv_seo_config_definitions', $definitions );
}

/**
 * 按 Tab 分组
 *
 * @since 1.0.0
 *
 * @return array<string, array<int, array<string, mixed>>>
 */
function slv_seo_get_config_by_tab(): array {
    $grouped = [];

    foreach ( slv_seo_get_config_definitions() as $definition ) {
        $tab = (string) ( $definition['tab'] ?? 'basic' );
        $grouped[ $tab ][] = $definition;
    }

    return $grouped;
}

/**
 * 获取 Tab 名称
 *
 * @since 1.0.0
 * @return array<string, string>
 */
function slv_seo_get_tabs(): array {
    return [
        'basic'   => esc_html__( '基础设置', 'sunlyvo-nexus' ),
        'schema'  => esc_html__( '结构化数据', 'sunlyvo-nexus' ),
        'social'  => esc_html__( '社交分享', 'sunlyvo-nexus' ),
        'geo'     => esc_html__( 'GEO / AI 优化', 'sunlyvo-nexus' ),
        'robots'  => esc_html__( 'robots.txt', 'sunlyvo-nexus' ),
        'diag'    => esc_html__( '诊断工具', 'sunlyvo-nexus' ),
    ];
}

/**
 * 初始化默认配置项到数据库
 *
 * 幂等，只在不存在时插入。
 *
 * @since 1.0.0
 * @return int 新增数量
 */
function slv_seo_seed_config_defaults(): int {
    global $wpdb;

    $inserted = 0;

    foreach ( slv_seo_get_config_definitions() as $definition ) {
        $key = (string) ( $definition['key'] ?? '' );

        if ( '' === $key ) {
            continue;
        }

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}slv_config_registry
                 WHERE config_key = %s AND owner_type = 'platform' AND owner_id = 0",
                $key
            )
        );

        if ( $exists ) {
            continue;
        }

        $group = explode( '.', $key )[0] ?? 'seo';

        $wpdb->insert(
            "{$wpdb->prefix}slv_config_registry",
            [
                'config_key'    => $key,
                'config_group'  => $group,
                'config_type'   => (string) ( $definition['type'] ?? 'string' ),
                'default_value' => (string) ( $definition['default'] ?? '' ),
                'config_value'  => (string) ( $definition['default'] ?? '' ),
                'owner_type'    => 'platform',
                'owner_id'      => 0,
                'description'   => (string) ( $definition['desc'] ?? '' ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ]
        );

        $inserted++;
    }

    return $inserted;
}

/**
 * 获取配置值（带默认值）
 *
 * 简化调用：slv_seo_get_config('site_verification_google')
 *
 * @since 1.0.0
 *
 * @param string $short_key 短键（省略 seo. 前缀）
 * @param mixed  $fallback  回退值
 *
 * @return mixed
 */
function slv_seo_get_config( string $short_key, $fallback = null ) {
    $full_key = strpos( $short_key, 'seo.' ) === 0 ? $short_key : 'seo.' . $short_key;

    return slv_get_config( $full_key, $fallback );
}