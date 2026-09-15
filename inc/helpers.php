<?php
/**
 * 通用辅助函数
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 判断当前是否为阅读器上下文
 *
 * @since 1.0.0
 * @return bool
 */
function slv_is_reader_context(): bool {
    return is_singular( [ 'slv_chapter' ] );
}

/**
 * 获取当前 blog ID
 *
 * @since 1.0.0
 * @return int
 */
function slv_current_blog_id(): int {
    return (int) get_current_blog_id();
}

/**
 * 判断当前是否为 Multisite 环境
 *
 * @since 1.0.0
 * @return bool
 */
function slv_is_multisite(): bool {
    return is_multisite();
}

/**
 * 格式化金额
 *
 * @since 1.0.0
 *
 * @param float  $amount   金额
 * @param string $currency 货币代码
 *
 * @return string
 */
function slv_format_price( float $amount, string $currency = '' ): string {
    $currency = '' !== $currency ? $currency : (string) slv_get_config( 'general.currency', SLV_DEFAULT_CURRENCY );

    return number_format( $amount, 2 ) . ' ' . $currency;
}

/**
 * 格式化数字（含千分位）
 *
 * @since 1.0.0
 *
 * @param int $number 数字
 *
 * @return string
 */
function slv_format_number( int $number ): string {
    return number_format_i18n( $number );
}

/**
 * 获取当前用户 ID
 *
 * @since 1.0.0
 * @return int
 */
function slv_current_user_id(): int {
    return (int) get_current_user_id();
}

/**
 * 数组安全取值
 *
 * @since 1.0.0
 *
 * @param array  $array   数组
 * @param string $key     键
 * @param mixed  $default 默认值
 *
 * @return mixed
 */
function slv_array_get( array $array, string $key, $default = null ) {
    return array_key_exists( $key, $array ) ? $array[ $key ] : $default;
}

/**
 * 检查字符串是否为空或仅含空白
 *
 * @since 1.0.0
 *
 * @param string $value 字符串
 *
 * @return bool
 */
function slv_is_blank( string $value ): bool {
    return '' === trim( $value );
}

/**
 * 记录调试日志
 *
 * @since 1.0.0
 *
 * @param mixed  $message 日志内容
 * @param string $tag     标签
 *
 * @return void
 */
function slv_log( $message, string $tag = 'slv' ): void {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }

    if ( is_array( $message ) || is_object( $message ) ) {
        $message = wp_json_encode( $message );
    }

    error_log( sprintf( '[%s] %s', $tag, (string) $message ) );
}

/**
 * 获取主题版本
 *
 * @since 1.0.0
 * @return string
 */
function slv_get_version(): string {
    return defined( 'SLV_VERSION' ) ? SLV_VERSION : '0.0.0';
}

/**
 * 获取合集的可读访问模式标签
 *
 * @since 1.0.0
 *
 * @param string $mode 访问模式
 *
 * @return string
 */
function slv_get_access_mode_label( string $mode ): string {
    $labels = [
        'full_paid'   => esc_html__( '整包购买', 'sunlyvo-nexus' ),
        'per_chapter' => esc_html__( '单篇购买', 'sunlyvo-nexus' ),
        'mixed'       => esc_html__( '整包或单篇', 'sunlyvo-nexus' ),
    ];

    return $labels[ $mode ] ?? $labels['mixed'];
}