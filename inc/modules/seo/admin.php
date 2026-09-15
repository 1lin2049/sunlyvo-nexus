<?php
/**
 * SEO 后台管理
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/config-defaults.php';
require_once __DIR__ . '/admin-tabs.php';
require_once __DIR__ . '/admin-preview.php';

add_action( 'admin_menu', 'slv_seo_admin_menu' );
add_action( 'admin_post_slv_seo_save', 'slv_seo_admin_handle_save' );

/**
 * 注册后台菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_admin_menu(): void {
    // 确保有顶级菜单
    if ( ! slv_seo_admin_has_parent_menu() ) {
        add_menu_page(
            esc_html__( 'SunLyvo', 'sunlyvo-nexus' ),
            esc_html__( 'SunLyvo', 'sunlyvo-nexus' ),
            'manage_options',
            'slv-dashboard',
            '__return_null',
            'dashicons-superhero-alt',
            25
        );
    }

    add_submenu_page(
        'slv-dashboard',
        esc_html__( 'SEO / GEO / AEO', 'sunlyvo-nexus' ),
        esc_html__( 'SEO / GEO / AEO', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-seo',
        'slv_seo_admin_page'
    );
}

/**
 * 检查是否已有 SunLyvo 父菜单
 *
 * @since 1.0.0
 * @return bool
 */
function slv_seo_admin_has_parent_menu(): bool {
    global $admin_page_hooks;

    return isset( $admin_page_hooks['slv-dashboard'] ) || isset( $admin_page_hooks['slv-orders'] );
}

/**
 * 渲染 SEO 页面
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_admin_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    // 首次访问时，确保默认配置存在
    slv_seo_seed_config_defaults();

    $tabs        = slv_seo_get_tabs();
    $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : 'basic';

    if ( ! isset( $tabs[ $current_tab ] ) ) {
        $current_tab = 'basic';
    }

    $saved = isset( $_GET['saved'] ) && '1' === $_GET['saved'];
    ?>
    <div class="wrap slv-seo-wrap">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-search"></span>
            <?php esc_html_e( 'SEO / GEO / AEO', 'sunlyvo-nexus' ); ?>
        </h1>
        <hr class="wp-header-end">

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( '设置已保存。', 'sunlyvo-nexus' ); ?></p>
            </div>
        <?php endif; ?>

        <div class="slv-seo-intro">
            <p>
                <?php esc_html_e( '统一管理搜索引擎优化（SEO）、答案引擎优化（AEO）和生成式引擎优化（GEO）。所有设置立即生效，无需修改代码。', 'sunlyvo-nexus' ); ?>
            </p>
        </div>

        <nav class="nav-tab-wrapper slv-seo-tabs">
            <?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=slv-seo&tab=' . $tab_key ) ); ?>"
                   class="nav-tab <?php echo $tab_key === $current_tab ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html( $tab_label ); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="slv-seo-content">
            <?php
            if ( 'diag' === $current_tab ) {
                slv_seo_render_diagnostics_tab();
            } else {
                slv_seo_render_config_form( $current_tab );
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * 渲染配置表单
 *
 * @since 1.0.0
 *
 * @param string $tab Tab 键
 *
 * @return void
 */
function slv_seo_render_config_form( string $tab ): void {
    $by_tab = slv_seo_get_config_by_tab();
    $fields = $by_tab[ $tab ] ?? [];

    if ( empty( $fields ) ) {
        echo '<p>' . esc_html__( '此分类下暂无配置。', 'sunlyvo-nexus' ) . '</p>';
        return;
    }

    ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'slv_seo_save_' . $tab, 'slv_seo_nonce' ); ?>
        <input type="hidden" name="action" value="slv_seo_save">
        <input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">

        <table class="form-table slv-seo-form-table">
            <tbody>
            <?php foreach ( $fields as $field ) : ?>
                <tr>
                    <th scope="row">
                        <label for="<?php echo esc_attr( slv_seo_field_id( $field['key'] ) ); ?>">
                            <?php echo esc_html( (string) $field['label'] ); ?>
                        </label>
                    </th>
                    <td>
                        <?php slv_seo_render_field( $field ); ?>
                        <?php if ( ! empty( $field['desc'] ) ) : ?>
                            <p class="description"><?php echo esc_html( (string) $field['desc'] ); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button( esc_html__( '保存设置', 'sunlyvo-nexus' ) ); ?>
    </form>
    <?php
}

/**
 * 渲染单个字段
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $field 字段定义
 *
 * @return void
 */
