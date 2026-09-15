<?php
/**
 * 搜索结果页
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

<main id="slv-main" class="slv-main slv-search" role="main">
    <div class="slv-container">

        <header class="slv-page-header">
            <h1 class="slv-page-header__title">
                <?php
                printf(
                    /* translators: %s: query */
                    esc_html__( '搜索：%s', 'sunlyvo-nexus' ),
                    '<span class="slv-search__query">' . esc_html( get_search_query() ) . '</span>'
                );
                ?>
            </h1>
            <?php if ( have_posts() ) : ?>
                <p class="slv-search__summary">
                    <?php
                    global $wp_query;
                    printf(
                        /* translators: %d: result count */
                        esc_html__( '找到 %d 条结果', 'sunlyvo-nexus' ),
                        (int) $wp_query->found_posts
                    );
                    ?>
                </p>
            <?php endif; ?>
        </header>

        <div class="slv-search__form-wrap">
            <?php get_search_form(); ?>
        </div>

        <?php if ( have_posts() ) : ?>

            <div class="slv-search__results">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'slv-search-item' ); ?>>
                        <a class="slv-search-item__link" href="<?php the_permalink(); ?>">
                            <div class="slv-search-item__type">
                                <?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ?? '' ); ?>
                            </div>
                            <h2 class="slv-search-item__title"><?php the_title(); ?></h2>
                            <p class="slv-search-item__excerpt">
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
                <h2 class="slv-empty-state__title"><?php esc_html_e( '没有找到匹配的内容', 'sunlyvo-nexus' ); ?></h2>
                <p class="slv-empty-state__desc">
                    <?php esc_html_e( '试试换一个关键词，或者浏览我们的合集。', 'sunlyvo-nexus' ); ?>
                </p>
                <a class="slv-btn slv-btn--primary" href="<?php echo esc_url( get_post_type_archive_link( 'slv_collection' ) ); ?>">
                    <?php esc_html_e( '浏览合集', 'sunlyvo-nexus' ); ?>
                </a>
            </section>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();