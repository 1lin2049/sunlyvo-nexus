<?php
/**
 * 会员等级后台管理
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'slv_admin_membership_menu' );

/**
 * 注册菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_admin_membership_menu(): void {
    add_submenu_page(
        'options-general.php',
        esc_html__( '会员等级', 'sunlyvo-nexus' ),
        esc_html__( '会员等级', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-membership-levels',
        'slv_admin_membership_levels_page'
    );
}

/**
 * 渲染页面
 *
 * @since 1.0.0
 * @return void
 */
function slv_admin_membership_levels_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    // 处理表单提交
    if ( isset( $_POST['slv_membership_action'] ) ) {
        check_admin_referer( 'slv_membership_levels' );

        $action = sanitize_key( wp_unslash( (string) $_POST['slv_membership_action'] ) );

        if ( 'save' === $action && isset( $_POST['level'] ) && is_array( $_POST['level'] ) ) {
            $level_data = wp_unslash( $_POST['level'] );
            slv_save_member_level( $level_data );
            echo '<div class="notice notice-success"><p>' . esc_html__( '等级已保存。', 'sunlyvo-nexus' ) . '</p></div>';
        } elseif ( 'delete' === $action && isset( $_POST['level_id'] ) ) {
            slv_delete_member_level( (int) $_POST['level_id'] );
            echo '<div class="notice notice-success"><p>' . esc_html__( '等级已删除。', 'sunlyvo-nexus' ) . '</p></div>';
        }
    }

    $levels = slv_get_member_levels( false );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '会员等级', 'sunlyvo-nexus' ); ?></h1>
        <p><?php esc_html_e( '用户通过累计消费或累计积分自动升级。满足任一条件即可。', 'sunlyvo-nexus' ); ?></p>

        <h2><?php esc_html_e( '现有等级', 'sunlyvo-nexus' ); ?></h2>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( '排序', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '名称', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( 'Slug', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '累计消费', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '累计积分', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '折扣 %', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '积分倍率', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '操作', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $levels ) ) : ?>
                    <tr><td colspan="9"><?php esc_html_e( '暂无等级。', 'sunlyvo-nexus' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $levels as $level ) : ?>
                        <tr>
                            <td><?php echo esc_html( (string) $level['level_order'] ); ?></td>
                            <td><?php echo esc_html( (string) $level['level_name'] ); ?></td>
                            <td><code><?php echo esc_html( (string) $level['level_slug'] ); ?></code></td>
                            <td><?php echo esc_html( (string) $level['required_spent'] ); ?></td>
                            <td><?php echo esc_html( (string) $level['required_points'] ); ?></td>
                            <td><?php echo esc_html( (string) $level['discount_rate'] ); ?></td>
                            <td><?php echo esc_html( (string) $level['points_multiplier'] ); ?></td>
                            <td>
                                <?php if ( 1 === (int) $level['is_active'] ) : ?>
                                    <span style="color:#00a658;"><?php esc_html_e( '启用', 'sunlyvo-nexus' ); ?></span>
                                <?php else : ?>
                                    <span style="color:#999;"><?php esc_html_e( '停用', 'sunlyvo-nexus' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('确定删除？');">
                                    <?php wp_nonce_field( 'slv_membership_levels' ); ?>
                                    <input type="hidden" name="slv_membership_action" value="delete">
                                    <input type="hidden" name="level_id" value="<?php echo esc_attr( (string) $level['id'] ); ?>">
                                    <button type="submit" class="button-link" style="color:#a00;">
                                        <?php esc_html_e( '删除', 'sunlyvo-nexus' ); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h2 style="margin-top:32px;"><?php esc_html_e( '新增等级', 'sunlyvo-nexus' ); ?></h2>

        <form method="post">
            <?php wp_nonce_field( 'slv_membership_levels' ); ?>
            <input type="hidden" name="slv_membership_action" value="save">

            <table class="form-table">
                <tr>
                    <th><label for="slv_level_slug"><?php esc_html_e( 'Slug', 'sunlyvo-nexus' ); ?></label></th>
                    <td>
                        <input type="text" id="slv_level_slug" name="level[level_slug]" class="regular-text" required>
                        <p class="description"><?php esc_html_e( '仅小写字母和短横线，例如 diamond。', 'sunlyvo-nexus' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="slv_level_name"><?php esc_html_e( '名称', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="text" id="slv_level_name" name="level[level_name]" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="slv_level_order"><?php esc_html_e( '排序', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_level_order" name="level[level_order]" value="0" min="0"></td>
                </tr>
                <tr>
                    <th><label for="slv_level_spent"><?php esc_html_e( '累计消费', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_level_spent" name="level[required_spent]" value="0" step="0.01" min="0"></td>
                </tr>
                <tr>
                    <th><label for="slv_level_points"><?php esc_html_e( '累计积分', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_level_points" name="level[required_points]" value="0" step="1" min="0"></td>
                </tr>
                <tr>
                    <th><label for="slv_level_discount"><?php esc_html_e( '折扣 %', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_level_discount" name="level[discount_rate]" value="0" step="0.01" min="0" max="100"></td>
                </tr>
                <tr>
                    <th><label for="slv_level_multiplier"><?php esc_html_e( '积分倍率', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_level_multiplier" name="level[points_multiplier]" value="1.00" step="0.01" min="0"></td>
                </tr>
                <tr>
                    <th><label for="slv_level_benefits"><?php esc_html_e( '权益说明', 'sunlyvo-nexus' ); ?></label></th>
                    <td><textarea id="slv_level_benefits" name="level[benefits]" class="large-text" rows="3"></textarea></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="level[is_active]" value="1" checked>
                            <?php esc_html_e( '启用', 'sunlyvo-nexus' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <?php submit_button( esc_html__( '保存等级', 'sunlyvo-nexus' ) ); ?>
        </form>
    </div>
    <?php
}