function slv_seo_render_field( array $field ): void {
    $key     = (string) $field['key'];
    $type    = (string) $field['type'];
    $default = (string) ( $field['default'] ?? '' );
    $value   = slv_seo_get_config( $key, $default );
    $id      = slv_seo_field_id( $key );
    $name    = 'slv_seo[' . $key . ']';

    switch ( $type ) {
        case 'text':
            printf(
                '<input type="text" id="%s" name="%s" value="%s" class="regular-text">',
                esc_attr( $id ),
                esc_attr( $name ),
                esc_attr( (string) $value )
            );
            break;

        case 'number':
            printf(
                '<input type="number" id="%s" name="%s" value="%s" class="small-text">',
                esc_attr( $id ),
                esc_attr( $name ),
                esc_attr( (string) $value )
            );
            break;

        case 'textarea':
            printf(
                '<textarea id="%s" name="%s" rows="10" class="large-text code">%s</textarea>',
                esc_attr( $id ),
                esc_attr( $name ),
                esc_textarea( (string) $value )
            );
            break;

        case 'checkbox':
            $checked = '1' === (string) $value || 'true' === (string) $value;
            printf(
                '<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
                esc_attr( $id ),
                esc_attr( $name ),
                checked( $checked, true, false ),
                esc_html__( '启用', 'sunlyvo-nexus' )
            );
            break;

        case 'select':
            $options = (array) ( $field['options'] ?? [] );
            printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );

            foreach ( $options as $option_value => $option_label ) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( (string) $option_value ),
                    selected( (string) $value, (string) $option_value, false ),
                    esc_html( (string) $option_label )
                );
            }

            echo '</select>';
            break;

        case 'image':
            $url = (string) $value;
            ?>
            <div class="slv-seo-image-field">
                <input type="hidden"
                       id="<?php echo esc_attr( $id ); ?>"
                       name="<?php echo esc_attr( $name ); ?>"
                       value="<?php echo esc_attr( $url ); ?>"
                       class="slv-seo-image-url">

                <div class="slv-seo-image-preview">
                    <?php if ( '' !== $url ) : ?>
                        <img src="<?php echo esc_url( $url ); ?>" alt="">
                    <?php endif; ?>
                </div>

                <button type="button"
                        class="button slv-seo-image-select"
                        data-target="<?php echo esc_attr( $id ); ?>">
                    <?php esc_html_e( '选择图片', 'sunlyvo-nexus' ); ?>
                </button>

                <button type="button"
                        class="button slv-seo-image-remove"
                        data-target="<?php echo esc_attr( $id ); ?>">
                    <?php esc_html_e( '移除', 'sunlyvo-nexus' ); ?>
                </button>
            </div>
            <?php
            break;

        default:
            printf(
                '<input type="text" id="%s" name="%s" value="%s" class="regular-text">',
                esc_attr( $id ),
                esc_attr( $name ),
                esc_attr( (string) $value )
            );
    }
}

/**
 * 字段 ID 生成
 *
 * @since 1.0.0
 *
 * @param string $key 配置键
 *
 * @return string
 */
function slv_seo_field_id( string $key ): string {
    return 'slv_seo_' . str_replace( [ '.', '-' ], '_', $key );
}

/**
 * 保存处理
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_admin_handle_save(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    $tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( (string) $_POST['tab'] ) ) : 'basic';

    if ( ! isset( $_POST['slv_seo_nonce'] ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=slv-seo&tab=' . $tab ) );
        exit;
    }

    $nonce = sanitize_text_field( wp_unslash( (string) $_POST['slv_seo_nonce'] ) );

    if ( ! wp_verify_nonce( $nonce, 'slv_seo_save_' . $tab ) ) {
        wp_die( esc_html__( '会话已失效，请刷新页面重试。', 'sunlyvo-nexus' ) );
    }

    $submitted = isset( $_POST['slv_seo'] ) && is_array( $_POST['slv_seo'] )
        ? wp_unslash( $_POST['slv_seo'] )
        : [];

    $by_tab = slv_seo_get_config_by_tab();
    $fields = $by_tab[ $tab ] ?? [];

    foreach ( $fields as $field ) {
        $key      = (string) $field['key'];
        $type     = (string) $field['type'];
        $default  = (string) ( $field['default'] ?? '' );

        // checkbox 未提交时表示未勾选
        if ( 'checkbox' === $type ) {
            $value = isset( $submitted[ $key ] ) && '1' === (string) $submitted[ $key ] ? '1' : '0';
        } else {
            $raw = $submitted[ $key ] ?? '';

            if ( 'textarea' === $type ) {
                $value = sanitize_textarea_field( (string) $raw );
            } elseif ( 'image' === $type ) {
                $value = esc_url_raw( (string) $raw );
            } elseif ( 'number' === $type ) {
                $value = (string) (int) $raw;
            } else {
                $value = sanitize_text_field( (string) $raw );
            }
        }

        slv_set_config( $key, $value, 'platform', 0 );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=slv-seo&tab=' . $tab . '&saved=1' ) );
    exit;
}