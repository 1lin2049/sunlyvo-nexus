<?php
/**
 * 布局 meta box
 *
 * 显示在文章/页面/章节编辑页。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'add_meta_boxes', 'slv_layout_register_meta_box' );
add_action( 'save_post', 'slv_layout_save_meta_box', 10, 2 );

/**
 * 注册 meta box
 *
 * @since 1.0.0
 * @return void
 */
function slv_layout_register_meta_box(): void {
    $types = [ 'post', 'page', 'slv_chapter', 'slv_collection' ];

    foreach ( $types as $type ) {
        add_meta_box(
            'slv_layout_meta',
            esc_html__( '页面布局', 'sunlyvo-nexus' ),
            'slv_layout_render_meta_box',
            $type,
            'side',
            'default'
        );
    }
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
function slv_layout_render_meta_box( $post ): void {
    wp_nonce_field( 'slv_layout_meta', 'slv_layout_meta_nonce' );

    $current        = (string) get_post_meta( $post->ID, '_slv_layout', true );
    $custom_pc      = (int) get_post_meta( $post->ID, '_slv_layout_custom_vw', true );
    $custom_mobile  = (int) get_post_meta( $post->ID, '_slv_layout_custom_vw_mobile', true );

    if ( '' === $current ) {
        $current = 'inherit';
    }
    ?>
    <div class="slv-layout-meta">
        <p class="slv-meta-field">
            <label for="slv_layout" class="slv-meta-field__label">
                <?php esc_html_e( '选择布局', 'sunlyvo-nexus' ); ?>
            </label>
            <select id="slv_layout" name="slv_layout" class="widefat">
                <option value="inherit" <?php selected( $current, 'inherit' ); ?>>
                    <?php esc_html_e( '继承全站默认', 'sunlyvo-nexus' ); ?>
                </option>
                <?php foreach ( SLV_LAYOUT_OPTIONS as $slug => $label ) : ?>
                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <div class="slv-layout-custom" data-visible-when="custom">
            <p class="slv-meta-field">
                <label for="slv_layout_custom_vw" class="slv-meta-field__label">
                    <?php esc_html_e( 'PC 宽度（vw）', 'sunlyvo-nexus' ); ?>
                </label>
                <input type="number"
                       id="slv_layout_custom_vw"
                       name="slv_layout_custom_vw"
                       min="30" max="100" step="1"
                       value="<?php echo esc_attr( $custom_pc > 0 ? (string) $custom_pc : '' ); ?>"
                       placeholder="60"
                       class="widefat">
            </p>

            <p class="slv-meta-field">
                <label for="slv_layout_custom_vw_mobile" class="slv-meta-field__label">
                    <?php esc_html_e( '移动端宽度（vw）', 'sunlyvo-nexus' ); ?>
                </label>
                <input type="number"
                       id="slv_layout_custom_vw_mobile"
                       name="slv_layout_custom_vw_mobile"
                       min="30" max="100" step="1"
                       value="<?php echo esc_attr( $custom_mobile > 0 ? (string) $custom_mobile : '' ); ?>"
                       placeholder="95"
                       class="widefat">
            </p>
        </div>
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
function slv_layout_save_meta_box( int $post_id, $post ): void {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;

    if ( ! isset( $_POST['slv_layout_meta_nonce'] ) ) return;

    $nonce = sanitize_text_field( wp_unslash( (string) $_POST['slv_layout_meta_nonce'] ) );

    if ( ! wp_verify_nonce( $nonce, 'slv_layout_meta' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // 布局
    if ( isset( $_POST['slv_layout'] ) ) {
        $layout = sanitize_key( wp_unslash( (string) $_POST['slv_layout'] ) );

        if ( 'inherit' === $layout ) {
            delete_post_meta( $post_id, '_slv_layout' );
        } elseif ( array_key_exists( $layout, SLV_LAYOUT_OPTIONS ) ) {
            update_post_meta( $post_id, '_slv_layout', $layout );
        }
    }

    // 自定义 vw
    if ( isset( $_POST['slv_layout_custom_vw'] ) ) {
        $vw = (int) $_POST['slv_layout_custom_vw'];

        if ( $vw >= 30 && $vw <= 100 ) {
            update_post_meta( $post_id, '_slv_layout_custom_vw', $vw );
        } else {
            delete_post_meta( $post_id, '_slv_layout_custom_vw' );
        }
    }

    if ( isset( $_POST['slv_layout_custom_vw_mobile'] ) ) {
        $vw = (int) $_POST['slv_layout_custom_vw_mobile'];

        if ( $vw >= 30 && $vw <= 100 ) {
            update_post_meta( $post_id, '_slv_layout_custom_vw_mobile', $vw );
        } else {
            delete_post_meta( $post_id, '_slv_layout_custom_vw_mobile' );
        }
    }
}