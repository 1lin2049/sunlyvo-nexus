<?php
/**
 * 合集编辑页 meta box
 *
 * 字段：
 * - _slv_access_mode  访问模式（full_paid / per_chapter / mixed）
 *
 * 附加：合集内章节只读列表
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'add_meta_boxes_slv_collection', 'slv_admin_collection_register_meta_boxes' );
add_action( 'save_post_slv_collection', 'slv_admin_collection_save_meta_boxes', 10, 2 );

/**
 * 注册 meta box
 *
 * @since 1.0.0
 * @return void
 */
function slv_admin_collection_register_meta_boxes(): void {
    add_meta_box(
        'slv_collection_access',
        esc_html__( '访问模式', 'sunlyvo-nexus' ),
        'slv_admin_collection_access_render',
        'slv_collection',
        'side',
        'high'
    );

    add_meta_box(
        'slv_collection_chapters',
        esc_html__( '合集内章节', 'sunlyvo-nexus' ),
        'slv_admin_collection_chapters_render',
        'slv_collection',
        'normal',
        'high'
    );
}

/**
 * 渲染访问模式 meta box
 *
 * @since 1.0.0
 *
 * @param WP_Post $post 当前文章
 *
 * @return void
 */
function slv_admin_collection_access_render( $post ): void {
    wp_nonce_field( 'slv_collection_meta', 'slv_collection_meta_nonce' );

    $current = slv_get_collection_access_mode( (int) $post->ID );

    $options = [
        'mixed'       => esc_html__( '整包或单篇（推荐）', 'sunlyvo-nexus' ),
        'full_paid'   => esc_html__( '仅整包购买', 'sunlyvo-nexus' ),
        'per_chapter' => esc_html__( '仅单篇购买', 'sunlyvo-nexus' ),
    ];
    ?>
    <div class="slv-meta-field">
        <p class="slv-meta-field__hint">
            <?php esc_html_e( '决定本合集的付费方式。影响前台购买入口。', 'sunlyvo-nexus' ); ?>
        </p>

        <?php foreach ( $options as $value => $label ) : ?>
            <p>
                <label>
                    <input type="radio"
                           name="slv_access_mode"
                           value="<?php echo esc_attr( $value ); ?>"
                        <?php checked( $current, $value ); ?>>
                    <?php echo esc_html( $label ); ?>
                </label>
            </p>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * 渲染合集内章节列表 meta box
 *
 * @since 1.0.0
 *
 * @param WP_Post $post 当前文章
 *
 * @return void
 */
function slv_admin_collection_chapters_render( $post ): void {
    $chapters = slv_get_collection_chapters( (int) $post->ID, 'any' );

    if ( empty( $chapters ) ) {
        ?>
        <p><?php esc_html_e( '本合集还没有章节。请先新增章节，并在章节编辑页选择"所属合集"。', 'sunlyvo-nexus' ); ?></p>
        <?php
        return;
    }

    $edit_base = admin_url( 'post.php?action=edit&post=' );
    ?>
    <p class="slv-meta-field__hint">
        <?php
        printf(
            /* translators: %d: chapter count */
            esc_html__( '共 %d 章。章节顺序由"章节序号"决定。', 'sunlyvo-nexus' ),
            count( $chapters )
        );
        ?>
    </p>

    <table class="widefat striped slv-chapter-table">
        <thead>
            <tr>
                <th style="width:80px;"><?php esc_html_e( '序号', 'sunlyvo-nexus' ); ?></th>
                <th><?php esc_html_e( '标题', 'sunlyvo-nexus' ); ?></th>
                <th style="width:80px;"><?php esc_html_e( '字数', 'sunlyvo-nexus' ); ?></th>
                <th style="width:100px;"><?php esc_html_e( '阅读时间', 'sunlyvo-nexus' ); ?></th>
                <th style="width:80px;"><?php esc_html_e( '访问', 'sunlyvo-nexus' ); ?></th>
                <th style="width:80px;"><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $chapters as $chapter ) : ?>
                <?php
                $cid     = (int) $chapter->ID;
                $number  = slv_get_chapter_number( $cid );
                $words   = slv_get_chapter_words( $cid );
                $time    = slv_get_chapter_time( $cid );
                $is_free = slv_is_chapter_free( $cid );
                ?>
                <tr>
                    <td><?php echo esc_html( '' !== $number ? $number : '—' ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( $edit_base . $cid ); ?>">
                            <?php echo esc_html( $chapter->post_title ); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html( number_format_i18n( $words ) ); ?></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %d: minutes */
                            esc_html__( '%d 分钟', 'sunlyvo-nexus' ),
                            (int) $time
                        );
                        ?>
                    </td>
                    <td>
                        <?php if ( $is_free ) : ?>
                            <span class="slv-badge slv-badge--free"><?php esc_html_e( '免费', 'sunlyvo-nexus' ); ?></span>
                        <?php else : ?>
                            <span class="slv-badge slv-badge--paid"><?php esc_html_e( '付费', 'sunlyvo-nexus' ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $status_obj = get_post_status_object( $chapter->post_status );
                        echo esc_html( $status_obj ? $status_obj->label : $chapter->post_status );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
function slv_admin_collection_save_meta_boxes( int $post_id, $post ): void {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['slv_collection_meta_nonce'] ) ) {
        return;
    }

    $nonce = sanitize_text_field( wp_unslash( (string) $_POST['slv_collection_meta_nonce'] ) );

    if ( ! wp_verify_nonce( $nonce, 'slv_collection_meta' ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['slv_access_mode'] ) ) {
        $mode = sanitize_key( wp_unslash( (string) $_POST['slv_access_mode'] ) );

        if ( ! in_array( $mode, [ 'full_paid', 'per_chapter', 'mixed' ], true ) ) {
            $mode = 'mixed';
        }

        update_post_meta( $post_id, '_slv_access_mode', $mode );
    }
}