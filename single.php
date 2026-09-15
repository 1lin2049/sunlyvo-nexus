<?php
/**
 * 博客文章单页模板
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) :
    the_post();
    ?>

    <main id="slv-main" class="slv-main slv-single" role="main">
        <div class="slv-container slv-container--narrow">

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'slv-article' ); ?>>

                <header class="slv-article__header">
                    <h1 class="slv-article__title"><?php the_title(); ?></h1>

                    <div class="slv-article__meta">
                        <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
                            <?php echo esc_html( get_the_date() ); ?>
                        </time>
                        <span class="slv-article__meta-sep">·</span>
                        <span><?php echo esc_html( get_the_author() ); ?></span>
                        <?php
                        $slv_cats = get_the_category_list( ', ' );
                        if ( $slv_cats ) :
                            ?>
                            <span class="slv-article__meta-sep">·</span>
                            <span><?php echo wp_kses_post( $slv_cats ); ?></span>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="slv-article__thumbnail">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="slv-article__body slv-content">
                    <?php the_content(); ?>
                </div>

                <?php
                wp_link_pages(
                    [
                        'before' => '<nav class="slv-page-links">',
                        'after'  => '</nav>',
                    ]
                );
                ?>

                <?php
                $slv_prev = get_previous_post();
                $slv_next = get_next_post();

                if ( $slv_prev || $slv_next ) :
                    ?>
                    <nav class="slv-chapter__nav" aria-label="<?php esc_attr_e( '文章导航', 'sunlyvo-nexus' ); ?>">
                        <?php if ( $slv_prev ) : ?>
                            <a class="slv-chapter__nav-item slv-chapter__nav-item--prev"
                               href="<?php echo esc_url( (string) get_permalink( $slv_prev ) ); ?>">
                                <span class="slv-chapter__nav-label"><?php esc_html_e( '上一篇', 'sunlyvo-nexus' ); ?></span>
                                <span class="slv-chapter__nav-title"><?php echo esc_html( get_the_title( $slv_prev ) ); ?></span>
                            </a>
                        <?php endif; ?>
                        <?php if ( $slv_next ) : ?>
                            <a class="slv-chapter__nav-item slv-chapter__nav-item--next"
                               href="<?php echo esc_url( (string) get_permalink( $slv_next ) ); ?>">
                                <span class="slv-chapter__nav-label"><?php esc_html_e( '下一篇', 'sunlyvo-nexus' ); ?></span>
                                <span class="slv-chapter__nav-title"><?php echo esc_html( get_the_title( $slv_next ) ); ?></span>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

                <?php
                if ( comments_open() || get_comments_number() ) {
                    comments_template();
                }
                ?>

            </article>

        </div>
    </main>

    <?php
endwhile;
?>

<?php
get_footer();