<?php
/**
 * Template Name: 试读目录
 *
 * 用途：
 * - 页面 meta `_slv_preview_collection_id` 有值时：展示该合集的完整章节目录
 * - 无值时：展示所有合集入口卡片
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

<main id="slv-main" class="slv-main slv-preview-page" role="main">
    <div class="slv-container">

        <header class="slv-preview-header">
            <h1 class="slv-preview-title"><?php the_title(); ?></h1>
            <?php
            $slv_intro = get_post_meta( get_the_ID(), '_slv_preview_intro', true );
            if ( is_string( $slv_intro ) && '' !== $slv_intro ) :
                ?>
                <div class="slv-preview-intro">
                    <?php echo wp_kses_post( wpautop( $slv_intro ) ); ?>
                </div>
                <?php
            endif;
            ?>
        </header>

        <?php
        $slv_target_collection_id = (int) get_post_meta( get_the_ID(), '_slv_preview_collection_id', true );

        if ( $slv_target_collection_id > 0 ) :
            slv_render_collection_preview( $slv_target_collection_id );
        else :
            $slv_collections = get_posts(
                [
                    'post_type'      => 'slv_collection',
                    'posts_per_page' => 100,
                    'post_status'    => 'publish',
                    'orderby'        => 'menu_order date',
                    'order'          => 'ASC',
                    'no_found_rows'  => true,
                ]
            );

            if ( ! empty( $slv_collections ) ) :
                ?>
                <div class="slv-collection-grid slv-collection-grid--preview">
                    <?php
                    foreach ( $slv_collections as $slv_collection ) :
                        $slv_cid        = (int) $slv_collection->ID;
                        $slv_count      = slv_get_collection_count( $slv_cid );
                        $slv_free_count = slv_count_free_chapters( $slv_cid );
                        $slv_total_time = slv_get_collection_time( $slv_cid );
                        ?>
                        <article class="slv-collection-card">
                            <a class="slv-collection-card__link"
                               href="<?php echo esc_url( (string) get_permalink( $slv_cid ) ); ?>">
                                <?php
                                $slv_thumb = get_the_post_thumbnail( $slv_cid, 'medium_large' );
                                if ( $slv_thumb ) :
                                    ?>
                                    <div class="slv-collection-card__cover">
                                        <?php echo wp_kses_post( $slv_thumb ); ?>
                                    </div>
                                    <?php
                                endif;
                                ?>

                                <div class="slv-collection-card__body">
                                    <h2 class="slv-collection-card__title">
                                        <?php echo esc_html( get_the_title( $slv_cid ) ); ?>
                                    </h2>

                                    <div class="slv-collection-card__meta">
                                        <span>
                                            <?php
                                            printf(
                                                /* translators: 1: chapters 2: free */
                                                esc_html__( '%1$d 章 · %2$d 章公开', 'sunlyvo-nexus' ),
                                                (int) $slv_count,
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
                                    </div>
                                </div>
                            </a>
                        </article>
                        <?php
                    endforeach;
                    ?>
                </div>
                <?php
            else :
                ?>
                <section class="slv-no-results">
                    <p><?php esc_html_e( '暂无合集。', 'sunlyvo-nexus' ); ?></p>
                </section>
                <?php
            endif;
        endif;
        ?>

    </div>
</main>

<?php
get_footer();