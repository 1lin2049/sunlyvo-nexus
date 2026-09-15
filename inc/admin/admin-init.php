<?php
/**
 * 后台管理入口
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$slv_admin_files = [
    'dashboard.php',
    'meta-box-chapter.php',
    'meta-box-chapter-access.php',
    'meta-box-collection.php',
    'meta-box-layout.php',
    'list-columns-chapter.php',
    'list-columns-collection.php',
    'membership-levels.php',
    'layout-settings.php',
    'assets.php',
];

foreach ( $slv_admin_files as $slv_file ) {
    $slv_path = SLV_INC_DIR . '/admin/' . $slv_file;
    if ( file_exists( $slv_path ) ) {
        require_once $slv_path;
    }
}

unset( $slv_admin_files, $slv_file, $slv_path );