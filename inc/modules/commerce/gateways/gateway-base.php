<?php
/**
 * 支付网关抽象基类
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 支付网关抽象类
 */
abstract class SLV_Payment_Gateway {

    /**
     * 网关唯一标识
     *
     * @return string
     */
    abstract public function get_id(): string;

    /**
     * 网关显示名称
     *
     * @return string
     */
    abstract public function get_title(): string;

    /**
     * 网关描述
     *
     * @return string
     */
    public function get_description(): string {
        return '';
    }

    /**
     * 网关是否可用
     *
     * @return bool
     */
    abstract public function is_available(): bool;

    /**
     * 发起支付
     *
     * @param array<string, mixed> $order 订单数据
     * @param array<string, mixed> $args  支付参数
     *
     * @return array{success:bool, message:string, transaction_id:string, redirect_url:string, payment_data:array<string,mixed>}
     */
    abstract public function process( array $order, array $args ): array;

    /**
     * 处理 Webhook
     *
     * @param array<string, mixed> $payload Webhook 数据
     * @param array<string, string> $headers HTTP 头
     *
     * @return array{success:bool, order_id:int, message:string}
     */
    public function handle_webhook( array $payload, array $headers ): array {
        return [
            'success'  => false,
            'order_id' => 0,
            'message'  => 'Webhook not supported',
        ];
    }

    /**
     * 退款
     *
     * @param int   $order_id 订单ID
     * @param float $amount   退款金额
     *
     * @return array{success:bool, message:string}
     */
    public function refund( int $order_id, float $amount ): array {
        return [
            'success' => false,
            'message' => esc_html__( '此网关不支持退款。', 'sunlyvo-nexus' ),
        ];
    }
}

/**
 * 获取网关实例
 *
 * @since 1.0.0
 *
 * @param string $gateway_id 网关ID
 *
 * @return SLV_Payment_Gateway|null
 */
function slv_gateway_get( string $gateway_id ): ?SLV_Payment_Gateway {
    $gateways = slv_gateway_get_all();

    return $gateways[ $gateway_id ] ?? null;
}

/**
 * 获取所有注册的网关
 *
 * @since 1.0.0
 * @return array<string, SLV_Payment_Gateway>
 */
function slv_gateway_get_all(): array {
    static $gateways = null;

    if ( null !== $gateways ) {
        return $gateways;
    }

    $gateways = [];

    // 手动网关（开发环境）
    if ( class_exists( 'SLV_Gateway_Manual' ) ) {
        $manual = new SLV_Gateway_Manual();
        $gateways[ $manual->get_id() ] = $manual;
    }

    // Stripe
    if ( class_exists( 'SLV_Gateway_Stripe' ) ) {
        $stripe = new SLV_Gateway_Stripe();
        $gateways[ $stripe->get_id() ] = $stripe;
    }

    /**
     * 过滤可用网关
     *
     * @since 1.0.0
     *
     * @param array<string, SLV_Payment_Gateway> $gateways 网关列表
     */
    return (array) apply_filters( 'slv_payment_gateways', $gateways );
}

/**
 * 获取所有可用网关（已启用的）
 *
 * @since 1.0.0
 * @return array<string, SLV_Payment_Gateway>
 */
function slv_gateway_get_available(): array {
    $all       = slv_gateway_get_all();
    $available = [];

    foreach ( $all as $id => $gateway ) {
        if ( $gateway->is_available() ) {
            $available[ $id ] = $gateway;
        }
    }

    return $available;
}