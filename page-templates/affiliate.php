<?php
/**
 * Template Name: 分销中心
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

$slv_user_id  = get_current_user_id();
$slv_earnings = slv_affiliate_get_user_earnings( $slv_user_id );
$slv_ref_url  = slv_affiliate_get_referral_url( $slv_user_id, home_url( '/' ) );
$slv_records  = slv_affiliate_list_user_earnings( $slv_user_id, 20 );
$slv_withdrawals = slv_affiliate_list_user_withdrawals( $slv_user_id, 20 );
?>

<main id="slv-main" class="slv-main slv-affiliate" role="main">
    <div class="slv-container">

        <header class="slv-page-header">
            <h1 class="slv-page-header__title"><?php the_title(); ?></h1>
        </header>

        <div class="slv-affiliate-stats">
            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '待结算', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value"><?php echo esc_html( slv_format_price( $slv_earnings['pending'] ) ); ?></div>
            </div>

            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '可提现', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value"><?php echo esc_html( slv_format_price( $slv_earnings['available'] ) ); ?></div>
            </div>

            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '已结算', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value"><?php echo esc_html( slv_format_price( $slv_earnings['settled'] ) ); ?></div>
            </div>

            <div class="slv-stat-card">
                <div class="slv-stat-card__label"><?php esc_html_e( '累计收益', 'sunlyvo-nexus' ); ?></div>
                <div class="slv-stat-card__value"><?php echo esc_html( slv_format_price( $slv_earnings['total'] ) ); ?></div>
            </div>
        </div>

        <section class="slv-account__section">
            <h2 class="slv-account__section-title"><?php esc_html_e( '你的推荐链接', 'sunlyvo-nexus' ); ?></h2>
            <p><?php esc_html_e( '分享此链接，任何通过它完成的购买都会给你带来佣金。', 'sunlyvo-nexus' ); ?></p>
            <div class="slv-referral-url">
                <input type="text" value="<?php echo esc_attr( $slv_ref_url ); ?>" readonly onclick="this.select();">
            </div>
        </section>

        <section class="slv-account__section">
            <h2 class="slv-account__section-title"><?php esc_html_e( '收益记录', 'sunlyvo-nexus' ); ?></h2>

            <?php if ( empty( $slv_records ) ) : ?>
                <p class="slv-account__empty"><?php esc_html_e( '暂无收益记录。', 'sunlyvo-nexus' ); ?></p>
            <?php else : ?>
                <table class="slv-account__table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '订单', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '订单金额', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '佣金', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '时间', 'sunlyvo-nexus' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $slv_records as $rec ) : ?>
                            <tr>
                                <td><code>#<?php echo esc_html( (string) $rec['order_id'] ); ?></code></td>
                                <td><?php echo esc_html( slv_format_price( (float) $rec['gross'] ) ); ?></td>
                                <td style="color:#00733d;font-weight:600;"><?php echo esc_html( slv_format_price( (float) $rec['net'] ) ); ?></td>
                                <td><?php echo esc_html( (string) $rec['status'] ); ?></td>
                                <td><?php echo esc_html( (string) $rec['created_at'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="slv-account__section">
            <h2 class="slv-account__section-title"><?php esc_html_e( '提现记录', 'sunlyvo-nexus' ); ?></h2>

            <?php if ( empty( $slv_withdrawals ) ) : ?>
                <p class="slv-account__empty"><?php esc_html_e( '暂无提现记录。', 'sunlyvo-nexus' ); ?></p>
            <?php else : ?>
                <table class="slv-account__table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '金额', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '方式', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '状态', 'sunlyvo-nexus' ); ?></th>
                            <th><?php esc_html_e( '申请时间', 'sunlyvo-nexus' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $slv_withdrawals as $w ) : ?>
                            <tr>
                                <td><?php echo esc_html( slv_format_price( (float) $w['amount'] ) ); ?></td>
                                <td><?php echo esc_html( (string) $w['method'] ); ?></td>
                                <td><?php echo esc_html( (string) $w['status'] ); ?></td>
                                <td><?php echo esc_html( (string) $w['created_at'] ); ?></td>
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