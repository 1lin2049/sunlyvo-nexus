<?php
/**
 * Canonical URL 处理
 *
 * 支持多站点同步内容的动态 canonical：
 * - 状态同步 → 指向主站
 * - 状态帧同步 → 指向分站
 * - 帧同步 → 无跨站 canonical
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

remove_action( 'wp_head', 'rel_canonical' );
add_action( 'wp_head', 'slv_seo_output_canonical', 2 );

/**
 * 输出 canonical
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_output_canonical(): void {
    $url = slv_seo_get_canonical_url();

    if ( '' === $url ) {
        return;
    }

    printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
}

/**
 * 计算 canonical URL
 *
 * @since 1.0.0
 * @return string
 */
function slv_seo_get_canonical_url(): string {
    // 单页手动覆盖
    if ( is_singular() ) {
        $custom = (string) get_post_meta( get_the_ID(), '_slv_seo_canonical', true );

        if ( '' !== $custom ) {
            return $custom;
        }

        // 多站点同步逻辑
        $sync_mode = (string) get_post_meta( get_the_ID(), '_slv_sync_mode', true );
        $source_blog = (int) get_post_meta( get_the_ID(), '_slv_source_blog_id', true );

        if ( 'state' === $sync_mode && $source_blog > 0 && $source_blog !== get_current_blog_id() ) {
            // 状态同步 → 指向主站
            switch_to_blog( $source_blog );
            $url = (string) get_permalink( get_the_ID() );
            restore_current_blog();
            return $url;
        }

        if ( 'state_frame' === $sync_mode && $source_blog > 0 && $source_blog !== get_current_blog_id() ) {
            // 状态帧同步 → 指向当前分站
            return (string) get_permalink();
        }

        if ( 'frame' === $sync_mode ) {
            // 帧同步 → 无跨站 canonical
            return (string) get_permalink();
        }

        return (string) get_permalink();
    }

    if ( is_home() || is_front_page() ) {
        return (string) home_url( '/' );
    }

    if ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();

        if ( $term instanceof WP_Term ) {
            $link = get_term_link( $term );
            return is_wp_error( $link ) ? '' : (string) $link;
        }
    }

    if ( is_author() ) {
        return (string) get_author_posts_url( (int) get_query_var( 'author' ) );
    }

    if ( is_post_type_archive() ) {
        $link = get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
        return $link ? (string) $link : '';
    }

    return '';
}