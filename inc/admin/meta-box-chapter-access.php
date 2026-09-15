<?php
/**
 * 章节访问控制面板
 *
 * 提供章节访问类型、价格、积分、试读等配置。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'add_meta_boxes_slv_chapter', 'slv_admin_chapter_access_register' );
add_action( 'save_post_slv_chapter', 'slv_admin_chapter_access_save', 10, 2 );

/**
 * 注册 meta box
 *
 * @since 1.0.0
 * @return void
 */
function slv_admin_chapter_access_register(): void {
    add_meta_box(
        'slv_chapter_access',
        esc_html__( '访问控制', 'sunlyvo-nexus' ),
        'slv_admin_chapter_access_render',
        'slv_chapter',
        'normal',
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
function slv_admin_chapter_access_render( $post ): void {
    wp_nonce_field( 'slv_chapter_access', 'slv_chapter_access_nonce' );

    $config        = slv_get_chapter_access_config( (int) $post->ID );
    $access_types  = slv_get_access_types();
    $preview_types = slv_get_preview_types();
    ?>
    <div class="slv-access-panel">

        <div class="slv-access-row">
            <label class="slv-access-label">
                <?php esc_html_e( '访问类型', 'sunlyvo-nexus' ); ?>
            </label>
            <div class="slv-access-field">
                <select name="slv_access_type" id="slv_access_type" class="widefat">
                    <?php foreach ( $access_types as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>"
                            <?php selected( $config['type'], $value ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="slv-access-hint">
                    <?php esc_html_e( '决定未购买用户如何访问本内容。', 'sunlyvo-nexus' ); ?>
                </p>
            </div>
        </div>

        <div class="slv-access-row" data-access-for="purchase">
            <label class="slv-access-label">
                <?php esc_html_e( '单章价格', 'sunlyvo-nexus' ); ?>
            </label>
            <div class="slv-access-field">
                <input type="number"
                       name="slv_access_price"
                       class="widefat"
                       step="0.01"
                       min="0"
                       value="<?php echo esc_attr( (string) $config['price'] ); ?>">
                <p class="slv-access-hint">
                    <?php esc_html_e( '仅"需购买"类型生效。留 0 表示免费。', 'sunlyvo-nexus' ); ?>
                </p>
            </div>
        </div>

        <div class="slv-access-row" data-access-for="purchase">
            <label class="slv-access-label">
                <?php esc_html_e( '积分成本', 'sunlyvo-nexus' ); ?>
            </label>
            <div class="slv-access-field">
                <input type="number"
                       name="slv_access_points"
                       class="widefat"
                       step="1"
                       min="0"
                       value="<?php echo esc_attr( (string) $config['points'] ); ?>">
                <p class="slv-access-hint">
                    <?php esc_html_e( '可用积分抵扣的替代方案。留 0 表示不启用。', 'sunlyvo-nexus' ); ?>
                </p>
            </div>
        </div>

        <div class="slv-access-row" data-access-for="level">
            <label class="slv-access-label">
                <?php esc_html_e( '最低会员等级', 'sunlyvo-nexus' ); ?>
            </label>
            <div class="slv-access-field">
                <input type="text"
                       name="slv_access_required_level"
                       class="widefat"
                       value="<?php echo esc_attr( $config['required_level'] ); ?>"
                       placeholder="<?php esc_attr_e( '例：silver / gold', 'sunlyvo-nexus' ); ?>">
                <p class="slv-access-hint">
                    <?php esc_html_e( '仅"需会员等级"类型生效。填写会员等级的 slug。', 'sunlyvo-nexus' ); ?>
                </p>
            </div>
        </div>

        <div class="slv-access-row">
            <label class="slv-access-label">
                <?php esc_html_e( '试读设置', 'sunlyvo-nexus' ); ?>
            </label>
            <div class="slv-access-field slv-access-field--inline">
                <select name="slv_preview_type" class="slv-access-select">
                    <?php foreach ( $preview_types as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>"
                            <?php selected( $config['preview_type'], $value ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number"
                       name="slv_preview_value"
                       class="slv-access-input"
                       min="0"
                       step="1"
                       value="<?php echo esc_attr( (string) $config['preview_value'] ); ?>"
                       placeholder="<?php esc_attr_e( '试读值', 'sunlyvo-nexus' ); ?>">
                <p class="slv-access-hint">
                    <?php esc_html_e( '非公开内容可设置试读。百分比填 1-100，其他填数量。', 'sunlyvo-nexus' ); ?>
                </p>
            </div>
        </div>

        <div class="slv-access-row">
            <label class="slv-access-label">
                <?php esc_html_e( '下载次数上限', 'sunlyvo-nexus' ); ?>
            </label>
            <div class="slv-access-field">
                <input type="number"
                       name="slv_download_limit"
                       class="widefat"
                       min="0"
                       step="1"
                       value="<?php echo esc_attr( (string) $config['download_limit'] ); ?>">
                <p class="slv-access-hint">
                    <?php esc_html_e( '0 为不限次数。', 'sunlyvo-nexus' ); ?>
                </p>
            </div>
        </div>

        <div class="slv-access-row">
            <label class="slv-access-label">
                <?php esc_html_e( '访问过期天数', 'sunlyvo-nexus' ); ?>
            </label>
            <div class="slv-access-field">
                <input type="number"
                       name="slv_expiry_days"
                       class="widefat"
                       min="0"
                       step="1"
                       value="<?php echo esc_attr( (string) $config['expiry_days'] ); ?>">
                <p class="slv-access-hint">
                    <?php esc_html_e( '0 为不过期。', 'sunlyvo-nexus' ); ?>
                </p>
            </div>
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
function slv_admin_chapter_access_save( int $post_id, $post ): void {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['slv_chapter_access_nonce'] ) ) {
        return;
    }

    $nonce = sanitize_text_field( wp_unslash( (string) $_POST['slv_chapter_access_nonce'] ) );

    if ( ! wp_verify_nonce( $nonce, 'slv_chapter_access' ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // 访问类型
    if ( isset( $_POST['slv_access_type'] ) ) {
        $type = sanitize_key( wp_unslash( (string) $_POST['slv_access_type'] ) );

        if ( ! array_key_exists( $type, slv_get_access_types() ) ) {
            $type = 'public';
        }

        update_post_meta( $post_id, '_slv_access_type', $type );
    }

    // 价格
    if ( isset( $_POST['slv_access_price'] ) ) {
        $price = (float) $_POST['slv_access_price'];

        if ( $price > 0 ) {
            update_post_meta( $post_id, '_slv_access_price', $price );
        } else {
            delete_post_meta( $post_id, '_slv_access_price' );
        }
    }

    // 积分
    if ( isset( $_POST['slv_access_points'] ) ) {
        $points = (int) $_POST['slv_access_points'];

        if ( $points > 0 ) {
            update_post_meta( $post_id, '_slv_access_points', $points );
        } else {
            delete_post_meta( $post_id, '_slv_access_points' );
        }
    }

    // 会员等级
    if ( isset( $_POST['slv_access_required_level'] ) ) {
        $level = sanitize_text_field( wp_unslash( (string) $_POST['slv_access_required_level'] ) );

        if ( '' !== $level ) {
            update_post_meta( $post_id, '_slv_access_required_level', $level );
        } else {
            delete_post_meta( $post_id, '_slv_access_required_level' );
        }
    }

    // 试读类型
    if ( isset( $_POST['slv_preview_type'] ) ) {
        $preview_type = sanitize_key( wp_unslash( (string) $_POST['slv_preview_type'] ) );

        if ( ! array_key_exists( $preview_type, slv_get_preview_types() ) ) {
            $preview_type = 'none';
        }

        update_post_meta( $post_id, '_slv_preview_type', $preview_type );
    }

    // 试读值
    if ( isset( $_POST['slv_preview_value'] ) ) {
        $preview_value = (int) $_POST['slv_preview_value'];

        if ( $preview_value > 0 ) {
            update_post_meta( $post_id, '_slv_preview_value', $preview_value );
        } else {
            delete_post_meta( $post_id, '_slv_preview_value' );
        }
    }

    // 下载限制
    if ( isset( $_POST['slv_download_limit'] ) ) {
        $limit = (int) $_POST['slv_download_limit'];

        if ( $limit > 0 ) {
            update_post_meta( $post_id, '_slv_download_limit', $limit );
        } else {
            delete_post_meta( $post_id, '_slv_download_limit' );
        }
    }

    // 过期天数
    if ( isset( $_POST['slv_expiry_days'] ) ) {
        $days = (int) $_POST['slv_expiry_days'];

        if ( $days > 0 ) {
            update_post_meta( $post_id, '_slv_expiry_days', $days );
        } else {
            delete_post_meta( $post_id, '_slv_expiry_days' );
        }
    }
}