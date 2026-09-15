<?php
/**
 * 最终兜底模板
 *
 * 处理：首页 / 归档 / 搜索 / 未匹配内容
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// 计算页面标题
$slv_page_title = '';

if ( is_home() && ! is_front_page() ) {
    $slv_page_title = single_post_title( '', false );
} elseif ( is_front_page() ) {
    $slv_page_title = get_bloginfo( 'name' );
} elseif ( is_search() ) {
    $slv_page_title = sprintf(
        /* translators: %s: search query */
        __( '搜索结果：%s', 'sunlyvo-nexus' ),
        get_search_query()
    );
} elseif ( is_archive() ) {
    $slv_page_title = get_the_archive_title();
} elseif ( is_singular() ) {
    $slv_page_title = get_the_title();
} else {
    $slv_page_title = get_bloginfo( 'name' );
}
?>

<main id="slv-main" class="slv-main slv-fallback" role="main">
    <div class="slv-container slv-container--narrow">

        <?php if ( '' !== $slv_page_title ) : ?>
            <header class="slv-archive-header">
                <h1 class="slv-archive-title">
                    <?php echo esc_html( $slv_page_title ); ?>
                </h1>
            </header>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>

            <div class="slv-post-list">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'slv-post-item' ); ?>>
                        <header class="slv-post-item__header">
                            <h2 class="slv-post-item__title">
                                <a href="<?php the_permalink(); ?>" rel="bookmark">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                            <div class="slv-post-item__meta">
                                <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                            </div>
                        </header>

                        <div class="slv-post-item__excerpt">
                            <?php the_excerpt(); ?>
                        </div>
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
                <h2><?php esc_html_e( '没有找到内容', 'sunlyvo-nexus' ); ?></h2>
                <p><?php esc_html_e( '试试其他关键词或返回首页。', 'sunlyvo-nexus' ); ?></p>
                <?php get_search_form(); ?>
            </section>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();