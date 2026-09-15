# SunLyvo Nexus · 完整规划 v8.0

**版本**：v8.0 · 自研电商层
**日期**：2026-09-16
**变更**：移除 WooCommerce，完全自研电商层

## 一、核心决策记录

| # | 决策 | 理由 |
|---|---|---|
| D1 | 放弃 WooCommerce，完全自研电商层 | 业务形态与 WooCommerce 物理商品模型错配 |
| D2 | 商品模型支持 4 种类型 | digital/physical/service/subscription |
| D3 | 访问控制从分类法迁移到 post meta | 分类法无法表达 5 访问类型 + 4 试读方式 |
| D4 | 订单保存商品快照 | 商品改价不影响历史订单 |
| D5 | 订单号格式 SLV-YYYYMMDD-XXXXX | 人类可读 + 唯一性 + 日期分组 |
| D6 | 支付网关抽象基类 | 可插拔 Stripe / PayPal / Manual |
| D7 | i18n 使用 PHP 数组 | 版本控制友好 |
| D8 | 电商表独立模块目录 | 与业务模块解耦 |

## 二、44 张表清单

### 核心表（32 张）
1. slv_member_levels
2. slv_config_registry
3. slv_config_permissions
4. slv_config_audit_log
5. slv_field_definitions
6. slv_points_log
7. slv_user_purchases
8. slv_vendor_earnings
9. slv_withdrawals
10. slv_content_product
11. slv_digital_assets
12. slv_learning_progress
13. slv_user_follows
14. slv_group_members
15. slv_city_config（网络级）
16. slv_field_sync_rules
17. slv_sync_queue
18. slv_service_configs
19. slv_service_logs
（+ 其他规划表）

### 电商表（13 张）
1. slv_products
2. slv_product_variants
3. slv_warehouses
4. slv_inventory
5. slv_shipping_zones
6. slv_shipping_methods
7. slv_orders
8. slv_order_items
9. slv_order_status_log
10. slv_payment_transactions
11. slv_coupons
12. slv_coupon_uses
13. slv_carts

## 三、订单状态机
pending_payment ──┬─→ paid ──┬─→ processing ──→ completed ──→ refunded
│ │
│ └─→ refunded
├─→ cancelled
└─→ failed ──┬─→ pending_payment
└─→ cancelled

## 四、支付网关
| 网关 | ID | 环境 | 说明 |
|---|---|---|---|
| Manual | manual | 开发 | 仅 WP_DEBUG 启用 |
| Stripe | stripe | 生产 | Checkout Session |

## 五、CLI 命令 v2.1.0
wp slv version
wp slv seed
wp slv list_users
wp slv list_levels
wp slv user_info <user>
wp slv access <id> [--user=...]
wp slv purchase <user> <type> <id>
wp slv purchase_delete <user> <type> <id>
wp slv reset_content <id>
wp slv product_list
wp slv product_sync
wp slv order_list [--user=]
wp slv order_create <user> <id>
wp slv order_pay <order_id>
wp slv i18n stats/test/list

## 六、模块状态
| 功能域 | 状态 |
|---|---|
| D01 基础设施 | ✅ |
| D03 会员积分 | ✅ |
| D04 内容基础 | ✅ |
| D05 电商核心 | ✅ |
| D08 数字资产 | 🔄 |
| D10 分销体系 | 🔄 60% |
| D17 多语言 | 🔄 |
| 其他 | 🔲 |

## 七、开发里程碑
| 阶段 | 状态 |
|---|---|
| M1 骨架 | ✅ |
| M2 内容 | ✅ |
| M3 会员 | ✅ |
| M4 电商 | ✅ |
| M5 分销 | 🔄 |
| M6 AI 代理 | 🔲 |