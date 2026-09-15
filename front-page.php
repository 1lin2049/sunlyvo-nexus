<?php
/**
 * 首页（营销落地页）
 *
 * 显示逻辑：
 * - 静态首页已设置且内容非空 → 显示静态首页内容
 * - 其他情况（未设静态首页 或 内容为空）→ 显示营销落地页
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$slv_static_front = (int) get_option( 'page_on_front' );
$slv_has_content  = false;

if ( $slv_static_front > 0 ) {
    $slv_front_post = get_post( $slv_static_front );

    if ( $slv_front_post && '' !== trim( (string) $slv_front_post->post_content ) ) {
        $slv_has_content = true;
    }
}

if ( $slv_has_content && have_posts() ) :
    while ( have_posts() ) :
        the_post();
        ?>
        <main id="slv-main" class="slv-main" role="main">
            <div class="slv-container slv-container--narrow">
                <article <?php post_class( 'slv-page-article' ); ?>>
                    <header class="slv-page-header">
                        <h1 class="slv-page-title"><?php the_title(); ?></h1>
                    </header>
                    <div class="slv-page-content slv-content">
                        <?php the_content(); ?>
                    </div>
                </article>
            </div>
        </main>
        <?php
    endwhile;
else :
    // === 营销落地页 ===
    $slv_featured_collections = get_posts(
        [
            'post_type'      => 'slv_collection',
            'posts_per_page' => 3,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ]
    );

    $slv_recent_posts = get_posts(
        [
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'post_status'    => 'publish',
        ]
    );
    ?>

    <main id="slv-main" class="slv-main" role="main">

        <!-- Hero -->
        <section class="slv-hero">
            <div class="slv-container">
                <div class="slv-hero__inner">
                    <h1 class="slv-hero__title"><?php bloginfo( 'name' ); ?></h1>
                    <p class="slv-hero__subtitle">
                        <?php bloginfo( 'description' ); ?>
                    </p>
                    <div class="slv-hero__actions">
                        <?php
                        $slv_archive = get_post_type_archive_link( 'slv_collection' );

                        if ( $slv_archive ) :
                            ?>
                            <a class="slv-btn slv-btn--primary slv-btn--lg" href="<?php echo esc_url( $slv_archive ); ?>">
                                <?php esc_html_e( '浏览全部合集', 'sunlyvo-nexus' ); ?>
                            </a>
                        <?php endif; ?>

                        <?php
                        $slv_blog_url = get_option( 'page_for_posts' )
                            ? get_permalink( (int) get_option( 'page_for_posts' ) )
                            : home_url( '/blog/' );
                        ?>
                        <a class="slv-btn slv-btn--lg" href="<?php echo esc_url( $slv_blog_url ); ?>">
                            <?php esc_html_e( '阅读博客', 'sunlyvo-nexus' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 精选合集 -->
        <?php if ( ! empty( $slv_featured_collections ) ) : ?>
            <section class="slv-section">
                <div class="slv-container">
                    <header class="slv-section__header">
                        <h2 class="slv-section__title"><?php esc_html_e( '精选合集', 'sunlyvo-nexus' ); ?></h2>
                        <?php if ( $slv_archive ) : ?>
                            <a class="slv-section__more" href="<?php echo esc_url( $slv_archive ); ?>">
                                <?php esc_html_e( '查看全部', 'sunlyvo-nexus' ); ?> →
                            </a>
                        <?php endif; ?>
                    </header>

                    <div class="slv-collection-grid">
                        <?php foreach ( $slv_featured_collections as $slv_collection ) : ?>
                            <?php
                            $slv_cid   = (int) $slv_collection->ID;
                            $slv_count = function_exists( 'slv_get_collection_count' ) ? slv_get_collection_count( $slv_cid ) : 0;
                            $slv_time  = function_exists( 'slv_get_collection_time' ) ? slv_get_collection_time( $slv_cid ) : 0;
                            ?>
                            <article class="slv-collection-card">
                                <a class="slv-collection-card__link" href="<?php echo esc_url( (string) get_permalink( $slv_cid ) ); ?>">
                                    <?php if ( has_post_thumbnail( $slv_cid ) ) : ?>
                                        <div class="slv-collection-card__cover">
                                            <?php echo get_the_post_thumbnail( $slv_cid, 'medium_large' ); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="slv-collection-card__body">
                                        <h3 class="slv-collection-card__title">
                                            <?php echo esc_html( $slv_collection->post_title ); ?>
                                        </h3>
                                        <?php if ( '' !== $slv_collection->post_excerpt ) : ?>
                                            <p class="slv-collection-card__intro">
                                                <?php echo esc_html( wp_trim_words( $slv_collection->post_excerpt, 40, '…' ) ); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="slv-collection-card__meta">
                                            <span><?php printf( esc_html__( '%d 章', 'sunlyvo-nexus' ), $slv_count ); ?></span>
                                            <span><?php printf( esc_html__( '约 %d 分钟', 'sunlyvo-nexus' ), $slv_time ); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- 最新文章 -->
        <?php if ( ! empty( $slv_recent_posts ) ) : ?>
            <section class="slv-section slv-section--alt">
                <div class="slv-container">
                    <header class="slv-section__header">
                        <h2 class="slv-section__title"><?php esc_html_e( '最新文章', 'sunlyvo-nexus' ); ?></h2>
                    </header>

                    <div class="slv-post-grid">
                        <?php foreach ( $slv_recent_posts as $slv_post ) : ?>
                            <article class="slv-post-card">
                                <a class="slv-post-card__link" href="<?php echo esc_url( (string) get_permalink( $slv_post->ID ) ); ?>">
                                    <time class="slv-post-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $slv_post ) ); ?>">
                                        <?php echo esc_html( get_the_date( '', $slv_post ) ); ?>
                                    </time>
                                    <h3 class="slv-post-card__title">
                                        <?php echo esc_html( $slv_post->post_title ); ?>
                                    </h3>
                                    <p class="slv-post-card__excerpt">
                                        <?php echo esc_html( wp_trim_words( wp_strip_all_tags( $slv_post->post_excerpt ?: $slv_post->post_content ), 30, '…' ) ); ?>
                                    </p>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- CTA -->
        <section class="slv-section slv-cta">
            <div class="slv-container slv-container--narrow">
                <div class="slv-cta__inner">
                    <h2 class="slv-cta__title"><?php esc_html_e( '开启你的内容之旅', 'sunlyvo-nexus' ); ?></h2>
                    <p class="slv-cta__desc">
                        <?php esc_html_e( '注册即可访问免费内容，购买合集解锁全部章节。', 'sunlyvo-nexus' ); ?>
                    </p>
                    <div class="slv-cta__actions">
                        <?php if ( ! is_user_logged_in() ) : ?>
                            <a class="slv-btn slv-btn--primary slv-btn--lg" href="<?php echo esc_url( wp_registration_url() ); ?>">
                                <?php esc_html_e( '立即注册', 'sunlyvo-nexus' ); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ( $slv_archive ) : ?>
                            <a class="slv-btn slv-btn--lg" href="<?php echo esc_url( $slv_archive ); ?>">
                                <?php esc_html_e( '浏览合集', 'sunlyvo-nexus' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php
endif;

get_footer();