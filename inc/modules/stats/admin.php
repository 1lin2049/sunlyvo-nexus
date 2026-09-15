<?php
/**
 * 统计后台
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// === 文章列表列 ===
add_filter( 'manage_posts_columns', 'slv_stats_admin_columns' );
add_filter( 'manage_pages_columns', 'slv_stats_admin_columns' );
add_action( 'manage_posts_custom_column', 'slv_stats_admin_column_content', 10, 2 );
add_action( 'manage_pages_custom_column', 'slv_stats_admin_column_content', 10, 2 );

/**
 * 添加列
 *
 * @since 1.0.0
 *
 * @param array $columns 原列
 *
 * @return array
 */
function slv_stats_admin_columns( array $columns ): array {
    $new = [];

    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;

        if ( 'title' === $key ) {
            $new['slv_views']    = esc_html__( '浏览', 'sunlyvo-nexus' );
            $new['slv_reads']    = esc_html__( '深度阅读', 'sunlyvo-nexus' );
            $new['slv_comments'] = esc_html__( '评论', 'sunlyvo-nexus' );
        }
    }

    return $new;
}

/**
 * 列内容
 *
 * @since 1.0.0
 *
 * @param string $column  列名
 * @param int    $post_id 文章ID
 *
 * @return void
 */
function slv_stats_admin_column_content( string $column, int $post_id ): void {
    if ( 'slv_views' === $column ) {
        $stats = slv_stats_get( $post_id );
        echo esc_html( number_format_i18n( $stats['views'] ) );
    } elseif ( 'slv_reads' === $column ) {
        $stats = slv_stats_get( $post_id );
        echo esc_html( number_format_i18n( $stats['reads'] ) );
    } elseif ( 'slv_comments' === $column ) {
        echo esc_html( number_format_i18n( (int) get_comments_number( $post_id ) ) );
    }
}

// === 编辑器底部状态栏 ===
add_action( 'admin_enqueue_scripts', 'slv_stats_admin_assets' );

/**
 * 加载后台统计脚本
 *
 * @since 1.0.0
 *
 * @param string $hook 当前 hook
 *
 * @return void
 */
function slv_stats_admin_assets( string $hook ): void {
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        return;
    }

    if ( file_exists( SLV_THEME_DIR . '/assets/js/admin-stats-bar.js' ) ) {
        wp_enqueue_script(
            'slv-admin-stats-bar',
            SLV_ASSETS_URI . '/js/admin-stats-bar.js',
            [],
            SLV_VERSION,
            [ 'strategy' => 'defer', 'in_footer' => true ]
        );

        // 传递当前 post_id
        global $post;
        $post_id = $post ? (int) $post->ID : 0;

        $stats = $post_id > 0 ? slv_stats_get( $post_id ) : null;

        wp_localize_script( 'slv-admin-stats-bar', 'slvStats', [
            'postId'   => $post_id,
            'views'    => $stats ? $stats['views'] : 0,
            'reads'    => $stats ? $stats['reads'] : 0,
            'words'    => function_exists( 'slv_get_chapter_words' ) && $post_id > 0 ? slv_get_chapter_words( $post_id ) : 0,
            'time'     => function_exists( 'slv_get_chapter_time' ) && $post_id > 0 ? slv_get_chapter_time( $post_id ) : 0,
            'comments' => $post_id > 0 ? (int) get_comments_number( $post_id ) : 0,
        ] );
    }
}

// === 统计自检页 ===
add_action( 'admin_menu', 'slv_stats_admin_menu', 30 );

/**
 * 注册菜单
 *
 * @since 1.0.0
 * @return void
 */
function slv_stats_admin_menu(): void {
    add_submenu_page(
        'slv-dashboard',
        esc_html__( '统计自检', 'sunlyvo-nexus' ),
        esc_html__( '统计自检', 'sunlyvo-nexus' ),
        'manage_options',
        'slv-stats',
        'slv_stats_admin_page'
    );
}

/**
 * 渲染自检页
 *
 * @since 1.0.0
 * @return void
 */
function slv_stats_admin_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( '权限不足。', 'sunlyvo-nexus' ) );
    }

    global $wpdb;

    $stats_table = $wpdb->prefix . 'slv_post_stats';
    $daily_table = $wpdb->prefix . 'slv_post_daily';
    $log_table   = $wpdb->prefix . 'slv_track_log';

    $stats_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$stats_table}" );
    $daily_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$daily_table}" );
    $log_count   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table}" );

    $top_posts = $wpdb->get_results(
        "SELECT post_id, views, reads FROM {$stats_table} ORDER BY views DESC LIMIT 10",
        ARRAY_A
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '统计自检', 'sunlyvo-nexus' ); ?></h1>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin:20px 0;">
            <div style="background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:4px;">
                <div style="font-size:12px;color:#666;"><?php esc_html_e( '聚合行数', 'sunlyvo-nexus' ); ?></div>
                <div style="font-size:28px;font-weight:600;"><?php echo esc_html( number_format_i18n( $stats_count ) ); ?></div>
            </div>
            <div style="background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:4px;">
                <div style="font-size:12px;color:#666;"><?php esc_html_e( '按天行数', 'sunlyvo-nexus' ); ?></div>
                <div style="font-size:28px;font-weight:600;"><?php echo esc_html( number_format_i18n( $daily_count ) ); ?></div>
            </div>
            <div style="background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:4px;">
                <div style="font-size:12px;color:#666;"><?php esc_html_e( '日志行数', 'sunlyvo-nexus' ); ?></div>
                <div style="font-size:28px;font-weight:600;"><?php echo esc_html( number_format_i18n( $log_count ) ); ?></div>
            </div>
        </div>

        <h2><?php esc_html_e( 'Top 10 内容', 'sunlyvo-nexus' ); ?></h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( '标题', 'sunlyvo-nexus' ); ?></th>
                    <th style="width:100px;"><?php esc_html_e( '浏览', 'sunlyvo-nexus' ); ?></th>
                    <th style="width:120px;"><?php esc_html_e( '深度阅读', 'sunlyvo-nexus' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $top_posts ) ) : ?>
                    <tr><td colspan="3"><?php esc_html_e( '暂无数据。', 'sunlyvo-nexus' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $top_posts as $row ) : ?>
                        <?php $post = get_post( (int) $row['post_id'] ); ?>
                        <tr>
                            <td>
                                <?php if ( $post ) : ?>
                                    <a href="<?php echo esc_url( (string) get_edit_post_link( (int) $row['post_id'] ) ); ?>">
                                        <?php echo esc_html( $post->post_title ); ?>
                                    </a>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( number_format_i18n( (int) $row['views'] ) ); ?></td>
                            <td><?php echo esc_html( number_format_i18n( (int) $row['reads'] ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}