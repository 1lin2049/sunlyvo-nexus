<?php
/**
 * 中文化加载器
 *
 * 双层架构：
 * 1. 优先加载主题 languages/plugins/ 下的官方 .mo 文件（完整汉化）
 * 2. PHP 数组翻译表作为术语定制层（覆盖少数品牌字符串）
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 判断是否需要应用中文覆盖
 *
 * @since 1.0.0
 * @return bool
 */
function slv_i18n_should_apply(): bool {
    static $should_apply = null;

    if ( null !== $should_apply ) {
        return $should_apply;
    }

    $locale = determine_locale();

    $should_apply = in_array( $locale, [ 'zh_CN', 'zh_Hans', 'zh-hans' ], true );

    /**
     * 过滤是否应用中文覆盖
     *
     * @since 1.0.0
     *
     * @param bool $should_apply 是否应用
     */
    $should_apply = (bool) apply_filters( 'slv_i18n_should_apply', $should_apply );

    return $should_apply;
}

/**
 * 判断当前 locale 是否为简体中文
 *
 * @since 1.0.0
 *
 * @param string $locale Locale
 *
 * @return bool
 */
function slv_i18n_is_zh_cn( string $locale ): bool {
    return in_array( $locale, [ 'zh_CN', 'zh_Hans', 'zh-hans' ], true );
}

// =============================================================================
// 第一层：主题目录下的官方 .mo 文件优先加载
// =============================================================================

add_filter( 'load_textdomain_mofile', 'slv_i18n_prioritize_theme_mofile', 10, 2 );

/**
 * 优先从主题 languages/ 目录加载翻译文件
 *
 * 支持结构：
 * - languages/plugins/{domain}-{locale}.mo
 * - languages/themes/{domain}-{locale}.mo
 *
 * @since 1.0.0
 *
 * @param string $mofile 原始 mo 文件路径
 * @param string $domain 文本域
 *
 * @return string
 */
function slv_i18n_prioritize_theme_mofile( string $mofile, string $domain ): string {
    if ( '' === $domain ) {
        return $mofile;
    }

    $locale = determine_locale();

    if ( ! slv_i18n_is_zh_cn( $locale ) ) {
        return $mofile;
    }

    // 单站点兼容 locale 变体
    $locales = [ $locale, 'zh_CN', 'zh_Hans' ];

    $base_dir = SLV_THEME_DIR . '/languages';

    foreach ( $locales as $loc ) {
        $candidates = [
            $base_dir . '/plugins/' . $domain . '-' . $loc . '.mo',
            $base_dir . '/themes/' . $domain . '-' . $loc . '.mo',
            $base_dir . '/' . $domain . '-' . $loc . '.mo',
        ];

        foreach ( $candidates as $candidate ) {
            if ( file_exists( $candidate ) ) {
                return $candidate;
            }
        }
    }

    return $mofile;
}

// =============================================================================
// 第二层：PHP 数组翻译表（术语定制）
// =============================================================================

/**
 * 获取所有翻译表
 *
 * @since 1.0.0
 * @return array<string, array<string, string>>
 */
function slv_i18n_get_tables(): array {
    static $tables = null;

    if ( null !== $tables ) {
        return $tables;
    }

    $tables = [];

    $files = [
        SLV_INC_DIR . '/i18n/translations-woocommerce.php',
        SLV_INC_DIR . '/i18n/translations-wordpress.php',
    ];

    foreach ( $files as $file ) {
        if ( ! file_exists( $file ) ) {
            continue;
        }

        $data = require $file;

        if ( is_array( $data ) ) {
            $tables = array_merge( $tables, $data );
        }
    }

    /**
     * 过滤所有翻译表
     *
     * @since 1.0.0
     *
     * @param array<string, array<string, string>> $tables 翻译表
     */
    $tables = (array) apply_filters( 'slv_i18n_tables', $tables );

    return $tables;
}

/**
 * 查找翻译
 *
 * @since 1.0.0
 *
 * @param string $original 原始字符串
 * @param string $domain   文本域
 *
 * @return string
 */
function slv_i18n_lookup( string $original, string $domain ): string {
    if ( '' === $original || '' === $domain ) {
        return '';
    }

    $tables = slv_i18n_get_tables();

    if ( ! isset( $tables[ $domain ] ) ) {
        return '';
    }

    $table = $tables[ $domain ];

    return isset( $table[ $original ] ) ? $table[ $original ] : '';
}

