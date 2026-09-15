<?php
/**
 * 统计模块
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/track.php';
require_once __DIR__ . '/aggregate.php';
require_once __DIR__ . '/display.php';
require_once __DIR__ . '/rest.php';
require_once __DIR__ . '/cron.php';

if ( is_admin() ) {
    require_once __DIR__ . '/admin.php';
}