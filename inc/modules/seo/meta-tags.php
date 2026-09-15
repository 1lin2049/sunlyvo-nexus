<?php
/**
 * 基础 Meta 标签
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 移除 WordPress 默认输出的冗余标签
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

add_action( 'wp_head', 'slv_seo_output_meta_tags', 1 );

/**
 * 输出基础 meta 标签
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_output_meta_tags(): void {
    $title       = slv_seo_get_page_title();
    $description = slv_seo_get_page_description();
    $keywords    = slv_seo_get_page_keywords();
    $robots      = slv_seo_get_robots_meta();

    // 标准 meta
    if ( '' !== $description ) {
        printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
    }

    if ( '' !== $keywords ) {
        printf( '<meta name="keywords" content="%s">' . "\n", esc_attr( $keywords ) );
    }

    printf( '<meta name="robots" content="%s">' . "\n", esc_attr( $robots ) );

    // 编辑者信息
    if ( is_singular() ) {
        $post = get_post();
        if ( $post ) {
            $author = get_userdata( (int) $post->post_author );
            if ( $author ) {
                printf( '<meta name="author" content="%s">' . "\n", esc_attr( $author->display_name ) );
            }
        }
    }

    // 站点验证
    $verification = (string) slv_get_config( 'seo.google_verification', '' );
    if ( '' !== $verification ) {
        printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $verification ) );
    }

    $bing = (string) slv_get_config( 'seo.bing_verification', '' );
    if ( '' !== $bing ) {
        printf( '<meta name="msvalidate.01" content="%s">' . "\n", esc_attr( $bing ) );
    }
}

/**
 * 获取页面标题
 *
 * @since 1.0.0
 * @return string
 */
function slv_seo_get_page_title(): string {
    if ( is_singular() ) {
        return (string) get_the_title();
    }

    if ( is_home() && ! is_front_page() ) {
        return (string) single_post_title( '', false );
    }

    if ( is_archive() ) {
        return (string) wp_strip_all_tags( get_the_archive_title() );
    }

    if ( is_search() ) {
        return sprintf(
            /* translators: %s: query */
            __( '搜索结果：%s', 'sunlyvo-nexus' ),
            get_search_query()
        );
    }

    return (string) get_bloginfo( 'name' );
}

/**
 * 获取页面描述
 *
 * @since 1.0.0
 * @return string
 */
function slv_seo_get_page_description(): string {
    if ( is_singular() ) {
        $post_id = get_the_ID();

        // 优先使用自定义 SEO 描述
        $custom = (string) get_post_meta( $post_id, '_slv_seo_description', true );

        if ( '' !== $custom ) {
            return $custom;
        }

        // 使用摘要
        $excerpt = get_the_excerpt();

        if ( '' !== $excerpt ) {
            return wp_trim_words( wp_strip_all_tags( $excerpt ), 30, '…' );
        }

        // 从内容提取
        $content = (string) get_post_field( 'post_content', $post_id );
        return wp_trim_words( wp_strip_all_tags( $content ), 30, '…' );
    }

    if ( is_home() ) {
        return (string) get_bloginfo( 'description' );
    }

    if ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();

        if ( $term instanceof WP_Term ) {
            if ( '' !== $term->description ) {
                return wp_trim_words( $term->description, 30, '…' );
            }

            return sprintf(
                /* translators: %s: term name */
                __( '关于「%s」的全部内容。', 'sunlyvo-nexus' ),
                $term->name
            );
        }
    }

    return (string) get_bloginfo( 'description' );
}

/**
 * 获取页面关键词
 *
 * @since 1.0.0
 * @return string
 */
function slv_seo_get_page_keywords(): string {
    if ( ! is_singular() ) {
        return '';
    }

    $post_id = get_the_ID();

    // 自定义关键词
    $custom = (string) get_post_meta( $post_id, '_slv_seo_keywords', true );

    if ( '' !== $custom ) {
        return $custom;
    }

    // 从分类/标签提取
    $keywords = [];

    $taxonomies = get_object_taxonomies( get_post_type( $post_id ) );

    foreach ( $taxonomies as $taxonomy ) {
        $terms = get_the_terms( $post_id, $taxonomy );

        if ( is_array( $terms ) ) {
            foreach ( $terms as $term ) {
                $keywords[] = $term->name;
            }
        }
    }

    return implode( ', ', array_slice( $keywords, 0, 10 ) );
}

/**
 * 获取 robots meta 内容
 *
 * @since 1.0.0
 * @return string
 */
function slv_seo_get_robots_meta(): string {
    $parts = [ 'index', 'follow' ];

    if ( is_search() || is_404() ) {
        $parts = [ 'noindex', 'nofollow' ];
    }

    // 单页覆盖
    if ( is_singular() ) {
        $noindex = (int) get_post_meta( get_the_ID(), '_slv_seo_noindex', true );

        if ( 1 === $noindex ) {
            $parts = [ 'noindex', 'nofollow' ];
        }
    }

    $parts[] = 'max-snippet:-1';
    $parts[] = 'max-image-preview:large';
    $parts[] = 'max-video-preview:-1';

    return implode( ', ', $parts );
}