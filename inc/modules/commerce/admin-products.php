<?php
/**
 * 后台商品管理
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'slv_commerce_admin_products_menu', 30 );

/**
 * 注册商品菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_products_menu(): void {
    add_submenu_page(
        'slv-orders',
        esc_html__( '商品', 'sunlyvo-nexus' ),
        esc_html__( '商品', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-products',
        'slv_commerce_admin_products_page'
    );
}

/**
 * 商品列表页
 *
 * @since 1.0.0
 * @return void
 */
function slv_commerce_admin_products_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    $type_filter = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( (string) $_GET['type'] ) ) : '';

    $products = slv_product_list(
        [
            'product_type'   => $type_filter,
            'product_status' => '',
            'limit'          => 500,
        ]
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '商品管理', 'sunlyvo-nexus' ); ?></h1>

        <ul class="subsubsub">
            <li>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=slv-products' ) ); ?>"
                   class="<?php echo '' === $type_filter ? 'current' : ''; ?>">
                    <?php esc_html_e( '全部', 'sunlyvo-nexus' ); ?>
                </a> |
            </li>
            <?php
            $types = [
                'digital'      => __( '数字商品', 'sunlyvo-nexus' ),
                'physical'     => __( '物理商品', 'sunlyvo-nexus' ),
                'service'      => __( '服务', 'sunlyvo-nexus' ),
                'subscription' => __( '订阅', 'sunlyvo-nexus' ),
            ];
            $i = 0;
            $total = count( $types );
            foreach ( $types as $key => $label ) :
                $i++;
                ?>
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=slv-products&type=' . $key ) ); ?>"
                       class="<?php echo $type_filter === $key ? 'current' : ''; ?>">
                        <?php echo esc_html( $label ); ?>
                    </a>
                    <?php echo $i < $total ? ' |' : ''; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:60px;"><?php esc_html_e( 'ID', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '标题', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '类型', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                    <th style="text-align:right;"><?php esc_html_e( '价格', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '关联', 'sunlyvo-nexus' ); ?></th>
                    <th><?php esc_html_e( '库存', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $products ) ) : ?>
                    <tr><td colspan="7"><?php esc_html_e( '无商品。', 'sunlyvo-nexus' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $products as $p ) : ?>
                        <tr>
                            <td><?php echo esc_html( (string) $p['id'] ); ?></td>
                            <td><strong><?php echo esc_html( (string) $p['title'] ); ?></strong></td>
                            <td><?php echo esc_html( (string) $p['product_type'] ); ?></td>
                            <td><?php echo esc_html( (string) $p['product_status'] ); ?></td>
                            <td style="text-align:right;">
                                <?php echo esc_html( slv_format_price( (float) $p['price'], (string) $p['currency'] ) ); ?>
                            </td>
                            <td>
                                <?php if ( (int) $p['linked_object_id'] > 0 ) : ?>
                                    <a href="<?php echo esc_url( (string) get_edit_post_link( (int) $p['linked_object_id'] ) ); ?>">
                                        <?php echo esc_html( (string) $p['linked_object_type'] . '#' . (string) $p['linked_object_id'] ); ?>
                                    </a>
                                <?php else : ?>
                                    <span style="color:#999;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if ( $p['manage_stock'] ) {
                                    echo esc_html( (string) $p['stock_quantity'] );
                                } else {
                                    echo '<span style="color:#999;">—</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}