// 挂载 gettext filter（优先级 100，晚于官方 .mo 翻译）
add_filter( 'gettext', 'slv_i18n_filter_gettext', 100, 3 );

/**
 * 单数翻译 filter
 *
 * @since 1.0.0
 *
 * @param string $translated 已翻译文本
 * @param string $original   原始文本
 * @param string $domain     文本域
 *
 * @return string
 */
function slv_i18n_filter_gettext( string $translated, string $original, string $domain ): string {
    if ( ! slv_i18n_should_apply() ) {
        return $translated;
    }

    $custom = slv_i18n_lookup( $original, $domain );

    if ( '' !== $custom ) {
        return $custom;
    }

    return $translated;
}

// 挂载 ngettext filter
add_filter( 'ngettext', 'slv_i18n_filter_ngettext', 100, 5 );

/**
 * 复数翻译 filter
 *
 * @since 1.0.0
 *
 * @param string $translation 已翻译文本
 * @param string $single      单数原文
 * @param string $plural      复数原文
 * @param int    $number      数量
 * @param string $domain      文本域
 *
 * @return string
 */
function slv_i18n_filter_ngettext( string $translation, string $single, string $plural, int $number, string $domain ): string {
    if ( ! slv_i18n_should_apply() ) {
        return $translation;
    }

    $custom = slv_i18n_lookup( $single, $domain );

    if ( '' !== $custom ) {
        if ( false !== strpos( $custom, '%' ) ) {
            return sprintf( $custom, $number );
        }
        return $custom;
    }

    return $translation;
}

// =============================================================================
// 诊断工具
// =============================================================================

/**
 * 获取翻译表统计信息
 *
 * @since 1.0.0
 * @return array<string, int>
 */
function slv_i18n_get_stats(): array {
    $tables = slv_i18n_get_tables();
    $stats  = [];

    foreach ( $tables as $domain => $entries ) {
        $stats[ $domain ] = count( $entries );
    }

    return $stats;
}

/**
 * 获取主题目录下的 .mo 文件列表
 *
 * @since 1.0.0
 * @return array<int, array{file:string, size:int, domain:string, locale:string}>
 */
function slv_i18n_list_theme_mofiles(): array {
    $base_dir = SLV_THEME_DIR . '/languages';

    if ( ! is_dir( $base_dir ) ) {
        return [];
    }

    $result = [];

    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator( $base_dir, \FilesystemIterator::SKIP_DOTS )
    );

    foreach ( $iterator as $file ) {
        if ( ! $file->isFile() ) {
            continue;
        }

        if ( 'mo' !== strtolower( $file->getExtension() ) ) {
            continue;
        }

        $filename = $file->getBasename( '.mo' );
        $parts    = explode( '-', $filename );

        $locale = '';

        if ( count( $parts ) >= 2 ) {
            $locale = array_pop( $parts );
        }

        $domain = implode( '-', $parts );

        $result[] = [
            'file'   => str_replace( SLV_THEME_DIR . '/', '', $file->getPathname() ),
            'size'   => (int) $file->getSize(),
            'domain' => $domain,
            'locale' => $locale,
        ];
    }

    usort( $result, static fn( $a, $b ) => strcmp( $a['file'], $b['file'] ) );

    return $result;
}

/**
 * 导出所有 PHP 数组翻译为 PO 格式
 *
 * @since 1.0.0
 * @return string
 */
function slv_i18n_export_po(): string {
    $tables = slv_i18n_get_tables();
    $output = '';

    foreach ( $tables as $domain => $entries ) {
        $output .= sprintf( "# ============================================\n" );
        $output .= sprintf( "# Domain: %s\n", $domain );
        $output .= sprintf( "# Entries: %d\n", count( $entries ) );
        $output .= sprintf( "# ============================================\n\n" );

        foreach ( $entries as $msgid => $msgstr ) {
            $output .= sprintf( "msgid %s\n", slv_i18n_po_escape( $msgid ) );
            $output .= sprintf( "msgstr %s\n\n", slv_i18n_po_escape( $msgstr ) );
        }

        $output .= "\n";
    }

    return $output;
}

/**
 * PO 字符串转义
 *
 * @since 1.0.0
 *
 * @param string $text 原文
 *
 * @return string
 */
function slv_i18n_po_escape( string $text ): string {
    $escaped = str_replace(
        [ '\\', '"', "\n", "\t", "\r" ],
        [ '\\\\', '\\"', '\\n', '\\t', '\\r' ],
        $text
    );

    return '"' . $escaped . '"';
}