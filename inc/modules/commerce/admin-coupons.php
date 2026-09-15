<?php
/**
 * 后台优惠券管理
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'slv_commerce_admin_coupons_menu', 30 );

/**
 * 注册优惠券菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_coupons_menu(): void {
    add_submenu_page(
        'slv-orders',
        esc_html__( '优惠券', 'sunlyvo-nexus' ),
        esc_html__( '优惠券', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-coupons',
        'slv_commerce_admin_coupons_page'
    );
}

/**
 * 优惠券管理页
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_coupons_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    // 处理提交
    if ( isset( $_POST['slv_coupon_action'] ) ) {
        check_admin_referer( 'slv_coupon_manage' );

        $action = sanitize_key( wp_unslash( (string) $_POST['slv_coupon_action'] ) );

        if ( 'create' === $action ) {
            $coupon_id = slv_coupon_create(
                [
                    'code'              => isset( $_POST['code'] ) ? (string) $_POST['code'] : '',
                    'description'       => isset( $_POST['description'] ) ? (string) $_POST['description'] : '',
                    'discount_type'     => isset( $_POST['discount_type'] ) ? (string) $_POST['discount_type'] : 'percent',
                    'discount_value'    => isset( $_POST['discount_value'] ) ? (float) $_POST['discount_value'] : 0,
                    'min_order_amount'  => isset( $_POST['min_order_amount'] ) ? (float) $_POST['min_order_amount'] : 0,
                    'max_uses'          => isset( $_POST['max_uses'] ) ? (int) $_POST['max_uses'] : 0,
                    'max_uses_per_user' => isset( $_POST['max_uses_per_user'] ) ? (int) $_POST['max_uses_per_user'] : 0,
                    'expires_at'        => isset( $_POST['expires_at'] ) && '' !== $_POST['expires_at'] ? (string) $_POST['expires_at'] : null,
                ]
            );

            if ( $coupon_id > 0 ) {
                echo '<div class="notice notice-success"><p>' . esc_html__( '优惠券已创建。', 'sunlyvo-nexus' ) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__( '创建失败，可能优惠码已存在。', 'sunlyvo-nexus' ) . '</p></div>';
            }
        }
    }

    $coupons = slv_coupon_list( [ 'limit' => 100 ] );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '优惠券管理', 'sunlyvo-nexus' ); ?></h1>

        <h2><?php esc_html_e( '现有优惠券', 'sunlyvo-nexus' ); ?></h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( '优惠码', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '类型', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '值', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '已用', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '到期', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $coupons ) ) : ?>
                    <tr><td colspan="6"><?php esc_html_e( '暂无优惠券。', 'sunlyvo-nexus' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $coupons as $coupon ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( (string) $coupon['code'] ); ?></code></td>
                            <td><?php echo 'percent' === $coupon['discount_type'] ? esc_html__( '百分比', 'sunlyvo-nexus' ) : esc_html__( '固定额', 'sunlyvo-nexus' ); ?></td>
                            <td>
                                <?php
                                echo 'percent' === $coupon['discount_type']
                                    ? esc_html( (string) $coupon['discount_value'] . '%' )
                                    : esc_html( slv_format_price( (float) $coupon['discount_value'] ) );
                                ?>
                            </td>
                            <td><?php echo esc_html( (string) $coupon['used_count'] . ' / ' . ( $coupon['max_uses'] > 0 ? (string) $coupon['max_uses'] : '∞' ) ); ?></td>
                            <td>
                                <?php if ( $coupon['is_active'] ) : ?>
                                    <span style="color:#00a658;"><?php esc_html_e( '启用', 'sunlyvo-nexus' ); ?></span>
                                <?php else : ?>
                                    <span style="color:#999;"><?php esc_html_e( '停用', 'sunlyvo-nexus' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( (string) ( $coupon['expires_at'] ?? '—' ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h2 style="margin-top:32px;"><?php esc_html_e( '创建优惠券', 'sunlyvo-nexus' ); ?></h2>

        <form method="post">
            <?php wp_nonce_field( 'slv_coupon_manage' ); ?>
            <input type="hidden" name="slv_coupon_action" value="create">

            <table class="form-table">
                <tr>
                    <th><label for="slv_coupon_code"><?php esc_html_e( '优惠码', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="text" id="slv_coupon_code" name="code" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="slv_coupon_desc"><?php esc_html_e( '说明', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="text" id="slv_coupon_desc" name="description" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="slv_coupon_type"><?php esc_html_e( '类型', 'sunlyvo-nexus' ); ?></label></th>
                    <td>
                        <select id="slv_coupon_type" name="discount_type">
                            <option value="percent"><?php esc_html_e( '百分比（%）', 'sunlyvo-nexus' ); ?></option>
                            <option value="fixed"><?php esc_html_e( '固定金额', 'sunlyvo-nexus' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="slv_coupon_value"><?php esc_html_e( '值', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_coupon_value" name="discount_value" step="0.01" min="0" required></td>
                </tr>
                <tr>
                    <th><label for="slv_coupon_min"><?php esc_html_e( '最低消费', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_coupon_min" name="min_order_amount" step="0.01" min="0" value="0"></td>
                </tr>
                <tr>
                    <th><label for="slv_coupon_max_uses"><?php esc_html_e( '总使用上限', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_coupon_max_uses" name="max_uses" min="0" value="0"> <small><?php esc_html_e( '0 为不限', 'sunlyvo-nexus' ); ?></small></td>
                </tr>
                <tr>
                    <th><label for="slv_coupon_per_user"><?php esc_html_e( '每人上限', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="number" id="slv_coupon_per_user" name="max_uses_per_user" min="0" value="0"> <small><?php esc_html_e( '0 为不限', 'sunlyvo-nexus' ); ?></small></td>
                </tr>
                <tr>
                    <th><label for="slv_coupon_expires"><?php esc_html_e( '到期时间', 'sunlyvo-nexus' ); ?></label></th>
                    <td><input type="datetime-local" id="slv_coupon_expires" name="expires_at"> <small><?php esc_html_e( '留空为永不过期', 'sunlyvo-nexus' ); ?></small></td>
                </tr>
            </table>

            <?php submit_button( esc_html__( '创建优惠券', 'sunlyvo-nexus' ) ); ?>
        </form>
    </div>
    <?php
}