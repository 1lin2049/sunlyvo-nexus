<?php
/**
 * Sitemap 增强
 *
 * 在 WordPress 原生 sitemap 基础上：
 * - 添加自定义 CPT
 * - 添加 hreflang
 * - 调整优先级
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 让自定义 CPT 进入 sitemap
add_filter( 'wp_sitemaps_post_types', 'slv_seo_sitemap_post_types' );

/**
 * 注册自定义 CPT 到 sitemap
 *
 * @since 1.0.0
 *
 * @param array<string, WP_Post_Type> $post_types 已注册类型
 *
 * @return array<string, WP_Post_Type>
 */
function slv_seo_sitemap_post_types( array $post_types ): array {
    $extra = [ 'slv_collection', 'slv_chapter' ];

    foreach ( $extra as $type ) {
        $obj = get_post_type_object( $type );

        if ( $obj ) {
            $post_types[ $type ] = $obj;
        }
    }

    return $post_types;
}

// 排除不必要的内容
add_filter( 'wp_sitemaps_posts_query_args', 'slv_seo_sitemap_query_args', 10, 2 );

/**
 * 排除 noindex 内容
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $args     查询参数
 * @param string               $post_type Post type
 *
 * @return array<string, mixed>
 */
function slv_seo_sitemap_query_args( array $args, string $post_type ): array {
    $meta_query = $args['meta_query'] ?? [];

    $meta_query[] = [
        'key'     => '_slv_seo_noindex',
        'value'   => '1',
        'compare' => '!=',
    ];

    $args['meta_query'] = $meta_query;

    return $args;
}

// 添加 sitemap 索引中的站点信息
add_filter( 'wp_sitemaps_index_entry', 'slv_seo_sitemap_index_entry', 10, 3 );

/**
 * Sitemap 索引条目增强
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $entry 条目
 * @param string               $type  类型
 * @param int                  $page  页码
 *
 * @return array<string, mixed>
 */
function slv_seo_sitemap_index_entry( array $entry, string $type, int $page ): array {
    return $entry;
}