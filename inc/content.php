<?php
/**
 * 内容增强
 *
 * 字数、阅读时间、章节排序、合集聚合
 *
 * 访问相关函数已迁移至 inc/access.php
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'save_post_slv_chapter', 'slv_content_sync_chapter_meta', 10, 3 );
add_action( 'save_post_slv_collection', 'slv_content_sync_collection_meta', 10, 3 );

/**
 * 章节保存时同步 meta
 *
 * @since 1.0.0
 *
 * @param int     $post_id 文章ID
 * @param WP_Post $post    文章对象
 * @param bool    $update  是否为更新
 *
 * @return void
 */
function slv_content_sync_chapter_meta( int $post_id, $post, bool $update ): void {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    if ( 'slv_chapter' !== $post->post_type ) {
        return;
    }

    $words = slv_count_words( (string) $post->post_content );
    $time  = slv_get_reading_minutes( (string) $post->post_content );

    update_post_meta( $post_id, '_slv_chapter_words', $words );
    update_post_meta( $post_id, '_slv_chapter_time', $time );

    $number = get_post_meta( $post_id, '_slv_chapter_number', true );
    if ( is_string( $number ) && '' !== $number ) {
        slv_content_sync_chapter_order( $post_id, $number );
    }

    $collection_id = slv_get_chapter_collection_id( $post_id );
    if ( $collection_id > 0 ) {
        slv_content_recalculate_collection( $collection_id );
    }
}

/**
 * 合集保存时同步 meta
 *
 * @since 1.0.0
 *
 * @param int     $post_id 文章ID
 * @param WP_Post $post    文章对象
 * @param bool    $update  是否为更新
 *
 * @return void
 */
function slv_content_sync_collection_meta( int $post_id, $post, bool $update ): void {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    if ( 'slv_collection' !== $post->post_type ) {
        return;
    }

    slv_content_recalculate_collection( $post_id );
}

/**
 * 重新计算合集聚合数据
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return void
 */
function slv_content_recalculate_collection( int $collection_id ): void {
    if ( $collection_id <= 0 ) {
        return;
    }

    $chapters = slv_get_collection_chapters( $collection_id, 'any' );

    $total_words = 0;
    $total_time  = 0;
    $count       = 0;

    foreach ( $chapters as $chapter ) {
        $total_words += slv_get_chapter_words( (int) $chapter->ID );
        $total_time  += slv_get_chapter_time( (int) $chapter->ID );
        $count++;
    }

    update_post_meta( $collection_id, '_slv_collection_words', $total_words );
    update_post_meta( $collection_id, '_slv_collection_time', $total_time );
    update_post_meta( $collection_id, '_slv_collection_count', $count );
}

/**
 * 章节序号同步到 menu_order
 *
 * @since 1.0.0
 *
 * @param int    $post_id 文章ID
 * @param string $number  章节序号
 *
 * @return void
 */
function slv_content_sync_chapter_order( int $post_id, string $number ): void {
    $number = trim( $number );

    if ( '' === $number ) {
        return;
    }

    $order = slv_content_number_to_order( $number );

    if ( $order <= 0 ) {
        return;
    }

    global $wpdb;

    $wpdb->update(
        $wpdb->posts,
        [ 'menu_order' => $order ],
        [ 'ID' => $post_id ],
        [ '%d' ],
        [ '%d' ]
    );

    clean_post_cache( $post_id );
}

/**
 * 章节序号转排序权重
 *
 * @since 1.0.0
 *
 * @param string $number 章节序号
 *
 * @return int
 */
function slv_content_number_to_order( string $number ): int {
    $parts = explode( '.', $number, 2 );

    $major = isset( $parts[0] ) ? (int) $parts[0] : 0;
    $minor = isset( $parts[1] ) ? (int) $parts[1] : 0;

    if ( $major <= 0 && $minor <= 0 ) {
        return 0;
    }

    return $major * 1000 + $minor;
}

/**
 * 计算字符串字数
 *
 * @since 1.0.0
 *
 * @param string $content 内容
 *
 * @return int
 */
function slv_count_words( string $content ): int {
    $content = wp_strip_all_tags( $content );
    $content = strip_shortcodes( $content );

    if ( '' === trim( $content ) ) {
        return 0;
    }

    $chinese_count = preg_match_all( '/[\x{4e00}-\x{9fa5}]/u', $content, $matches );

    $without_chinese = preg_replace( '/[\x{4e00}-\x{9fa5}]/u', ' ', $content );
    $english_count   = preg_match_all( '/[a-zA-Z]+/', (string) $without_chinese, $matches_en );
    $number_count    = preg_match_all( '/\d+/', (string) $without_chinese, $matches_num );

    return (int) $chinese_count + (int) $english_count + (int) $number_count;
}

/**
 * 计算预计阅读时间（分钟）
 *
 * @since 1.0.0
 *
 * @param string $content 内容
 *
 * @return int
 */
