<?php
/**
 * 布局设置后台页
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'slv_layout_settings_menu', 30 );

/**
 * 注册菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_layout_settings_menu(): void {
    add_submenu_page(
        'slv-dashboard',
        esc_html__( '布局设置', 'sunlyvo-nexus' ),
        esc_html__( '布局设置', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-layout-settings',
        'slv_layout_settings_page'
    );
}

/**
 * 渲染页面
 *
 * @since 1.0.0
 * @return void
 */
function slv_layout_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    if ( isset( $_POST['slv_layout_settings_action'] ) ) {
        check_admin_referer( 'slv_layout_settings' );

        $default = isset( $_POST['site_default'] ) ? sanitize_key( wp_unslash( (string) $_POST['site_default'] ) ) : SLV_LAYOUT_DEFAULT;
        $vw_pc   = isset( $_POST['custom_vw_pc'] ) ? (int) $_POST['custom_vw_pc'] : 60;
        $vw_mob  = isset( $_POST['custom_vw_mobile'] ) ? (int) $_POST['custom_vw_mobile'] : 95;

        if ( array_key_exists( $default, SLV_LAYOUT_OPTIONS ) ) {
            slv_set_config( 'layout.site_default', $default, 'platform', 0 );
        }
        slv_set_config( 'layout.custom_vw_pc', max( 30, min( 100, $vw_pc ) ), 'platform', 0 );
        slv_set_config( 'layout.custom_vw_mobile', max( 30, min( 100, $vw_mob ) ), 'platform', 0 );

        echo '<div class="notice notice-success"><p>' . esc_html__( '布局设置已保存。', 'sunlyvo-nexus' ) . '</p></div>';
    }

    $site_default = (string) slv_get_config( 'layout.site_default', SLV_LAYOUT_DEFAULT );
    $vw_pc        = (int) slv_get_config( 'layout.custom_vw_pc', 60 );
    $vw_mobile    = (int) slv_get_config( 'layout.custom_vw_mobile', 95 );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '布局设置', 'sunlyvo-nexus' ); ?></h1>
        <p class="description"><?php esc_html_e( '全站默认布局。单篇可在编辑页覆盖。', 'sunlyvo-nexus' ); ?></p>

        <form method="post">
            <?php wp_nonce_field( 'slv_layout_settings' ); ?>
            <input type="hidden" name="slv_layout_settings_action" value="save">

            <table class="form-table">
                <tr>
                    <th><label for="site_default"><?php esc_html_e( '全站默认布局', 'sunlyvo-nexus' ); ?></label></th>
                    <td>
                        <select id="site_default" name="site_default">
                            <?php foreach ( SLV_LAYOUT_OPTIONS as $slug => $label ) : ?>
                                <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $site_default, $slug ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr data-layout-row="custom">
                    <th><label for="custom_vw_pc"><?php esc_html_e( '自定义宽度 · PC (vw)', 'sunlyvo-nexus' ); ?></label></th>
                    <td>
                        <input type="number" id="custom_vw_pc" name="custom_vw_pc" min="30" max="100" value="<?php echo esc_attr( (string) $vw_pc ); ?>">
                        <p class="description"><?php esc_html_e( '仅"自定义宽度"布局生效。默认 60vw。', 'sunlyvo-nexus' ); ?></p>
                    </td>
                </tr>
                <tr data-layout-row="custom">
                    <th><label for="custom_vw_mobile"><?php esc_html_e( '自定义宽度 · 移动端 (vw)', 'sunlyvo-nexus' ); ?></label></th>
                    <td>
                        <input type="number" id="custom_vw_mobile" name="custom_vw_mobile" min="30" max="100" value="<?php echo esc_attr( (string) $vw_mobile ); ?>">
                        <p class="description"><?php esc_html_e( '默认 95vw。', 'sunlyvo-nexus' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( esc_html__( '保存布局设置', 'sunlyvo-nexus' ) ); ?>
        </form>
    </div>

    <script>
        (function () {
            'use strict';

            var select = document.getElementById('site_default');
            if (!select) return;

            var rows = document.querySelectorAll('[data-layout-row="custom"]');

            function apply() {
                var show = select.value === 'custom';
                rows.forEach(function (row) {
                    row.style.display = show ? '' : 'none';
                });
            }

            select.addEventListener('change', apply);
            apply();
        })();
    </script>
    <?php
}