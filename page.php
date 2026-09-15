<?php
/**
 * 独立页面模板
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

    <main id="slv-main" class="slv-main slv-page-single" role="main">
        <div class="slv-container slv-container--narrow">

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'slv-page-article' ); ?>>

                <header class="slv-page-header">
                    <h1 class="slv-page-title"><?php the_title(); ?></h1>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="slv-page-thumbnail">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="slv-page-content slv-content">
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