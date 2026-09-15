<?php
/**
 * 购买模块与访问控制的桥接
 *
 * 通过 filter 挂载真实的购买判断逻辑。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'slv_user_has_purchased_chapter', 'slv_purchase_check_chapter', 10, 3 );
add_filter( 'slv_user_has_purchased_collection', 'slv_purchase_check_collection', 10, 3 );

/**
 * 检查用户是否购买过章节
 *
 * @since 1.0.0
 *
 * @param bool $purchased  默认 false
 * @param int  $user_id    用户ID
 * @param int  $chapter_id 章节ID
 *
 * @return bool
 */
function slv_purchase_check_chapter( bool $purchased, int $user_id, int $chapter_id ): bool {
    if ( $purchased ) {
        return true;
    }

    return slv_user_has_purchase( $user_id, 'chapter', $chapter_id );
}

/**
 * 检查用户是否购买过合集
 *
 * @since 1.0.0
 *
 * @param bool $purchased    默认 false
 * @param int  $user_id      用户ID
 * @param int  $collection_id 合集ID
 *
 * @return bool
 */
function slv_purchase_check_collection( bool $purchased, int $user_id, int $collection_id ): bool {
    if ( $purchased ) {
        return true;
    }

    return slv_user_has_purchase( $user_id, 'collection', $collection_id );
}