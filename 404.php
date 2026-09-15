<?php
/**
 * 404 页面
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="slv-main" class="slv-main slv-404" role="main">
    <div class="slv-container slv-container--narrow">

        <div class="slv-404__inner">
            <div class="slv-404__code">404</div>
            <h1 class="slv-404__title"><?php esc_html_e( '页面不存在', 'sunlyvo-nexus' ); ?></h1>
            <p class="slv-404__desc">
                <?php esc_html_e( '你访问的页面可能被移除、改名，或者从未存在过。', 'sunlyvo-nexus' ); ?>
            </p>

            <div class="slv-404__search">
                <?php get_search_form(); ?>
            </div>

            <div class="slv-404__links">
                <a class="slv-btn slv-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( '返回首页', 'sunlyvo-nexus' ); ?>
                </a>
                <?php
                $slv_archive = get_post_type_archive_link( 'slv_collection' );
                if ( $slv_archive ) :
                    ?>
                    <a class="slv-btn" href="<?php echo esc_url( $slv_archive ); ?>">
                        <?php esc_html_e( '浏览合集', 'sunlyvo-nexus' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<?php
get_footer();