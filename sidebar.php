<?php
/**
 * 侧边栏容器
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_active_sidebar( 'sidebar-primary' ) ) {
    return;
}
?>
<aside id="slv-sidebar" class="slv-sidebar" role="complementary">
    <?php dynamic_sidebar( 'sidebar-primary' ); ?>
</aside>