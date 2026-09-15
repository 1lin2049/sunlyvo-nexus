<?php
/**
 * 搜索表单
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$slv_search_id = 'slv-search-' . wp_unique_id();
?>
<form role="search" method="get" class="slv-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label for="<?php echo esc_attr( $slv_search_id ); ?>" class="screen-reader-text">
        <?php esc_html_e( '搜索', 'sunlyvo-nexus' ); ?>
    </label>
    <input type="search"
           id="<?php echo esc_attr( $slv_search_id ); ?>"
           class="slv-search-form__input"
           name="s"
           value="<?php echo esc_attr( get_search_query() ); ?>"
           placeholder="<?php esc_attr_e( '搜索内容…', 'sunlyvo-nexus' ); ?>"
           required>
    <button type="submit" class="slv-search-form__button" aria-label="<?php esc_attr_e( '提交搜索', 'sunlyvo-nexus' ); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
        </svg>
    </button>
</form>