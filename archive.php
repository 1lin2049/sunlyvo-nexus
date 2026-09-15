<?php
/**
 * 通用归档页
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

<main id="slv-main" class="slv-main slv-archive" role="main">
    <div class="slv-container">

        <header class="slv-page-header">
            <div class="slv-page-header__breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '首页', 'sunlyvo-nexus' ); ?></a>
                <span>/</span>
                <span><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></span>
            </div>
            <h1 class="slv-page-header__title">
                <?php the_archive_title(); ?>
            </h1>
            <?php
            $slv_desc = get_the_archive_description();
            if ( '' !== $slv_desc ) :
                ?>
                <div class="slv-archive__description">
                    <?php echo wp_kses_post( $slv_desc ); ?>
                </div>
            <?php endif; ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="slv-post-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'slv-post-card' ); ?>>
                        <a class="slv-post-card__link" href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="slv-post-card__cover">
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                </div>
                            <?php endif; ?>
                            <time class="slv-post-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
                                <?php echo esc_html( get_the_date() ); ?>
                            </time>
                            <h2 class="slv-post-card__title"><?php the_title(); ?></h2>
                            <p class="slv-post-card__excerpt">
                                <?php echo esc_html( wp_trim_words( get_the_excerpt(), 30, '…' ) ); ?>
                            </p>
                        </a>
                    </article>
                    <?php
                endwhile;
                ?>
            </div>

            <?php
            the_posts_pagination(
                [
                    'mid_size'  => 2,
                    'prev_text' => '‹ ' . esc_html__( '上一页', 'sunlyvo-nexus' ),
                    'next_text' => esc_html__( '下一页', 'sunlyvo-nexus' ) . ' ›',
                ]
            );
            ?>

        <?php else : ?>

            <section class="slv-empty-state">
                <h2 class="slv-empty-state__title"><?php esc_html_e( '这里还没有内容', 'sunlyvo-nexus' ); ?></h2>
                <p class="slv-empty-state__desc"><?php esc_html_e( '稍后再来，或浏览其他分类。', 'sunlyvo-nexus' ); ?></p>
                <a class="slv-btn slv-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( '返回首页', 'sunlyvo-nexus' ); ?>
                </a>
            </section>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();