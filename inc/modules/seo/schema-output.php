<?php
/**
 * Schema.org 结构化数据输出
 *
 * 覆盖：
 * - WebSite（站点）
 * - Organization（组织）
 * - Article（博客文章）
 * - Product（商品）
 * - Book（合集）
 * - Chapter（章节）
 * - BreadcrumbList（面包屑）
 * - Course（课程，预留）
 * - FAQPage（FAQ）
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_head', 'slv_seo_output_schema', 4 );

/**
 * 输出 Schema
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_output_schema(): void {
    $graph = [];

    // 站点信息（所有页面）
    $graph[] = slv_seo_schema_website();

    if ( is_singular() ) {
        $graph[] = slv_seo_schema_breadcrumb();

        $post_type = (string) get_post_type();

        switch ( $post_type ) {
            case 'post':
                $graph[] = slv_seo_schema_article();
                break;

            case 'page':
                $graph[] = slv_seo_schema_webpage();
                break;

            case 'slv_collection':
                $graph[] = slv_seo_schema_book();
                break;

            case 'slv_chapter':
                $graph[] = slv_seo_schema_chapter();
                break;
        }
    }

    if ( is_archive() || is_tax() ) {
        $graph[] = slv_seo_schema_collection_page();
    }

    /**
     * 过滤 Schema 图
     *
     * @since 1.0.0
     *
     * @param array $graph Schema 节点数组
     */
    $graph = (array) apply_filters( 'slv_seo_schema_graph', $graph );

    if ( empty( $graph ) ) {
        return;
    }

    $data = [
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    ];

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
    );
}

