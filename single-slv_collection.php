<?php
/**
 * 合集详情模板
 *
 * 展示合集信息、访问模式、目录（按分组），并标记每个章节的访问状态。
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

    $slv_collection_id = (int) get_the_ID();
    $slv_mode          = slv_get_collection_access_mode( $slv_collection_id );
    $slv_total_count   = slv_get_collection_count( $slv_collection_id );
    $slv_total_words   = slv_get_collection_words( $slv_collection_id );
    $slv_total_time    = slv_get_collection_time( $slv_collection_id );
    $slv_free_count    = slv_count_free_chapters( $slv_collection_id );
    $slv_grouped       = slv_get_collection_grouped_chapters( $slv_collection_id );
    ?>

    <main id="slv-main" class="slv-main slv-collection" role="main">
        <div class="slv-container">

            <nav class="slv-breadcrumb" aria-label="<?php esc_attr_e( '面包屑', 'sunlyvo-nexus' ); ?>">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'slv_collection' ) ?: home_url( '/' ) ); ?>">
                    <?php esc_html_e( '全部合集', 'sunlyvo-nexus' ); ?>
                </a>
                <span class="slv-breadcrumb__sep">/</span>
                <span aria-current="page"><?php the_title(); ?></span>
            </nav>

            <header class="slv-collection__header">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="slv-collection__cover">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="slv-collection__info">
                    <h1 class="slv-collection__title"><?php the_title(); ?></h1>

                    <?php if ( has_excerpt() ) : ?>
                        <div class="slv-collection__intro"><?php the_excerpt(); ?></div>
                    <?php endif; ?>

                    <div class="slv-collection__meta">
                        <span class="slv-collection__meta-item">
                            <?php
                            printf(
                                /* translators: %d: chapters */
                                esc_html__( '%d 章', 'sunlyvo-nexus' ),
                                (int) $slv_total_count
                            );
                            ?>
                        </span>
                        <span class="slv-collection__meta-item">
                            <?php
                            printf(
                                /* translators: %d: free chapters */
                                esc_html__( '%d 章公开', 'sunlyvo-nexus' ),
                                (int) $slv_free_count
                            );
                            ?>
                        </span>
                        <span class="slv-collection__meta-item">
                            <?php
                            printf(
                                /* translators: %s: words */
                                esc_html__( '共 %s 字', 'sunlyvo-nexus' ),
                                esc_html( number_format_i18n( $slv_total_words ) )
                            );
                            ?>
                        </span>
                        <span class="slv-collection__meta-item">
                            <?php
                            printf(
                                /* translators: %d: minutes */
                                esc_html__( '约 %d 分钟', 'sunlyvo-nexus' ),
                                (int) $slv_total_time
                            );
                            ?>
                        </span>
                        <span class="slv-collection__meta-item slv-access-mode slv-access-mode--<?php echo esc_attr( $slv_mode ); ?>">
                            <?php echo esc_html( slv_get_access_mode_label( $slv_mode ) ); ?>
                        </span>
                    </div>
                </div>
            </header>

            <?php if ( '' !== trim( (string) get_the_content() ) ) : ?>
                <div class="slv-collection__body slv-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $slv_grouped ) ) : ?>

                <?php
                foreach ( $slv_grouped as $slv_group ) :
                    $slv_section  = $slv_group['section'];
                    $slv_chapters = $slv_group['chapters'];

                    if ( empty( $slv_chapters ) ) {
                        continue;
                    }
                    ?>

                    <section class="slv-collection-section">
                        <h2 class="slv-collection-section__title">
                            <?php echo esc_html( $slv_section->name ); ?>
                            <span class="slv-collection-section__count">
                                <?php
                                printf(
                                    /* translators: %d: chapters */
                                    esc_html__( '%d 章', 'sunlyvo-nexus' ),
                                    count( $slv_chapters )
                                );
                                ?>
                            </span>
                        </h2>

                        <ol class="slv-chapter-list">
                            <?php
                            foreach ( $slv_chapters as $slv_chapter ) :
                                $slv_cid          = (int) $slv_chapter->ID;
                                $slv_number       = slv_get_chapter_number( $slv_cid );
                                $slv_time         = slv_get_chapter_time( $slv_cid );
                                $slv_words        = slv_get_chapter_words( $slv_cid );
                                $slv_state        = slv_get_access_state( $slv_cid );
                                $slv_state_label  = slv_get_access_state_label( $slv_state );
                                ?>

                                <li class="slv-chapter-list__item">
                                    <a class="slv-chapter-list__link"
                                       href="<?php echo esc_url( (string) get_permalink( $slv_cid ) ); ?>"
                                       data-access-state="<?php echo esc_attr( $slv_state ); ?>">

                                        <span class="slv-chapter-list__number">
                                            <?php echo esc_html( '' !== $slv_number ? $slv_number : '—' ); ?>
                                        </span>

                                        <span class="slv-chapter-list__body">
                                            <span class="slv-chapter-list__title">
                                                <?php echo esc_html( get_the_title( $slv_cid ) ); ?>
                                            </span>
                                            <?php if ( has_excerpt( $slv_cid ) ) : ?>
                                                <span class="slv-chapter-list__excerpt">
                                                    <?php echo esc_html( wp_trim_words( get_the_excerpt( $slv_cid ), 40, '…' ) ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>

                                        <span class="slv-chapter-list__meta">
                                            <span class="slv-chapter-list__meta-item">
                                                <?php
                                                printf(
                                                    /* translators: %d: words */
                                                    esc_html__( '%d 字', 'sunlyvo-nexus' ),
                                                    (int) $slv_words
                                                );
                                                ?>
                                            </span>
                                            <span class="slv-chapter-list__meta-item">
                                                <?php
                                                printf(
                                                    /* translators: %d: minutes */
                                                    esc_html__( '%d 分钟', 'sunlyvo-nexus' ),
                                                    (int) $slv_time
                                                );
                                                ?>
                                            </span>
                                            <span class="slv-chapter-list__badge slv-access-state--<?php echo esc_attr( $slv_state ); ?>">
                                                <?php echo esc_html( $slv_state_label ); ?>
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <?php
                            endforeach;
                            ?>
                        </ol>
                    </section>

                    <?php
                endforeach;
                ?>

            <?php else : ?>

                <section class="slv-no-results">
                    <p><?php esc_html_e( '合集正在准备中，敬请期待。', 'sunlyvo-nexus' ); ?></p>
                </section>

            <?php endif; ?>

        </div>
    </main>

    <?php
endwhile;
?>

<?php
get_footer();