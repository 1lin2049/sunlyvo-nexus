<?php
/**
 * Open Graph / Twitter Card
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_head', 'slv_seo_output_open_graph', 3 );

/**
 * 输出 Open Graph 与 Twitter Card
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_output_open_graph(): void {
    $title       = slv_seo_get_page_title();
    $description = slv_seo_get_page_description();
    $url         = slv_seo_get_canonical_url();
    $image       = slv_seo_get_og_image();
    $site_name   = (string) get_bloginfo( 'name' );
    $type        = slv_seo_get_og_type();

    // Open Graph
    printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );
    printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
    printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
    printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );

    if ( '' !== $description ) {
        printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
    }

    if ( '' !== $image ) {
        printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
        printf( '<meta property="og:image:width" content="1200">' . "\n" );
        printf( '<meta property="og:image:height" content="630">' . "\n" );
    }

    if ( is_singular() ) {
        $post = get_post();
        if ( $post ) {
            printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( DATE_W3C ) ) );
            printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_the_modified_date( DATE_W3C ) ) );

            $author = get_userdata( (int) $post->post_author );
            if ( $author ) {
                printf( '<meta property="article:author" content="%s">' . "\n", esc_attr( $author->display_name ) );
            }
        }
    }

    // Twitter Card
    printf( '<meta name="twitter:card" content="%s">' . "\n", '' !== $image ? 'summary_large_image' : 'summary' );
    printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );

    if ( '' !== $description ) {
        printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
    }

    if ( '' !== $image ) {
        printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
    }

    $twitter_site = (string) slv_get_config( 'seo.twitter_site', '' );
    if ( '' !== $twitter_site ) {
        printf( '<meta name="twitter:site" content="%s">' . "\n", esc_attr( $twitter_site ) );
    }
}

/**
 * 获取 OG 图片
 *
 * @since 1.0.0
 * @return string
 */
function slv_seo_get_og_image(): string {
    if ( is_singular() ) {
        $post_id = get_the_ID();

        // 1. 自定义 OG 图片
        $custom = (string) get_post_meta( $post_id, '_slv_seo_og_image', true );

        if ( '' !== $custom ) {
            return $custom;
        }

        // 2. 特色图像
        if ( has_post_thumbnail( $post_id ) ) {
            $url = get_the_post_thumbnail_url( $post_id, 'full' );

            if ( $url ) {
                return (string) $url;
            }
        }

        // 3. 内容第一张图
        $content = (string) get_post_field( 'post_content', $post_id );

        if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches ) ) {
            return (string) $matches[1];
        }
    }

    // 站点默认 OG 图片
    $default = (string) slv_get_config( 'seo.default_og_image', '' );

    return $default;
}

/**
 * 获取 OG 类型
 *
 * @since 1.0.0
 * @return string
 */
function slv_seo_get_og_type(): string {
    if ( is_singular() ) {
        $post_type = (string) get_post_type();

        if ( 'post' === $post_type ) {
            return 'article';
        }

        if ( 'slv_collection' === $post_type ) {
            return 'book';
        }

        if ( 'slv_chapter' === $post_type ) {
            return 'article';
        }

        return 'website';
    }

    return 'website';
}