function slv_get_reading_minutes( string $content ): int {
    $content = wp_strip_all_tags( $content );
    $content = strip_shortcodes( $content );

    if ( '' === trim( $content ) ) {
        return 0;
    }

    $chinese_count = preg_match_all( '/[\x{4e00}-\x{9fa5}]/u', $content, $matches );

    $without_chinese = preg_replace( '/[\x{4e00}-\x{9fa5}]/u', ' ', $content );
    $english_count   = preg_match_all( '/[a-zA-Z]+/', (string) $without_chinese, $matches_en );
    $number_count    = preg_match_all( '/\d+/', (string) $without_chinese, $matches_num );

    $minutes = (int) $chinese_count / 400 + (int) $english_count / 200 + (int) $number_count / 300;

    $image_count = preg_match_all( '/<img[^>]*>/i', $content, $matches_img );
    $code_count  = preg_match_all( '/<pre[^>]*>|<code[^>]*>/i', $content, $matches_code );

    $extra_seconds = (int) $image_count * 12 + (int) $code_count * 18;
    $minutes      += $extra_seconds / 60;

    return max( 1, (int) ceil( $minutes ) );
}

/**
 * 获取章节字数（带缓存）
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return int
 */
function slv_get_chapter_words( int $post_id ): int {
    $words = get_post_meta( $post_id, '_slv_chapter_words', true );

    if ( '' === $words || null === $words ) {
        $post = get_post( $post_id );

        if ( ! $post ) {
            return 0;
        }

        $words = slv_count_words( (string) $post->post_content );
        update_post_meta( $post_id, '_slv_chapter_words', $words );
    }

    return (int) $words;
}

/**
 * 获取章节阅读时间（带缓存）
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return int
 */
function slv_get_chapter_time( int $post_id ): int {
    $time = get_post_meta( $post_id, '_slv_chapter_time', true );

    if ( '' === $time || null === $time ) {
        $post = get_post( $post_id );

        if ( ! $post ) {
            return 0;
        }

        $time = slv_get_reading_minutes( (string) $post->post_content );
        update_post_meta( $post_id, '_slv_chapter_time', $time );
    }

    return (int) $time;
}

/**
 * 获取章节序号
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return string
 */
function slv_get_chapter_number( int $post_id ): string {
    $number = get_post_meta( $post_id, '_slv_chapter_number', true );

    return is_string( $number ) ? $number : '';
}

/**
 * 获取合集访问模式
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return string full_paid | per_chapter | mixed | subscription_only
 */
function slv_get_collection_access_mode( int $collection_id ): string {
    $mode = get_post_meta( $collection_id, '_slv_access_mode', true );

    if ( ! in_array( $mode, [ 'full_paid', 'per_chapter', 'mixed', 'subscription_only' ], true ) ) {
        return 'mixed';
    }

    return (string) $mode;
}

/**
 * 获取合集聚合字数
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return int
 */
function slv_get_collection_words( int $collection_id ): int {
    return (int) get_post_meta( $collection_id, '_slv_collection_words', true );
}

/**
 * 获取合集聚合阅读时间
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return int
 */
function slv_get_collection_time( int $collection_id ): int {
    return (int) get_post_meta( $collection_id, '_slv_collection_time', true );
}

/**
 * 获取合集章节数量
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return int
 */
function slv_get_collection_count( int $collection_id ): int {
    return (int) get_post_meta( $collection_id, '_slv_collection_count', true );
}

/**
 * 统计合集中公开章节数
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return int
 */
function slv_count_free_chapters( int $collection_id ): int {
    $chapters = slv_get_collection_chapters( $collection_id );
    $count    = 0;

    foreach ( $chapters as $chapter ) {
        $config = slv_get_chapter_access_config( (int) $chapter->ID );

        if ( 'public' === $config['type'] ) {
            $count++;
        }
    }

    return $count;
}

/**
 * 获取合集按分组的章节
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return array<int, array{section: WP_Term|object, chapters: WP_Post[]}>
 */
function slv_get_collection_grouped_chapters( int $collection_id ): array {
    $chapters = slv_get_collection_chapters( $collection_id );

    if ( empty( $chapters ) ) {
        return [];
    }

    $sections = get_terms(
        [
            'taxonomy'   => 'slv_chapter_section',
            'hide_empty' => false,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ]
    );

    if ( is_wp_error( $sections ) ) {
        return [];
    }

    $grouped  = [];
    $assigned = [];

    foreach ( $sections as $section ) {
        $matched = [];

        foreach ( $chapters as $chapter ) {
            $terms = wp_get_post_terms( (int) $chapter->ID, 'slv_chapter_section', [ 'fields' => 'ids' ] );

            if ( ! is_wp_error( $terms ) && in_array( $section->term_id, $terms, true ) ) {
                $matched[]                = $chapter;
                $assigned[ $chapter->ID ] = true;
            }
        }

        if ( ! empty( $matched ) ) {
            $grouped[] = [
                'section'  => $section,
                'chapters' => $matched,
            ];
        }
    }

    $ungrouped = [];
    foreach ( $chapters as $chapter ) {
        if ( ! isset( $assigned[ $chapter->ID ] ) ) {
            $ungrouped[] = $chapter;
        }
    }

    if ( ! empty( $ungrouped ) ) {
        $grouped[] = [
            'section'  => (object) [
                'term_id' => 0,
                'name'    => esc_html__( '未分组', 'sunlyvo-nexus' ),
            ],
            'chapters' => $ungrouped,
        ];
    }

    return $grouped;
}

