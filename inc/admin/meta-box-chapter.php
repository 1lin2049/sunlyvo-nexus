<?php
/**
 * 章节编辑页基础 meta box
 *
 * 字段：
 * - _slv_parent_collection_id  所属合集
 * - _slv_chapter_number        章节序号
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'add_meta_boxes_slv_chapter', 'slv_admin_chapter_register_meta_boxes' );
add_action( 'save_post_slv_chapter', 'slv_admin_chapter_save_meta_boxes', 10, 2 );

/**
 * 注册 meta box
 *
 * @since 1.0.0
 * @return void
 */
function slv_admin_chapter_register_meta_boxes(): void {
    add_meta_box(
        'slv_chapter_meta',
        esc_html__( '章节归属', 'sunlyvo-nexus' ),
        'slv_admin_chapter_meta_box_render',
        'slv_chapter',
        'side',
        'high'
    );
}

/**
 * 渲染 meta box
 *
 * @since 1.0.0
 *
 * @param WP_Post $post 当前文章
 *
 * @return void
 */
function slv_admin_chapter_meta_box_render( $post ): void {
    wp_nonce_field( 'slv_chapter_meta', 'slv_chapter_meta_nonce' );

    $parent_id = (int) get_post_meta( $post->ID, '_slv_parent_collection_id', true );
    $number    = (string) get_post_meta( $post->ID, '_slv_chapter_number', true );

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
    <div class="slv-meta-field">
        <label class="slv-meta-field__label" for="slv_parent_collection_id">
            <?php esc_html_e( '所属合集', 'sunlyvo-nexus' ); ?>
        </label>
        <select id="slv_parent_collection_id"
                name="slv_parent_collection_id"
                class="widefat">
            <option value="0"><?php esc_html_e( '— 未归属 —', 'sunlyvo-nexus' ); ?></option>
            <?php foreach ( $collections as $collection ) : ?>
                <option value="<?php echo esc_attr( (string) $collection->ID ); ?>"
                    <?php selected( $parent_id, (int) $collection->ID ); ?>>
                    <?php echo esc_html( $collection->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="slv-meta-field__hint">
            <?php esc_html_e( '章节必须归属某个合集才能在目录中展示。', 'sunlyvo-nexus' ); ?>
        </p>
    </div>

    <div class="slv-meta-field">
        <label class="slv-meta-field__label" for="slv_chapter_number">
            <?php esc_html_e( '章节序号', 'sunlyvo-nexus' ); ?>
        </label>
        <input type="text"
               id="slv_chapter_number"
               name="slv_chapter_number"
               class="widefat"
               value="<?php echo esc_attr( $number ); ?>"
               placeholder="<?php esc_attr_e( '例：00 / 01 / 1.1', 'sunlyvo-nexus' ); ?>">
        <p class="slv-meta-field__hint">
            <?php esc_html_e( '决定章节排序。保存后自动同步到排序权重。', 'sunlyvo-nexus' ); ?>
        </p>
    </div>
    <?php
}

/**
 * 保存 meta box
 *
 * @since 1.0.0
 *
 * @param int     $post_id 文章ID
 * @param WP_Post $post    文章对象
 *
 * @return void
 */
function slv_admin_chapter_save_meta_boxes( int $post_id, $post ): void {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['slv_chapter_meta_nonce'] ) ) {
        return;
    }

    $nonce = sanitize_text_field( wp_unslash( (string) $_POST['slv_chapter_meta_nonce'] ) );

    if ( ! wp_verify_nonce( $nonce, 'slv_chapter_meta' ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['slv_parent_collection_id'] ) ) {
        $parent_id = (int) $_POST['slv_parent_collection_id'];
        $old_id    = (int) get_post_meta( $post_id, '_slv_parent_collection_id', true );

        if ( $parent_id > 0 ) {
            update_post_meta( $post_id, '_slv_parent_collection_id', $parent_id );
        } else {
            delete_post_meta( $post_id, '_slv_parent_collection_id' );
        }

        if ( $old_id !== $parent_id ) {
            if ( $old_id > 0 ) {
                slv_content_recalculate_collection( $old_id );
            }
            if ( $parent_id > 0 ) {
                slv_content_recalculate_collection( $parent_id );
            }
        }
    }

    if ( isset( $_POST['slv_chapter_number'] ) ) {
        $number = sanitize_text_field( wp_unslash( (string) $_POST['slv_chapter_number'] ) );

        if ( '' !== $number ) {
            update_post_meta( $post_id, '_slv_chapter_number', $number );
            slv_content_sync_chapter_order( $post_id, $number );
        } else {
            delete_post_meta( $post_id, '_slv_chapter_number' );
        }
    }
}