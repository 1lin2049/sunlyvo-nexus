<?php
/**
 * 配置中心
 *
 * 统一配置读写、权限校验、缓存、审计
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取当前上下文的所有者信息
 *
 * M1 阶段始终返回平台；后续根据当前用户角色、blog_id 推断。
 * 通过 filter 允许模块注入。
 *
 * @since 1.0.0
 * @return array{vendor_id:int, station_id:int, region_id:int}
 */
function slv_get_config_owner(): array {
    $owner = [
        'vendor_id'  => 0,
        'station_id' => 0,
        'region_id'  => 0,
    ];

    /**
     * 过滤当前配置所有者
     *
     * @since 1.0.0
     *
     * @param array $owner 所有者信息
     */
    return (array) apply_filters( 'slv_current_config_owner', $owner );
}

/**
 * 读取配置值
 *
 * 继承顺序：商户 → 站长 → 区域 → 平台
 *
 * @since 1.0.0
 *
 * @param string $key     配置键
 * @param mixed  $default 默认值
 *
 * @return mixed
 */
function slv_get_config( string $key, $default = null ) {
    if ( '' === $key ) {
        return $default;
    }

    $owner = slv_get_config_owner();

    $owners = [
        [ 'vendor',  (int) ( $owner['vendor_id']  ?? 0 ) ],
        [ 'station', (int) ( $owner['station_id'] ?? 0 ) ],
        [ 'region',  (int) ( $owner['region_id']  ?? 0 ) ],
        [ 'platform', 0 ],
    ];

    foreach ( $owners as [ $type, $id ] ) {
        if ( 'platform' !== $type && $id <= 0 ) {
            continue;
        }

        $value = slv_get_config_by_owner( $key, $type, $id );

        if ( null !== $value && '__slv_null__' !== $value ) {
            return slv_cast_config_value( $value );
        }
    }

    return $default;
}

/**
 * 按指定所有者读取配置原始值
 *
 * @since 1.0.0
 *
 * @param string $key        配置键
 * @param string $owner_type 所有者类型
 * @param int    $owner_id   所有者ID
 *
 * @return string|null
 */
function slv_get_config_by_owner( string $key, string $owner_type, int $owner_id ): ?string {
    $cache_key = slv_config_cache_key( $key, $owner_type, $owner_id );
    $cached    = wp_cache_get( $cache_key, SLV_CACHE_GROUP );

    if ( false !== $cached ) {
        return is_string( $cached ) ? $cached : null;
    }

    global $wpdb;

    $value = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT config_value FROM {$wpdb->prefix}slv_config_registry
             WHERE config_key = %s AND owner_type = %s AND owner_id = %d
             AND config_value IS NOT NULL
             LIMIT 1",
            $key,
            $owner_type,
            $owner_id
        )
    );

    if ( null === $value ) {
        wp_cache_set( $cache_key, '__slv_null__', SLV_CACHE_GROUP, SLV_CONFIG_CACHE_TTL );
        return null;
    }

    wp_cache_set( $cache_key, (string) $value, SLV_CACHE_GROUP, SLV_CONFIG_CACHE_TTL );

    return (string) $value;
}

/**
 * 写入配置值
 *
 * @since 1.0.0
 *
 * @param string $key        配置键
 * @param mixed  $value      配置值
 * @param string $owner_type 所有者类型
 * @param int    $owner_id   所有者ID
 *
 * @return bool
 */
