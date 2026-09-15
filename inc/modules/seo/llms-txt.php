<?php
/**
 * llms.txt / llms-full.txt
 *
 * GEO 优化：为 AI 提供内容索引。
 *
 * 访问：
 * - /llms.txt        → 精简索引
 * - /llms-full.txt   → 完整内容
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'slv_seo_llms_txt_rewrite' );
add_filter( 'query_vars', 'slv_seo_llms_txt_query_vars' );
add_action( 'template_redirect', 'slv_seo_render_llms_txt' );

/**
 * 注册 rewrite 规则
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_llms_txt_rewrite(): void {
    add_rewrite_rule( '^llms\.txt$', 'index.php?slv_llms_txt=1', 'top' );
    add_rewrite_rule( '^llms-full\.txt$', 'index.php?slv_llms_txt=full', 'top' );
}

/**
 * 注册 query var
 *
 * @since 1.0.0
 *
 * @param array<int, string> $vars 已有 vars
 *
 * @return array<int, string>
 */
function slv_seo_llms_txt_query_vars( array $vars ): array {
    $vars[] = 'slv_llms_txt';
    return $vars;
}

/**
 * 输出 llms.txt
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_llms_txt(): void {
    $mode = get_query_var( 'slv_llms_txt' );

    if ( ! $mode ) {
        return;
    }

    // 输出纯文本
    header( 'Content-Type: text/plain; charset=utf-8' );
    header( 'X-Robots-Tag: noindex, follow' );

    if ( 'full' === $mode ) {
        slv_seo_render_llms_full_txt();
    } else {
        slv_seo_render_llms_index_txt();
    }

    exit;
}

/**
 * 精简索引
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_llms_index_txt(): void {
    $lines = [];

    $lines[] = '# ' . (string) get_bloginfo( 'name' );
    $lines[] = '';
    $lines[] = '> ' . (string) get_bloginfo( 'description' );
    $lines[] = '';

    // 站点信息
    $lines[] = '## Site';
    $lines[] = '';
    $lines[] = '- URL: ' . home_url( '/' );
    $lines[] = '- Language: ' . (string) get_bloginfo( 'language' );
    $lines[] = '- Author: ' . SLV_DEVELOPER_NAME;
    $lines[] = '';

    // 合集
    $collections = get_posts(
        [
            'post_type'      => 'slv_collection',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ]
    );

    if ( ! empty( $collections ) ) {
        $lines[] = '## Collections';
        $lines[] = '';

        foreach ( $collections as $collection ) {
            $count = slv_get_collection_count( (int) $collection->ID );
            $time  = slv_get_collection_time( (int) $collection->ID );

            $desc = wp_trim_words( (string) $collection->post_excerpt, 20, '…' );

            $lines[] = sprintf(
                '- [%s](%s): %s（%d 章 · %d 分钟）',
                $collection->post_title,
                get_permalink( $collection ),
                $desc,
                $count,
                $time
            );
        }

        $lines[] = '';
    }

    // 章节
    $chapters = get_posts(
        [
            'post_type'      => 'slv_chapter',
            'posts_per_page' => 200,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]
    );

    if ( ! empty( $chapters ) ) {
        $lines[] = '## Chapters';
        $lines[] = '';

        foreach ( $chapters as $chapter ) {
            $lines[] = sprintf(
                '- [%s](%s)',
                $chapter->post_title,
                get_permalink( $chapter )
            );
        }

        $lines[] = '';
    }

    // 博客
    $posts = get_posts(
        [
            'post_type'      => 'post',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
        ]
    );

    if ( ! empty( $posts ) ) {
        $lines[] = '## Blog';
        $lines[] = '';

        foreach ( $posts as $post ) {
            $lines[] = sprintf(
                '- [%s](%s)',
                $post->post_title,
                get_permalink( $post )
            );
        }

        $lines[] = '';
    }

    // 可选信息
    $lines[] = '## Optional';
    $lines[] = '';
    $lines[] = '- [完整内容索引](' . home_url( '/llms-full.txt' ) . '): 所有内容的完整文本';
    $lines[] = '- [站点地图](' . home_url( '/sitemap.xml' ) . '): XML 站点地图';
    $lines[] = '';

    echo implode( "\n", $lines );
}

/**
 * 完整内容索引
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_llms_full_txt(): void {
    $lines = [];

    $lines[] = '# ' . (string) get_bloginfo( 'name' ) . ' · 完整内容';
    $lines[] = '';
    $lines[] = 'Generated: ' . gmdate( DATE_W3C );
    $lines[] = '';

    // 合集
    $collections = get_posts(
        [
            'post_type'      => 'slv_collection',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
        ]
    );

    foreach ( $collections as $collection ) {
        $lines[] = '=' . str_repeat( '=', 70 );
        $lines[] = 'TITLE: ' . $collection->post_title;
        $lines[] = 'URL: ' . get_permalink( $collection );
        $lines[] = 'TYPE: collection';
        $lines[] = str_repeat( '-', 70 );
        $lines[] = '';
        $lines[] = wp_strip_all_tags( (string) $collection->post_content );
        $lines[] = '';

        // 合集章节
        $chapters = slv_get_collection_chapters( (int) $collection->ID );

        foreach ( $chapters as $chapter ) {
            $lines[] = str_repeat( '-', 70 );
            $lines[] = 'CHAPTER: ' . $chapter->post_title;
            $lines[] = 'URL: ' . get_permalink( $chapter );
            $lines[] = 'NUMBER: ' . slv_get_chapter_number( (int) $chapter->ID );
            $lines[] = str_repeat( '-', 70 );
            $lines[] = '';
            $lines[] = wp_strip_all_tags( (string) $chapter->post_content );
            $lines[] = '';
        }

        $lines[] = '';
    }

    // 博客
    $posts = get_posts(
        [
            'post_type'      => 'post',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
        ]
    );

    foreach ( $posts as $post ) {
        $lines[] = '=' . str_repeat( '=', 70 );
        $lines[] = 'TITLE: ' . $post->post_title;
        $lines[] = 'URL: ' . get_permalink( $post );
        $lines[] = 'TYPE: post';
        $lines[] = 'DATE: ' . $post->post_date_gmt;
        $lines[] = str_repeat( '-', 70 );
        $lines[] = '';
        $lines[] = wp_strip_all_tags( (string) $post->post_content );
        $lines[] = '';
    }

    echo implode( "\n", $lines );
}

// 刷新 rewrite 规则
add_action( 'after_switch_theme', 'slv_seo_llms_txt_flush_rewrite' );

/**
 * 刷新 rewrite 规则
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_llms_txt_flush_rewrite(): void {
    slv_seo_llms_txt_rewrite();
    flush_rewrite_rules();
}