<?php
/**
 * FAQ Snippet 优化
 *
 * 从内容中提取 H2 问题 + 后续段落作为答案，
 * 输出 FAQPage Schema。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_head', 'slv_seo_output_faq_schema', 5 );

/**
 * 输出 FAQ Schema
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_output_faq_schema(): void {
    if ( ! is_singular() ) {
        return;
    }

    $post = get_post();

    if ( ! $post ) {
        return;
    }

    // 只处理特定类型
    if ( ! in_array( $post->post_type, [ 'post', 'slv_chapter', 'slv_collection', 'page' ], true ) ) {
        return;
    }

    // 手动指定 FAQ 内容的元字段优先
    $manual_faq = get_post_meta( (int) $post->ID, '_slv_faq_items', true );

    $items = [];

    if ( is_array( $manual_faq ) && ! empty( $manual_faq ) ) {
        $items = $manual_faq;
    } else {
        $items = slv_seo_extract_faq_from_content( (string) $post->post_content );
    }

    if ( empty( $items ) ) {
        return;
    }

    $entities = [];

    foreach ( $items as $item ) {
        if ( empty( $item['question'] ) || empty( $item['answer'] ) ) {
            continue;
        }

        $entities[] = [
            '@type'          => 'Question',
            'name'           => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $item['answer'],
            ],
        ];
    }

    if ( empty( $entities ) ) {
        return;
    }

    $data = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    ];

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
    );
}

/**
 * 从内容中提取 FAQ
 *
 * 规则：
 * - H2 标题以 ? / ？ / 吗 / 呢 结尾 → 识别为问题
 * - 跟随的段落 → 识别为答案
 * - 最多提取 10 个
 *
 * @since 1.0.0
 *
 * @param string $content 内容
 *
 * @return array<int, array{question:string, answer:string}>
 */
function slv_seo_extract_faq_from_content( string $content ): array {
    if ( '' === $content ) {
        return [];
    }

    // 匹配 H2 + 后续段落
    $pattern = '/<h2[^>]*>(.*?)<\/h2>\s*(.*?)(?=<h2|<h3|$)/is';

    if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
        return [];
    }

    $items = [];

    foreach ( $matches as $match ) {
        $question = wp_strip_all_tags( $match[1] );
        $answer   = wp_strip_all_tags( $match[2] );

        // 问题特征
        $is_question = (
            strpos( $question, '?' ) !== false
            || strpos( $question, '？' ) !== false
            || mb_substr( $question, -1 ) === '吗'
            || mb_substr( $question, -1 ) === '呢'
        );

        if ( ! $is_question ) {
            continue;
        }

        // 答案太短或太长跳过
        $answer_len = mb_strlen( $answer );

        if ( $answer_len < 20 || $answer_len > 500 ) {
            continue;
        }

        $items[] = [
            'question' => $question,
            'answer'   => $answer,
        ];

        if ( count( $items ) >= 10 ) {
            break;
        }
    }

    return $items;
}