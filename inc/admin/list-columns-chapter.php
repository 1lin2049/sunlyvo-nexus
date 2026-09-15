<?php
/**
 * 章节列表后台列
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'manage_slv_chapter_posts_columns', 'slv_admin_chapter_list_columns' );
add_action( 'manage_slv_chapter_posts_custom_column', 'slv_admin_chapter_list_column_content', 10, 2 );
add_filter( 'manage_edit-slv_chapter_sortable_columns', 'slv_admin_chapter_sortable_columns' );
add_action( 'restrict_manage_posts', 'slv_admin_chapter_list_filters' );
add_action( 'pre_get_posts', 'slv_admin_chapter_list_query' );

/**
 * 定义列
 *
 * @since 1.0.0
 *
 * @param array $columns 原列
 *
 * @return array
 */
function slv_admin_chapter_list_columns( array $columns ): array {
    $new = [];

    foreach ( $columns as $key => $label ) {
        if ( 'title' === $key ) {
            $new[ $key ]           = $label;
            $new['slv_collection'] = esc_html__( '所属合集', 'sunlyvo-nexus' );
            $new['slv_number']     = esc_html__( '序号', 'sunlyvo-nexus' );
            $new['slv_words']      = esc_html__( '字数', 'sunlyvo-nexus' );
            $new['slv_time']       = esc_html__( '阅读时间', 'sunlyvo-nexus' );
            $new['slv_access']     = esc_html__( '访问类型', 'sunlyvo-nexus' );
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
function slv_admin_chapter_list_column_content( string $column, int $post_id ): void {
    switch ( $column ) {
        case 'slv_collection':
            $collection_id = slv_get_chapter_collection_id( $post_id );

            if ( $collection_id <= 0 ) {
                echo '<span style="color:#999;">—</span>';
                break;
            }

            $collection = get_post( $collection_id );

            if ( ! $collection ) {
                echo '<span style="color:#999;">—</span>';
                break;
            }

            printf(
                '<a href="%s">%s</a>',
                esc_url( (string) get_edit_post_link( $collection_id ) ),
                esc_html( $collection->post_title )
            );
            break;

        case 'slv_number':
            $number = slv_get_chapter_number( $post_id );
            echo esc_html( '' !== $number ? $number : '—' );
            break;

        case 'slv_words':
            echo esc_html( number_format_i18n( slv_get_chapter_words( $post_id ) ) );
            break;

        case 'slv_time':
            printf(
                /* translators: %d: minutes */
                esc_html__( '%d 分钟', 'sunlyvo-nexus' ),
                (int) slv_get_chapter_time( $post_id )
            );
            break;

        case 'slv_access':
            $config = slv_get_chapter_access_config( $post_id );
            $label  = slv_get_access_type_label( $config['type'] );

            printf(
                '<span class="slv-badge slv-badge--%s">%s</span>',
                esc_attr( $config['type'] ),
                esc_html( $label )
            );
            break;
    }
}

/**
 * 定义可排序列
 *
 * @since 1.0.0
 *
 * @param array $columns 可排序列
 *
 * @return array
 */
function slv_admin_chapter_sortable_columns( array $columns ): array {
    $columns['slv_words']  = 'slv_words';
    $columns['slv_number'] = 'menu_order';

    return $columns;
}

/**
 * 渲染列表筛选器
 *
 * @since 1.0.0
 *
 * @param string $post_type 当前文章类型
 *
 * @return void
 */
function slv_admin_chapter_list_filters( string $post_type ): void {
    if ( 'slv_chapter' !== $post_type ) {
        return;
    }

    $current_collection = isset( $_GET['slv_filter_collection'] )
        ? (int) $_GET['slv_filter_collection']
        : 0;

    $collections = get_posts(
        [
            'post_type'      => 'slv_collection',
            'posts_per_page' => 500,
            'post_status'    => [ 'publish', 'draft', 'private' ],
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]
    );
    ?>
    <select name="slv_filter_collection">
        <option value="0"><?php esc_html_e( '所有合集', 'sunlyvo-nexus' ); ?></option>
        <?php foreach ( $collections as $collection ) : ?>
            <option value="<?php echo esc_attr( (string) $collection->ID ); ?>"
                <?php selected( $current_collection, (int) $collection->ID ); ?>>
                <?php echo esc_html( $collection->post_title ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php

    $current_access = isset( $_GET['slv_filter_access'] )
        ? sanitize_key( wp_unslash( (string) $_GET['slv_filter_access'] ) )
        : '';
    ?>
    <select name="slv_filter_access">
        <option value=""><?php esc_html_e( '所有访问类型', 'sunlyvo-nexus' ); ?></option>
        <?php foreach ( slv_get_access_types() as $value => $label ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>"
                <?php selected( $current_access, $value ); ?>>
                <?php echo esc_html( $label ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * 处理筛选与排序查询
 *
 * @since 1.0.0
 *
 * @param WP_Query $query 查询对象
 *
 * @return void
 */
function slv_admin_chapter_list_query( $query ): void {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

    if ( ! $screen || 'edit-slv_chapter' !== $screen->id ) {
        return;
    }

    $collection_filter = isset( $_GET['slv_filter_collection'] )
        ? (int) $_GET['slv_filter_collection']
        : 0;

    if ( $collection_filter > 0 ) {
        $meta_query   = (array) $query->get( 'meta_query' );
        $meta_query[] = [
            'key'   => '_slv_parent_collection_id',
            'value' => $collection_filter,
            'type'  => 'NUMERIC',
        ];
        $query->set( 'meta_query', $meta_query );
    }

    $access_filter = isset( $_GET['slv_filter_access'] )
        ? sanitize_key( wp_unslash( (string) $_GET['slv_filter_access'] ) )
        : '';

    if ( '' !== $access_filter ) {
        $meta_query   = (array) $query->get( 'meta_query' );
        $meta_query[] = [
            'key'   => '_slv_access_type',
            'value' => $access_filter,
        ];
        $query->set( 'meta_query', $meta_query );
    }

    if ( 'slv_words' === $query->get( 'orderby' ) ) {
        $query->set( 'meta_key', '_slv_chapter_words' );
        $query->set( 'orderby', 'meta_value_num' );
    }
}