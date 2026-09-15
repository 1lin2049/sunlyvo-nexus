<?php
/**
 * 购买记录模块
 *
 * 记录用户购买章节/合集的记录，并桥接到 access.php 的访问判断。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/records.php';
require_once __DIR__ . '/access-bridge.php';