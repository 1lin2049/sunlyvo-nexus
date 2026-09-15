<?php
/**
 * 分销模块
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/tracking.php';
require_once __DIR__ . '/commission.php';
require_once __DIR__ . '/withdraw.php';

if ( is_admin() ) {
    require_once __DIR__ . '/admin.php';
}