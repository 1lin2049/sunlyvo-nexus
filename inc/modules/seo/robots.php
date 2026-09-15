<?php
/**
 * robots.txt 增强
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'robots_txt', 'slv_seo_custom_robots_txt', 10, 2 );

/**
 * 自定义 robots.txt 输出
 *
 * @since 1.0.0
 *
 * @param string $output 默认输出
 * @param bool   $public 是否公开
 *
 * @return string
 */
function slv_seo_custom_robots_txt( string $output, bool $public ): string {
    if ( ! $public ) {
        return $output;
    }

    $lines = [
        'User-agent: *',
        'Allow: /',
        '',
        '# 排除',
        'Disallow: /wp-admin/',
        'Allow: /wp-admin/admin-ajax.php',
        'Disallow: /wp-login.php',
        'Disallow: /?s=',
        'Disallow: /search/',
        'Disallow: /*?add-to-cart=',
        'Disallow: /cart/',
        'Disallow: /checkout/',
        'Disallow: /my-orders/',
        'Disallow: /order-view/',
        '',
        '# AI 爬虫（GEO / AEO）',
        'User-agent: GPTBot',
        'Allow: /',
        '',
        'User-agent: ChatGPT-User',
        'Allow: /',
        '',
        'User-agent: PerplexityBot',
        'Allow: /',
        '',
        'User-agent: ClaudeBot',
        'Allow: /',
        '',
        'User-agent: Google-Extended',
        'Allow: /',
        '',
        '# 站点地图',
        'Sitemap: ' . home_url( '/wp-sitemap.xml' ),
        'Sitemap: ' . home_url( '/llms.txt' ),
        '',
    ];

    return implode( "\n", $lines );
}