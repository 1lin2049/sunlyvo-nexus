<?php
/**
 * 章节阅读模板
 *
 * 根据访问状态渲染：完整内容 / 试读内容 / 登录提示 / 购买提示
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

    $slv_chapter_id   = (int) get_the_ID();
    $slv_config       = slv_get_chapter_access_config( $slv_chapter_id );
    $slv_state        = slv_get_access_state( $slv_chapter_id );
    $slv_number       = slv_get_chapter_number( $slv_chapter_id );
    $slv_words        = slv_get_chapter_words( $slv_chapter_id );
    $slv_time         = slv_get_chapter_time( $slv_chapter_id );
    $slv_collection_id = slv_get_chapter_collection_id( $slv_chapter_id );
    $slv_can_read     = slv_can_access( $slv_chapter_id );
    ?>

    <main id="slv-main" class="slv-main slv-reader" role="main"
          data-post-id="<?php echo esc_attr( (string) $slv_chapter_id ); ?>"
          data-access-state="<?php echo esc_attr( $slv_state ); ?>">

        <div class="slv-reader__progress" aria-hidden="true">
            <span class="slv-reader__progress-bar"></span>
        </div>

        <div class="slv-container slv-reader__container">

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'slv-chapter' ); ?>>

                <header class="slv-chapter__header">
                    <?php if ( '' !== $slv_number ) : ?>
                        <div class="slv-chapter__number"><?php echo esc_html( $slv_number ); ?></div>
                    <?php endif; ?>

                    <h1 class="slv-chapter__title"><?php the_title(); ?></h1>

                    <div class="slv-chapter__meta">
                        <span class="slv-chapter__meta-item">
                            <?php echo esc_html( number_format_i18n( $slv_words ) ); ?>
                            <?php esc_html_e( '字', 'sunlyvo-nexus' ); ?>
                        </span>
                        <span class="slv-chapter__meta-item">
                            <?php
                            printf(
                                /* translators: %d: minutes */
                                esc_html__( '约 %d 分钟', 'sunlyvo-nexus' ),
                                (int) $slv_time
                            );
                            ?>
                        </span>
                        <span class="slv-chapter__meta-item slv-access-state slv-access-state--<?php echo esc_attr( $slv_state ); ?>">
                            <?php echo esc_html( slv_get_access_state_label( $slv_state ) ); ?>
                        </span>
                    </div>
                </header>

                <?php if ( 'preview' === $slv_state ) : ?>
                    <div class="slv-chapter__notice slv-chapter__notice--preview">
                        <?php
                        printf(
                            /* translators: %s: access type label */
                            esc_html__( '以下为试读内容。完整内容需要：%s', 'sunlyvo-nexus' ),
                            esc_html( slv_get_access_type_label( $slv_config['type'] ) )
                        );
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ( 'login_required' === $slv_state ) : ?>
                    <div class="slv-chapter__notice slv-chapter__notice--login">
                        <?php esc_html_e( '本章节需要登录后访问。', 'sunlyvo-nexus' ); ?>
                        <a class="slv-button slv-button--primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
                            <?php esc_html_e( '登录', 'sunlyvo-nexus' ); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="slv-chapter__body slv-content">
                    <?php
                    if ( $slv_can_read ) {
                        the_content();
                    } elseif ( 'locked' === $slv_state && 'none' !== $slv_config['preview_type'] && $slv_config['preview_value'] > 0 ) {
                        $slv_preview = slv_apply_preview_limit( (string) get_the_content(), $slv_chapter_id );
                        echo wp_kses_post( wpautop( $slv_preview ) );
                        ?>
                        <div class="slv-chapter__paywall">
                            <div class="slv-chapter__paywall-title">
                                <?php esc_html_e( '试读结束', 'sunlyvo-nexus' ); ?>
                            </div>
                            <div class="slv-chapter__paywall-actions">
                                <?php if ( $slv_config['price'] > 0 ) : ?>
                                    <a class="slv-button slv-button--primary"
                                       href="<?php echo esc_url( slv_get_chapter_purchase_url( $slv_chapter_id ) ); ?>">
                                        <?php
                                        printf(
                                            /* translators: %s: price */
                                            esc_html__( '购买本章 · %s', 'sunlyvo-nexus' ),
                                            esc_html( slv_format_price( $slv_config['price'] ) )
                                        );
                                        ?>
                                    </a>
                                <?php endif; ?>

                                <?php if ( $slv_collection_id > 0 ) : ?>
                                    <a class="slv-button"
                                       href="<?php echo esc_url( get_permalink( $slv_collection_id ) ); ?>">
                                        <?php esc_html_e( '查看合集', 'sunlyvo-nexus' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="slv-chapter__paywall slv-chapter__paywall--full">
                            <div class="slv-chapter__paywall-title">
                                <?php esc_html_e( '本章节已锁定', 'sunlyvo-nexus' ); ?>
                            </div>
                            <p class="slv-chapter__paywall-desc">
                                <?php
                                printf(
                                    /* translators: %s: access type label */
                                    esc_html__( '访问方式：%s', 'sunlyvo-nexus' ),
                                    esc_html( slv_get_access_type_label( $slv_config['type'] ) )
                                );
                                ?>
                            </p>
                            <div class="slv-chapter__paywall-actions">
                                <?php if ( 'purchase' === $slv_config['type'] && $slv_config['price'] > 0 ) : ?>
                                    <a class="slv-button slv-button--primary"
                                       href="<?php echo esc_url( slv_get_chapter_purchase_url( $slv_chapter_id ) ); ?>">
                                        <?php
                                        printf(
                                            /* translators: %s: price */
                                            esc_html__( '购买本章 · %s', 'sunlyvo-nexus' ),
                                            esc_html( slv_format_price( $slv_config['price'] ) )
                                        );
                                        ?>
                                    </a>
                                <?php endif; ?>

                                <?php if ( $slv_collection_id > 0 ) : ?>
                                    <a class="slv-button"
                                       href="<?php echo esc_url( get_permalink( $slv_collection_id ) ); ?>">
                                        <?php esc_html_e( '查看合集', 'sunlyvo-nexus' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
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
                // 上下章导航（限定同合集内）
                $slv_prev = null;
                $slv_next = null;

                if ( $slv_collection_id > 0 ) {
                    $slv_all = slv_get_collection_chapters( $slv_collection_id );
                    $slv_ids = wp_list_pluck( $slv_all, 'ID' );
                    $slv_pos = array_search( $slv_chapter_id, array_map( 'intval', $slv_ids ), true );

                    if ( false !== $slv_pos ) {
                        if ( $slv_pos > 0 ) {
                            $slv_prev = get_post( (int) $slv_ids[ $slv_pos - 1 ] );
                        }
                        if ( $slv_pos < count( $slv_ids ) - 1 ) {
                            $slv_next = get_post( (int) $slv_ids[ $slv_pos + 1 ] );
                        }
                    }
                }

                if ( $slv_prev || $slv_next ) :
                    ?>
                    <nav class="slv-chapter__nav" aria-label="<?php esc_attr_e( '章节导航', 'sunlyvo-nexus' ); ?>">
                        <?php if ( $slv_prev ) : ?>
                            <a class="slv-chapter__nav-item slv-chapter__nav-item--prev"
                               href="<?php echo esc_url( (string) get_permalink( $slv_prev ) ); ?>">
                                <span class="slv-chapter__nav-label">
                                    <?php esc_html_e( '上一章', 'sunlyvo-nexus' ); ?>
                                </span>
                                <span class="slv-chapter__nav-title">
                                    <?php echo esc_html( get_the_title( $slv_prev ) ); ?>
                                </span>
                            </a>
                        <?php endif; ?>

                        <?php if ( $slv_next ) : ?>
                            <a class="slv-chapter__nav-item slv-chapter__nav-item--next"
                               href="<?php echo esc_url( (string) get_permalink( $slv_next ) ); ?>">
                                <span class="slv-chapter__nav-label">
                                    <?php esc_html_e( '下一章', 'sunlyvo-nexus' ); ?>
                                </span>
                                <span class="slv-chapter__nav-title">
                                    <?php echo esc_html( get_the_title( $slv_next ) ); ?>
                                </span>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

                <?php
                // 仅当可访问时展示评论
                if ( $slv_can_read && ( comments_open() || get_comments_number() ) ) {
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