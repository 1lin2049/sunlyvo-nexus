<?php
/**
 * 分类法注册
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'slv_register_taxonomies', 6 );

/**
 * 注册所有分类法
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_taxonomies(): void {
    slv_register_chapter_section_taxonomy();
    slv_register_collection_category_taxonomy();

    /**
     * 模块可通过此钩子注册额外分类法
     *
     * @since 1.0.0
     */
    do_action( 'slv_after_register_taxonomies' );
}

/**
 * 注册章节分组分类法
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_chapter_section_taxonomy(): void {
    register_taxonomy(
        'slv_chapter_section',
        [ 'slv_chapter' ],
        [
            'labels'             => [
                'name'                       => esc_html__( '章节分组', 'sunlyvo-nexus' ),
                'singular_name'              => esc_html__( '章节分组', 'sunlyvo-nexus' ),
                'search_items'               => esc_html__( '搜索分组', 'sunlyvo-nexus' ),
                'popular_items'              => esc_html__( '常用分组', 'sunlyvo-nexus' ),
                'all_items'                  => esc_html__( '所有分组', 'sunlyvo-nexus' ),
                'parent_item'                => esc_html__( '父级分组', 'sunlyvo-nexus' ),
                'parent_item_colon'          => esc_html__( '父级分组：', 'sunlyvo-nexus' ),
                'edit_item'                  => esc_html__( '编辑分组', 'sunlyvo-nexus' ),
                'update_item'                => esc_html__( '更新分组', 'sunlyvo-nexus' ),
                'add_new_item'               => esc_html__( '新增分组', 'sunlyvo-nexus' ),
                'new_item_name'              => esc_html__( '新分组名称', 'sunlyvo-nexus' ),
                'separate_items_with_commas' => esc_html__( '分组用逗号分隔', 'sunlyvo-nexus' ),
                'add_or_remove_items'        => esc_html__( '添加或移除分组', 'sunlyvo-nexus' ),
                'choose_from_most_used'      => esc_html__( '从常用分组选择', 'sunlyvo-nexus' ),
                'menu_name'                  => esc_html__( '章节分组', 'sunlyvo-nexus' ),
            ],
            'hierarchical'       => true,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'rest_base'          => 'slv_chapter_sections',
            'show_admin_column'  => true,
            'show_in_quick_edit' => true,
            'rewrite'            => [
                'slug'       => 'chapter-section',
                'with_front' => false,
            ],
        ]
    );
}

/**
 * 注册合集分类法
 *
 * @since 1.0.0
 * @return void
 */
function slv_register_collection_category_taxonomy(): void {
    register_taxonomy(
        'slv_collection_category',
        [ 'slv_collection' ],
        [
            'labels'             => [
                'name'          => esc_html__( '合集分类', 'sunlyvo-nexus' ),
                'singular_name' => esc_html__( '合集分类', 'sunlyvo-nexus' ),
                'search_items'  => esc_html__( '搜索分类', 'sunlyvo-nexus' ),
                'all_items'     => esc_html__( '所有分类', 'sunlyvo-nexus' ),
                'parent_item'   => esc_html__( '父级分类', 'sunlyvo-nexus' ),
                'edit_item'     => esc_html__( '编辑分类', 'sunlyvo-nexus' ),
                'update_item'   => esc_html__( '更新分类', 'sunlyvo-nexus' ),
                'add_new_item'  => esc_html__( '新增分类', 'sunlyvo-nexus' ),
                'new_item_name' => esc_html__( '新分类名称', 'sunlyvo-nexus' ),
                'menu_name'     => esc_html__( '合集分类', 'sunlyvo-nexus' ),
            ],
            'hierarchical'       => true,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'rest_base'          => 'slv_collection_categories',
            'show_admin_column'  => true,
            'show_in_quick_edit' => true,
            'rewrite'            => [
                'slug'       => 'collection-category',
                'with_front' => false,
            ],
        ]
    );
}