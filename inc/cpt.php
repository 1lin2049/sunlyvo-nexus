<?php
/**
 * 自定义内容类型注册
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'slv_register_post_types', 5 );

/**
 * 注册所有内容类型
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_post_types(): void {
    slv_register_collection_cpt();
    slv_register_chapter_cpt();

    do_action( 'slv_after_register_post_types' );
}

/**
 * 注册合集 CPT
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_collection_cpt(): void {
    $labels = [
        'name'                  => esc_html__( '合集', 'sunlyvo-nexus' ),
        'singular_name'         => esc_html__( '合集', 'sunlyvo-nexus' ),
        'menu_name'             => esc_html__( '合集', 'sunlyvo-nexus' ),
        'add_new'               => esc_html__( '新增合集', 'sunlyvo-nexus' ),
        'add_new_item'          => esc_html__( '新增合集', 'sunlyvo-nexus' ),
        'edit_item'             => esc_html__( '编辑合集', 'sunlyvo-nexus' ),
        'new_item'              => esc_html__( '新合集', 'sunlyvo-nexus' ),
        'view_item'             => esc_html__( '查看合集', 'sunlyvo-nexus' ),
        'view_items'            => esc_html__( '查看合集列表', 'sunlyvo-nexus' ),
        'search_items'          => esc_html__( '搜索合集', 'sunlyvo-nexus' ),
        'not_found'             => esc_html__( '未找到合集', 'sunlyvo-nexus' ),
        'not_found_in_trash'    => esc_html__( '回收站中没有合集', 'sunlyvo-nexus' ),
        'all_items'             => esc_html__( '所有合集', 'sunlyvo-nexus' ),
        'archives'              => esc_html__( '合集归档', 'sunlyvo-nexus' ),
        'featured_image'        => esc_html__( '合集封面', 'sunlyvo-nexus' ),
        'set_featured_image'    => esc_html__( '设置合集封面', 'sunlyvo-nexus' ),
        'remove_featured_image' => esc_html__( '移除合集封面', 'sunlyvo-nexus' ),
        'items_list'            => esc_html__( '合集列表', 'sunlyvo-nexus' ),
        'items_list_navigation' => esc_html__( '合集列表导航', 'sunlyvo-nexus' ),
    ];

    register_post_type(
        'slv_collection',
        [
            'labels'              => $labels,
            'description'         => esc_html__( '知识产品容器，支持整包购买与单篇购买', 'sunlyvo-nexus' ),
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'show_in_rest'        => true,
            'rest_base'           => 'slv_collections',
            'has_archive'         => 'collections',
            'hierarchical'        => false,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-portfolio',
            'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ],
            'taxonomies'          => [ 'slv_collection_category' ],
            'rewrite'             => [
                'slug'       => 'collections',
                'with_front' => false,
                'feeds'      => true,
                'pages'      => true,
            ],
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'delete_with_user'    => false,
        ]
    );
}

/**
 * 注册章节 CPT
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_chapter_cpt(): void {
    $labels = [
        'name'                  => esc_html__( '章节', 'sunlyvo-nexus' ),
        'singular_name'         => esc_html__( '章节', 'sunlyvo-nexus' ),
        'menu_name'             => esc_html__( '章节', 'sunlyvo-nexus' ),
        'add_new'               => esc_html__( '新增章节', 'sunlyvo-nexus' ),
        'add_new_item'          => esc_html__( '新增章节', 'sunlyvo-nexus' ),
        'edit_item'             => esc_html__( '编辑章节', 'sunlyvo-nexus' ),
        'new_item'              => esc_html__( '新章节', 'sunlyvo-nexus' ),
        'view_item'             => esc_html__( '查看章节', 'sunlyvo-nexus' ),
        'view_items'            => esc_html__( '查看章节列表', 'sunlyvo-nexus' ),
        'search_items'          => esc_html__( '搜索章节', 'sunlyvo-nexus' ),
        'not_found'             => esc_html__( '未找到章节', 'sunlyvo-nexus' ),
        'not_found_in_trash'    => esc_html__( '回收站中没有章节', 'sunlyvo-nexus' ),
        'all_items'             => esc_html__( '所有章节', 'sunlyvo-nexus' ),
        'archives'              => esc_html__( '章节归档', 'sunlyvo-nexus' ),
        'featured_image'        => esc_html__( '章节封面', 'sunlyvo-nexus' ),
        'set_featured_image'    => esc_html__( '设置章节封面', 'sunlyvo-nexus' ),
        'remove_featured_image' => esc_html__( '移除章节封面', 'sunlyvo-nexus' ),
        'items_list'            => esc_html__( '章节列表', 'sunlyvo-nexus' ),
        'items_list_navigation' => esc_html__( '章节列表导航', 'sunlyvo-nexus' ),
    ];

    register_post_type(
        'slv_chapter',
        [
            'labels'              => $labels,
            'description'         => esc_html__( '合集内的单篇内容单元，支持独立售卖', 'sunlyvo-nexus' ),
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => true,
            'show_in_rest'        => true,
            'rest_base'           => 'slv_chapters',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => 21,
            'menu_icon'           => 'dashicons-media-text',
            'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'comments', 'page-attributes' ],
            'taxonomies'          => [ 'slv_chapter_section' ],
            'rewrite'             => [
                'slug'       => 'preview',
                'with_front' => false,
                'feeds'      => false,
                'pages'      => true,
            ],
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'delete_with_user'    => false,
        ]
    );
}

/**
 * 获取合集关联的章节
 *
 * @since 1.0.0
 *
 * @param int    $collection_id 合集ID
 * @param string $status        'publish' 或 'any'
 *
 * @return WP_Post[]
 */
function slv_get_collection_chapters( int $collection_id, string $status = 'publish' ): array {
    if ( $collection_id <= 0 ) {
        return [];
    }

    $post_status = 'any' === $status
        ? [ 'publish', 'draft', 'private', 'pending' ]
        : $status;

    $query = new WP_Query(
        [
            'post_type'      => 'slv_chapter',
            'posts_per_page' => 500,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'post_status'    => $post_status,
            'meta_query'     => [
                [
                    'key'   => '_slv_parent_collection_id',
                    'value' => $collection_id,
                    'type'  => 'NUMERIC',
                ],
            ],
            'no_found_rows'  => true,
        ]
    );

    return $query->posts;
}

/**
 * 获取章节所属合集 ID
 *
 * @since 1.0.0
 *
 * @param int $chapter_id 章节ID
 *
 * @return int
 */
function slv_get_chapter_collection_id( int $chapter_id ): int {
    return (int) get_post_meta( $chapter_id, '_slv_parent_collection_id', true );
}

/**
 * 判断章节是否属于某合集
 *
 * @since 1.0.0
 *
 * @param int $chapter_id    章节ID
 * @param int $collection_id 合集ID
 *
 * @return bool
 */
function slv_chapter_belongs_to_collection( int $chapter_id, int $collection_id ): bool {
    return slv_get_chapter_collection_id( $chapter_id ) === $collection_id;
}