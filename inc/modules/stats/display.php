<?php
/**
 * 统计展示
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取前台 meta 行 HTML
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return string
 */
function slv_stats_meta_line( int $post_id ): string {
    $stats = slv_stats_get( $post_id );

    $parts = [];

    if ( $stats['views'] > 0 ) {
        $parts[] = sprintf(
            /* translators: %s: count */
            esc_html__( '浏览 %s', 'sunlyvo-nexus' ),
            esc_html( number_format_i18n( $stats['views'] ) )
        );
    }

    if ( $stats['reads'] > 0 ) {
        $parts[] = sprintf(
            /* translators: %s: count */
            esc_html__( '深度阅读 %s', 'sunlyvo-nexus' ),
            esc_html( number_format_i18n( $stats['reads'] ) )
        );
    }

    $words = function_exists( 'slv_get_chapter_words' )
        ? slv_get_chapter_words( $post_id )
        : str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );

    if ( $words > 0 ) {
        $parts[] = sprintf(
            /* translators: %s: words */
            esc_html__( '约 %s 字', 'sunlyvo-nexus' ),
            esc_html( number_format_i18n( $words ) )
        );
    }

    $time = function_exists( 'slv_get_chapter_time' ) ? slv_get_chapter_time( $post_id ) : 0;

    if ( $time > 0 ) {
        $parts[] = sprintf(
            /* translators: %d: minutes */
            esc_html__( '预计阅读 %d 分钟', 'sunlyvo-nexus' ),
            (int) $time
        );
    }

    $comments = (int) get_comments_number( $post_id );

    if ( $comments > 0 ) {
        $parts[] = sprintf(
            /* translators: %s: count */
            esc_html__( '评论 %s', 'sunlyvo-nexus' ),
            esc_html( number_format_i18n( $comments ) )
        );
    }

    return implode( ' · ', $parts );
}