function slv_set_config( string $key, $value, string $owner_type, int $owner_id ): bool {
    if ( '' === $key || ! slv_is_valid_owner_type( $owner_type ) ) {
        return false;
    }

    if ( ! slv_can_manage_config( $key, $owner_type, $owner_id ) ) {
        return false;
    }

    $config_group = slv_get_config_group_of( $key );

    if ( '' === $config_group ) {
        return false;
    }

    global $wpdb;

    $old_value = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT config_value FROM {$wpdb->prefix}slv_config_registry
             WHERE config_key = %s AND owner_type = %s AND owner_id = %d",
            $key,
            $owner_type,
            $owner_id
        )
    );

    $is_locked = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT is_locked FROM {$wpdb->prefix}slv_config_registry
             WHERE config_key = %s AND owner_type = %s AND owner_id = %d",
            $key,
            $owner_type,
            $owner_id
        )
    );

    if ( $is_locked && 'platform' !== $owner_type ) {
        return false;
    }

    $serialized = is_array( $value ) || is_object( $value )
        ? wp_json_encode( $value )
        : (string) $value;

    $result = $wpdb->replace(
        "{$wpdb->prefix}slv_config_registry",
        [
            'config_key'   => $key,
            'config_group' => $config_group,
            'config_type'  => slv_infer_config_type( $value ),
            'owner_type'   => $owner_type,
            'owner_id'     => $owner_id,
            'config_value' => $serialized,
        ],
        [ '%s', '%s', '%s', '%s', '%d', '%s' ]
    );

    if ( false === $result ) {
        return false;
    }

    slv_log_config_change( $key, $owner_type, $owner_id, $old_value, $serialized );
    wp_cache_delete( slv_config_cache_key( $key, $owner_type, $owner_id ), SLV_CACHE_GROUP );

    /**
     * 配置变更后触发
     *
     * @since 1.0.0
     *
     * @param string $key        配置键
     * @param mixed  $value      新值
     * @param string $owner_type 所有者类型
     * @param int    $owner_id   所有者ID
     */
    do_action( 'slv_config_changed', $key, $value, $owner_type, $owner_id );

    return true;
}

/**
 * 检查当前用户是否有权管理指定配置
 *
 * @since 1.0.0
 *
 * @param string $key        配置键
 * @param string $owner_type 所有者类型
 * @param int    $owner_id   所有者ID
 *
 * @return bool
 */
function slv_can_manage_config( string $key, string $owner_type, int $owner_id ): bool {
    $user_id = get_current_user_id();

    if ( ! $user_id ) {
        return false;
    }

    if ( is_super_admin( $user_id ) ) {
        return true;
    }

    $config_group = slv_get_config_group_of( $key );

    if ( '' === $config_group ) {
        return false;
    }

    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    if ( empty( $roles ) ) {
        return false;
    }

    global $wpdb;

    $placeholders = implode( ',', array_fill( 0, count( $roles ), '%s' ) );

    $can_edit = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT can_edit FROM {$wpdb->prefix}slv_config_permissions
             WHERE config_group = %s AND role IN ({$placeholders})
             LIMIT 1",
            array_merge( [ $config_group ], $roles )
        )
    );

    return (bool) $can_edit;
}

/**
 * 获取配置键所属分组
 *
 * 分组通过前缀约定：`{group}.{key}`
 *
 * @since 1.0.0
 *
 * @param string $key 配置键
 *
 * @return string
 */
function slv_get_config_group_of( string $key ): string {
    if ( '' === $key ) {
        return '';
    }

    $parts = explode( '.', $key, 2 );

    if ( count( $parts ) < 2 ) {
        return '';
    }

    return sanitize_key( $parts[0] );
}

/**
 * 配置值类型转换
 *
 * @since 1.0.0
 *
 * @param string|null $value 原始字符串值
 *
 * @return mixed
 */
function slv_cast_config_value( ?string $value ) {
    if ( null === $value || '__slv_null__' === $value ) {
        return null;
    }

    if ( '' === $value ) {
        return '';
    }

    if ( in_array( $value[0], [ '{', '[' ], true ) ) {
        $decoded = json_decode( $value, true );
        if ( JSON_ERROR_NONE === json_last_error() ) {
            return $decoded;
        }
    }

    if ( 'true' === $value ) {
        return true;
    }
    if ( 'false' === $value ) {
        return false;
    }

    if ( is_numeric( $value ) && (string) (int) $value === $value ) {
        return (int) $value;
    }

    if ( is_numeric( $value ) ) {
        return (float) $value;
    }

    return $value;
}