/**
 * WebSite Schema
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_seo_schema_website(): array {
    return [
        '@type'      => 'WebSite',
        '@id'        => home_url( '/#website' ),
        'url'        => home_url( '/' ),
        'name'       => (string) get_bloginfo( 'name' ),
        'description' => (string) get_bloginfo( 'description' ),
        'inLanguage' => (string) get_bloginfo( 'language' ),
        'publisher'  => [ '@id' => home_url( '/#organization' ) ],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => home_url( '/?s={search_term_string}' ),
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

/**
 * Article Schema
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_seo_schema_article(): array {
    $post = get_post();

    if ( ! $post ) {
        return [];
    }

    $author = get_userdata( (int) $post->post_author );

    $schema = [
        '@type'            => 'Article',
        '@id'              => (string) get_permalink() . '#article',
        'headline'         => (string) get_the_title(),
        'description'      => slv_seo_get_page_description(),
        'url'              => (string) get_permalink(),
        'datePublished'    => get_the_date( DATE_W3C ),
        'dateModified'     => get_the_modified_date( DATE_W3C ),
        'inLanguage'       => (string) get_bloginfo( 'language' ),
        'mainEntityOfPage' => [ '@id' => (string) get_permalink() . '#webpage' ],
        'isPartOf'         => [ '@id' => home_url( '/#website' ) ],
    ];

    if ( $author ) {
        $schema['author'] = [
            '@type' => 'Person',
            'name'  => $author->display_name,
            'url'   => get_author_posts_url( (int) $author->ID ),
        ];
    }

    if ( has_post_thumbnail() ) {
        $schema['image'] = [
            '@type'  => 'ImageObject',
            'url'    => (string) get_the_post_thumbnail_url( null, 'full' ),
            'width'  => 1200,
            'height' => 630,
        ];
    }

    $word_count = slv_count_words( (string) $post->post_content );

    if ( $word_count > 0 ) {
        $schema['wordCount'] = $word_count;
    }

    return $schema;
}

/**
 * WebPage Schema
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_seo_schema_webpage(): array {
    return [
        '@type'      => 'WebPage',
        '@id'        => (string) get_permalink() . '#webpage',
        'url'        => (string) get_permalink(),
        'name'       => (string) get_the_title(),
        'description' => slv_seo_get_page_description(),
        'inLanguage' => (string) get_bloginfo( 'language' ),
        'isPartOf'   => [ '@id' => home_url( '/#website' ) ],
    ];
}

/**
 * Book Schema（合集）
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_seo_schema_book(): array {
    $collection_id = (int) get_the_ID();

    $chapters = slv_get_collection_chapters( $collection_id );

    $schema = [
        '@type'            => 'Book',
        '@id'              => (string) get_permalink() . '#book',
        'name'             => (string) get_the_title(),
        'description'      => slv_seo_get_page_description(),
        'url'              => (string) get_permalink(),
        'inLanguage'       => (string) get_bloginfo( 'language' ),
        'bookFormat'       => 'https://schema.org/EBook',
        'numberOfPages'    => slv_get_collection_count( $collection_id ),
        'isPartOf'         => [ '@id' => home_url( '/#website' ) ],
    ];

    // 作者
    $post = get_post();

    if ( $post ) {
        $author = get_userdata( (int) $post->post_author );

        if ( $author ) {
            $schema['author'] = [
                '@type' => 'Person',
                'name'  => $author->display_name,
            ];
        }
    }

    // 章节列表
    if ( ! empty( $chapters ) ) {
        $schema['hasPart'] = [];

        foreach ( $chapters as $chapter ) {
            $schema['hasPart'][] = [
                '@type'    => 'Chapter',
                'name'     => $chapter->post_title,
                'url'      => (string) get_permalink( $chapter->ID ),
                'position' => slv_content_number_to_order( slv_get_chapter_number( (int) $chapter->ID ) ),
            ];
        }
    }

    // 价格
    $price = (float) get_post_meta( $collection_id, '_slv_collection_price', true );

    if ( $price > 0 ) {
        $schema['offers'] = [
            '@type'         => 'Offer',
            'price'         => number_format( $price, 2, '.', '' ),
            'priceCurrency' => (string) slv_get_config( 'general.currency', SLV_DEFAULT_CURRENCY ),
            'availability'  => 'https://schema.org/InStock',
            'url'           => (string) get_permalink(),
        ];
    }

    if ( has_post_thumbnail() ) {
        $schema['image'] = (string) get_the_post_thumbnail_url( null, 'full' );
    }

    return $schema;
}

/**
 * Chapter Schema（章节）
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_seo_schema_chapter(): array {
    $chapter_id    = (int) get_the_ID();
    $collection_id = slv_get_chapter_collection_id( $chapter_id );

    $schema = [
        '@type'      => 'Chapter',
        '@id'        => (string) get_permalink() . '#chapter',
        'name'       => (string) get_the_title(),
        'url'        => (string) get_permalink(),
        'inLanguage' => (string) get_bloginfo( 'language' ),
    ];

    if ( $collection_id > 0 ) {
        $schema['isPartOf'] = [
            '@type' => 'Book',
            'name'  => (string) get_the_title( $collection_id ),
            'url'   => (string) get_permalink( $collection_id ),
        ];
    }

    $number = slv_get_chapter_number( $chapter_id );

    if ( '' !== $number ) {
        $schema['position'] = slv_content_number_to_order( $number );
    }

    $words = slv_get_chapter_words( $chapter_id );

    if ( $words > 0 ) {
        $schema['wordCount'] = $words;
    }

    $time = slv_get_chapter_time( $chapter_id );

    if ( $time > 0 ) {
        $schema['timeRequired'] = 'PT' . $time . 'M';
    }

    $config = slv_get_chapter_access_config( $chapter_id );

    if ( 'public' === $config['type'] ) {
        $schema['isAccessibleForFree'] = true;
    } else {
        $schema['isAccessibleForFree'] = false;

        if ( (float) $config['price'] > 0 ) {
            $schema['offers'] = [
                '@type'         => 'Offer',
                'price'         => number_format( (float) $config['price'], 2, '.', '' ),
                'priceCurrency' => (string) slv_get_config( 'general.currency', SLV_DEFAULT_CURRENCY ),
                'availability'  => 'https://schema.org/InStock',
            ];
        }
    }

    // 作者
    $post = get_post();

    if ( $post ) {
        $author = get_userdata( (int) $post->post_author );

        if ( $author ) {
            $schema['author'] = [
                '@type' => 'Person',
                'name'  => $author->display_name,
            ];
        }
    }

    return $schema;
}

/**
 * CollectionPage Schema（归档页）
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_seo_schema_collection_page(): array {
    $title = wp_strip_all_tags( get_the_archive_title() );

    return [
        '@type'      => 'CollectionPage',
        '@id'        => (string) slv_seo_get_canonical_url() . '#collection',
        'name'       => $title,
        'url'        => slv_seo_get_canonical_url(),
        'description' => slv_seo_get_page_description(),
        'inLanguage' => (string) get_bloginfo( 'language' ),
        'isPartOf'   => [ '@id' => home_url( '/#website' ) ],
    ];
}

/**
 * BreadcrumbList Schema
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function slv_seo_schema_breadcrumb(): array {
    $items = [];
    $pos   = 1;

    // 首页
    $items[] = [
        '@type'    => 'ListItem',
        'position' => $pos++,
        'name'     => __( '首页', 'sunlyvo-nexus' ),
        'item'     => home_url( '/' ),
    ];

    // 归档
    if ( is_singular() ) {
        $post_type = (string) get_post_type();

        if ( 'slv_chapter' === $post_type ) {
            $collection_id = slv_get_chapter_collection_id( (int) get_the_ID() );

            if ( $collection_id > 0 ) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => (string) get_the_title( $collection_id ),
                    'item'     => (string) get_permalink( $collection_id ),
                ];
            }
        } elseif ( 'slv_collection' === $post_type ) {
            $archive_link = get_post_type_archive_link( 'slv_collection' );

            if ( $archive_link ) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => __( '全部合集', 'sunlyvo-nexus' ),
                    'item'     => (string) $archive_link,
                ];
            }
        }
    }

    // 当前页
    $items[] = [
        '@type'    => 'ListItem',
        'position' => $pos,
        'name'     => (string) get_the_title(),
        'item'     => (string) get_permalink(),
    ];

    return [
        '@type'           => 'BreadcrumbList',
        '@id'             => (string) get_permalink() . '#breadcrumb',
        'itemListElement' => $items,
    ];
}