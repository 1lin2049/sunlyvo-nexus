<?php
/**
 * 合集列表后台列
 *
 * 显示：章节数、总字数、免费章节数、访问模式
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'manage_slv_collection_posts_columns', 'slv_admin_collection_list_columns' );
add_action( 'manage_slv_collection_posts_custom_column', 'slv_admin_collection_list_column_content', 10, 2 );

/**
 * 定义列
 *
 * @since 1.0.0
 *
 * @param array $columns 原列
 *
 * @return array
 */
function slv_admin_collection_list_columns( array $columns ): array {
    $new = [];

    foreach ( $columns as $key => $label ) {
        if ( 'title' === $key ) {
            $new[ $key ] = $label;
            $new['slv_chapters']  = esc_html__( '章节数', 'sunlyvo-nexus' );
            $new['slv_words']     = esc_html__( '总字数', 'sunlyvo-nexus' );
            $new['slv_free']      = esc_html__( '免费章节', 'sunlyvo-nexus' );
            $new['slv_time']      = esc_html__( '总时长', 'sunlyvo-nexus' );
            $new['slv_mode']      = esc_html__( '访问模式', 'sunlyvo-nexus' );
        } elseif ( 'date' === $key ) {
            $new[ $key ] = $label;
        } else {
            $new[ $key ] = $label;
        }
    }

    return $new;
}

/**
 * 渲染列内容
 *
 * @since 1.0.0
 *
 * @param string $column  列名
 * @param int    $post_id 文章ID
 *
 * @return void
 */
function slv_admin_collection_list_column_content( string $column, int $post_id ): void {
    switch ( $column ) {
        case 'slv_chapters':
            echo esc_html( number_format_i18n( slv_get_collection_count( $post_id ) ) );
            break;

        case 'slv_words':
            echo esc_html( number_format_i18n( slv_get_collection_words( $post_id ) ) );
            break;

        case 'slv_free':
            $free  = slv_count_free_chapters( $post_id );
            $total = slv_get_collection_count( $post_id );

            printf(
                /* translators: 1: free chapters 2: total chapters */
                esc_html__( '%1$d / %2$d', 'sunlyvo-nexus' ),
                (int) $free,
                (int) $total
            );
            break;

        case 'slv_time':
            printf(
                /* translators: %d: minutes */
                esc_html__( '%d 分钟', 'sunlyvo-nexus' ),
                (int) slv_get_collection_time( $post_id )
            );
            break;

        case 'slv_mode':
            $mode = slv_get_collection_access_mode( $post_id );
            echo esc_html( slv_get_access_mode_label( $mode ) );
            break;
    }
}