/**
 * 计算内容热度（用于排序）
 *
 * @since 1.0.0
 *
 * @param int $post_id 文章ID
 *
 * @return int
 */
function slv_content_heat_score( int $post_id ): int {
    $words   = slv_get_chapter_words( $post_id );
    $clicks  = (int) get_post_meta( $post_id, '_slv_view_count', true );
    $comment = (int) get_comments_number( $post_id );

    return (int) ( $words * 0.3 + $clicks * 2 + $comment * 10 );
}

/**
 * 渲染单个合集的试读目录
 *
 * 供 page-templates/preview.php 使用。
 *
 * @since 1.0.0
 *
 * @param int $collection_id 合集ID
 *
 * @return void
 */
function slv_render_collection_preview( int $collection_id ): void {
    if ( $collection_id <= 0 ) {
        return;
    }

    $collection = get_post( $collection_id );

    if ( ! $collection || 'slv_collection' !== $collection->post_type ) {
        echo '<section class="slv-no-results"><p>' . esc_html__( '合集不存在。', 'sunlyvo-nexus' ) . '</p></section>';
        return;
    }

    $grouped = slv_get_collection_grouped_chapters( $collection_id );

    if ( empty( $grouped ) ) {
        echo '<section class="slv-no-results"><p>' . esc_html__( '暂无章节。', 'sunlyvo-nexus' ) . '</p></section>';
        return;
    }

    foreach ( $grouped as $group ) {
        $section  = $group['section'];
        $chapters = $group['chapters'];

        if ( empty( $chapters ) ) {
            continue;
        }

        echo '<section class="slv-preview-section">';
        echo '<header class="slv-preview-section__header">';
        echo '<h2 class="slv-preview-section__title">' . esc_html( $section->name ) . '</h2>';

        // 统计分组信息
        $free_count = 0;
        $total_time = 0;

        foreach ( $chapters as $chapter ) {
            $cid   = (int) $chapter->ID;
            $cfg   = slv_get_chapter_access_config( $cid );
            if ( 'public' === $cfg['type'] ) {
                $free_count++;
            }
            $total_time += slv_get_chapter_time( $cid );
        }

        printf(
            '<div class="slv-preview-section__meta"><span>%s</span><span>%s</span></div>',
            esc_html( sprintf(
                /* translators: 1: total 2: public */
                __( '%1$d 章 · %2$d 章公开', 'sunlyvo-nexus' ),
                count( $chapters ),
                $free_count
            ) ),
            esc_html( sprintf(
                /* translators: %d: minutes */
                __( '共约 %d 分钟', 'sunlyvo-nexus' ),
                $total_time
            ) )
        );

        echo '</header>';

        echo '<ol class="slv-preview-list">';

        foreach ( $chapters as $chapter ) {
            $cid         = (int) $chapter->ID;
            $number      = slv_get_chapter_number( $cid );
            $time        = slv_get_chapter_time( $cid );
            $words       = slv_get_chapter_words( $cid );
            $state       = slv_get_access_state( $cid );
            $state_label = slv_get_access_state_label( $state );

            printf(
                '<li class="slv-preview-list__item"><a class="slv-preview-list__link" href="%s" data-access-state="%s">',
                esc_url( (string) get_permalink( $cid ) ),
                esc_attr( $state )
            );

            printf(
                '<span class="slv-preview-list__number">%s</span>',
                esc_html( '' !== $number ? $number : '—' )
            );

            printf(
                '<span class="slv-preview-list__body"><span class="slv-preview-list__title">%s</span></span>',
                esc_html( get_the_title( $cid ) )
            );

            printf(
                '<span class="slv-preview-list__meta"><span>%s</span><span>%s</span><span class="slv-access-state--%s">%s</span></span>',
                esc_html( sprintf(
                    /* translators: %d: words */
                    __( '%d 字', 'sunlyvo-nexus' ),
                    $words
                ) ),
                esc_html( sprintf(
                    /* translators: %d: minutes */
                    __( '%d 分钟', 'sunlyvo-nexus' ),
                    $time
                ) ),
                esc_attr( $state ),
                esc_html( $state_label )
            );

            echo '</a></li>';
        }

        echo '</ol></section>';
    }
}