/**
 * 推断配置值类型
 *
 * @since 1.0.0
 *
 * @param mixed $value 配置值
 *
 * @return string
 */
function slv_infer_config_type( $value ): string {
    if ( is_bool( $value ) ) {
        return 'bool';
    }
    if ( is_int( $value ) ) {
        return 'int';
    }
    if ( is_float( $value ) ) {
        return 'float';
    }
    if ( is_array( $value ) || is_object( $value ) ) {
        return 'json';
    }

    return 'string';
}

/**
 * 校验所有者类型是否合法
 *
 * @since 1.0.0
 *
 * @param string $type 所有者类型
 *
 * @return bool
 */
function slv_is_valid_owner_type( string $type ): bool {
    return in_array( $type, [ 'platform', 'region', 'station', 'vendor' ], true );
}

/**
 * 配置缓存键（含多站点隔离）
 *
 * @since 1.0.0
 *
 * @param string $key        配置键
 * @param string $owner_type 所有者类型
 * @param int    $owner_id   所有者ID
 *
 * @return string
 */
function slv_config_cache_key( string $key, string $owner_type, int $owner_id ): string {
    return 'slv_config_' . get_current_blog_id() . '_' . md5( $key . '|' . $owner_type . '|' . $owner_id );
}

/**
 * 记录配置变更审计日志
 *
 * @since 1.0.0
 *
 * @param string      $key        配置键
 * @param string      $owner_type 所有者类型
 * @param int         $owner_id   所有者ID
 * @param string|null $old_value  旧值
 * @param string      $new_value  新值
 *
 * @return void
 */
function slv_log_config_change( string $key, string $owner_type, int $owner_id, ?string $old_value, string $new_value ): void {
    global $wpdb;

    $wpdb->insert(
        "{$wpdb->prefix}slv_config_audit_log",
        [
            'config_key' => $key,
            'owner_type' => $owner_type,
            'owner_id'   => $owner_id,
            'old_value'  => $old_value,
            'new_value'  => $new_value,
            'changed_by' => get_current_user_id(),
        ],
        [ '%s', '%s', '%d', '%s', '%s', '%d' ]
    );
}

/**
 * 批量读取某分组配置
 *
 * @since 1.0.0
 *
 * @param string $group      配置分组
 * @param string $owner_type 所有者类型
 * @param int    $owner_id   所有者ID
 *
 * @return array<string, mixed>
 */
function slv_get_config_group( string $group, string $owner_type = 'platform', int $owner_id = 0 ): array {
    if ( '' === $group || ! slv_is_valid_owner_type( $owner_type ) ) {
        return [];
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT config_key, config_value FROM {$wpdb->prefix}slv_config_registry
             WHERE config_group = %s AND owner_type = %s AND owner_id = %d
             AND config_value IS NOT NULL
             ORDER BY sort_order ASC, id ASC",
            $group,
            $owner_type,
            $owner_id
        ),
        ARRAY_A
    );

    $result = [];
    foreach ( (array) $rows as $row ) {
        $result[ $row['config_key'] ] = slv_cast_config_value( (string) $row['config_value'] );
    }

    return $result;
}

/**
 * 导出某所有者的全部配置为数组
 *
 * @since 1.0.0
 *
 * @param string $owner_type 所有者类型
 * @param int    $owner_id   所有者ID
 *
 * @return array<string, mixed>
 */
function slv_export_config( string $owner_type = 'platform', int $owner_id = 0 ): array {
    if ( ! slv_is_valid_owner_type( $owner_type ) ) {
        return [];
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT config_key, config_value FROM {$wpdb->prefix}slv_config_registry
             WHERE owner_type = %s AND owner_id = %d
             ORDER BY config_group ASC, sort_order ASC, id ASC",
            $owner_type,
            $owner_id
        ),
        ARRAY_A
    );

    $result = [];
    foreach ( (array) $rows as $row ) {
        $result[ $row['config_key'] ] = slv_cast_config_value( (string) $row['config_value'] );
    }

    return $result;
}