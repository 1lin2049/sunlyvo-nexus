<?php
/**
 * 合集归档模板
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

<main id="slv-main" class="slv-main slv-collections-archive" role="main">
    <div class="slv-container">

        <header class="slv-archive-header">
            <h1 class="slv-archive-title">
                <?php esc_html_e( '全部合集', 'sunlyvo-nexus' ); ?>
            </h1>
            <?php
            $slv_archive_desc = get_the_archive_description();
            if ( '' !== $slv_archive_desc ) :
                ?>
                <div class="slv-archive-description">
                    <?php echo wp_kses_post( $slv_archive_desc ); ?>
                </div>
                <?php
            endif;
            ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="slv-collection-grid">
                <?php
                while ( have_posts() ) :
                    the_post();

                    $slv_cid         = (int) get_the_ID();
                    $slv_count       = slv_get_collection_count( $slv_cid );
                    $slv_total_time  = slv_get_collection_time( $slv_cid );
                    $slv_free_count  = slv_count_free_chapters( $slv_cid );
                    $slv_mode        = slv_get_collection_access_mode( $slv_cid );
                    ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'slv-collection-card' ); ?>>
                        <a class="slv-collection-card__link" href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="slv-collection-card__cover">
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                </div>
                            <?php endif; ?>

                            <div class="slv-collection-card__body">
                                <h2 class="slv-collection-card__title"><?php the_title(); ?></h2>

                                <?php if ( has_excerpt() ) : ?>
                                    <div class="slv-collection-card__intro">
                                        <?php echo esc_html( wp_trim_words( get_the_excerpt(), 40, '…' ) ); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="slv-collection-card__meta">
                                    <span>
                                        <?php
                                        printf(
                                            /* translators: %d: chapters */
                                            esc_html__( '%d 章', 'sunlyvo-nexus' ),
                                            (int) $slv_count
                                        );
                                        ?>
                                    </span>
                                    <span>
                                        <?php
                                        printf(
                                            /* translators: %d: free chapters */
                                            esc_html__( '%d 章公开', 'sunlyvo-nexus' ),
                                            (int) $slv_free_count
                                        );
                                        ?>
                                    </span>
                                    <span>
                                        <?php
                                        printf(
                                            /* translators: %d: minutes */
                                            esc_html__( '约 %d 分钟', 'sunlyvo-nexus' ),
                                            (int) $slv_total_time
                                        );
                                        ?>
                                    </span>
                                    <span class="slv-access-mode slv-access-mode--<?php echo esc_attr( $slv_mode ); ?>">
                                        <?php echo esc_html( slv_get_access_mode_label( $slv_mode ) ); ?>
                                    </span>
                                </div>
                            </div>
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
                    'prev_text' => esc_html__( '上一页', 'sunlyvo-nexus' ),
                    'next_text' => esc_html__( '下一页', 'sunlyvo-nexus' ),
                ]
            );
            ?>

        <?php else : ?>

            <section class="slv-no-results">
                <h2><?php esc_html_e( '暂无合集', 'sunlyvo-nexus' ); ?></h2>
                <p><?php esc_html_e( '内容正在准备中。', 'sunlyvo-nexus' ); ?></p>
            </section>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();