<?php
/**
 * Template Name: 用户中心
 *
 * 展示当前用户的会员等级、积分、已购内容、积分流水。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

get_header();

$slv_user_id    = get_current_user_id();
$slv_user       = wp_get_current_user();
$slv_membership = slv_get_user_membership( $slv_user_id );
$slv_points     = slv_get_user_points( $slv_user_id );
$slv_spent      = slv_get_user_total_spent( $slv_user_id );
$slv_purchases  = slv_get_user_purchases( $slv_user_id, '', 20 );
$slv_points_log = slv_get_user_points_log( $slv_user_id, 20 );
?>

<main id="slv-main" class="slv-main slv-account" role="main">
    <div class="slv-container">

        <header class="slv-account__header">
            <h1 class="slv-account__title"><?php the_title(); ?></h1>
            <p class="slv-account__greeting">
                <?php
                printf(
                    /* translators: %s: display name */
                    esc_html__( '欢迎回来，%s', 'sunlyvo-nexus' ),
                    esc_html( $slv_user->display_name )
                );
                ?>
            </p>
        </header>

        <div class="slv-account__stats">
            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '会员等级', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value">
                    <?php
                    if ( null !== $slv_membership ) {
                        echo esc_html( (string) $slv_membership['level_name'] );
                    } else {
                        esc_html_e( '普通用户', 'sunlyvo-nexus' );
                    }
                    ?>
                </div>
                <?php if ( null !== $slv_membership ) : ?>
                    <div class="slv-stat-card__meta">
                        <?php echo esc_html( (string) $slv_membership['benefits'] ); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '积分余额', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value"><?php echo esc_html( number_format_i18n( $slv_points ) ); ?></div>
                <div class="slv-stat-card__meta">
                    <?php esc_html_e( '积分可用于购买内容', 'sunlyvo-nexus' ); ?>
                </div>
            </div>

            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '累计消费', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value"><?php echo esc_html( slv_format_price( $slv_spent ) ); ?></div>
                <div class="slv-stat-card__meta">
                    <?php esc_html_e( '用于会员等级计算', 'sunlyvo-nexus' ); ?>
                </div>
            </div>

            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '已购内容', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value">
                    <?php echo esc_html( number_format_i18n( count( $slv_purchases ) ) ); ?>
                </div>
                <div class="slv-stat-card__meta">
                    <?php esc_html_e( '章节与合集', 'sunlyvo-nexus' ); ?>
                </div>
            </div>
        </div>

        <section class="slv-account__section">
            <h2 class="slv-account__section-title"><?php esc_html_e( '已购内容', 'sunlyvo-nexus' ); ?></h2>

            <?php if ( empty( $slv_purchases ) ) : ?>
                <p class="slv-account__empty"><?php esc_html_e( '暂无购买记录。', 'sunlyvo-nexus' ); ?></p>
            <?php else : ?>
                <ul class="slv-account__list">
                    <?php
                    foreach ( $slv_purchases as $slv_purchase ) :
                        $slv_obj_id   = (int) $slv_purchase['object_id'];
                        $slv_obj_type = (string) $slv_purchase['object_type'];
                        $slv_obj      = get_post( $slv_obj_id );

                        if ( ! $slv_obj ) {
                            continue;
                        }
                        ?>
                        <li class="slv-account__list-item">
                            <a href="<?php echo esc_url( (string) get_permalink( $slv_obj_id ) ); ?>">
                                <?php echo esc_html( $slv_obj->post_title ); ?>
                            </a>
                            <span class="slv-account__list-meta">
                                <?php
                                echo 'chapter' === $slv_obj_type
                                    ? esc_html__( '章节', 'sunlyvo-nexus' )
                                    : esc_html__( '合集', 'sunlyvo-nexus' );
                                ?>
                                ·
                                <?php echo esc_html( (string) $slv_purchase['created_at'] ); ?>
                                <?php if ( (float) $slv_purchase['price'] > 0 ) : ?>
                                    · <?php echo esc_html( slv_format_price( (float) $slv_purchase['price'] ) ); ?>
                                <?php endif; ?>
                            </span>
                        </li>
                        <?php
                    endforeach;
                    ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="slv-account__section">
            <h2 class="slv-account__section-title"><?php esc_html_e( '积分流水', 'sunlyvo-nexus' ); ?></h2>

            <?php if ( empty( $slv_points_log ) ) : ?>
                <p class="slv-account__empty"><?php esc_html_e( '暂无积分记录。', 'sunlyvo-nexus' ); ?></p>
            <?php else : ?>
                <table class="slv-account__table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '时间', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '说明', 'sunlyvo-nexus' ); ?></th>
                            <th class="slv-account__table-num"><?php esc_html_e( '积分', 'sunlyvo-nexus' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $slv_points_log as $slv_entry ) : ?>
                            <tr>
                                <td><?php echo esc_html( (string) $slv_entry['created_at'] ); ?></td>
                                <td>
                                    <?php echo esc_html( (string) $slv_entry['description'] ); ?>
                                    <span class="slv-account__table-tag"><?php echo esc_html( (string) $slv_entry['action'] ); ?></span>
                                </td>
                                <td class="slv-account__table-num
                                    <?php echo (int) $slv_entry['points'] > 0 ? 'is-positive' : 'is-negative'; ?>">
                                    <?php
                                    $slv_pts = (int) $slv_entry['points'];
                                    echo esc_html( ( $slv_pts > 0 ? '+' : '' ) . number_format_i18n( $slv_pts ) );
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php
get_footer();