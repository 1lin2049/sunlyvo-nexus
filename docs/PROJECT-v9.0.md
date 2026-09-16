# SunLyvo Nexus 完整规划文档 · AI可执行最终版

**文档性质**：自包含、可直接粘贴到任何AI对话或Agent中执行开发任务
**版本**：v9.0
**品牌**：SunLyvo
**项目名称**：SunLyvo Nexus
**开发者**：李咏燊
**开发者微信**：getthink-info
**已有主题**：ai-coding-methodology-wp-theme（仅保留阅读统计与评论系统作为能力参考，区块模板与设计Token不迁移）
**执行进度仓库**：https://github.com/1lin2049/sunlyvo-nexus
**核心架构**：WordPress Multisite + 自研电商引擎 + React 19 + Ant Design 6
**开发周期**：分6阶段


# 第零部分：AI执行启动指令

## 0.1 使用说明

**当你在新的AI对话中启动本项目时，请先粘贴以下启动指令：**

```
你是一位资深全栈工程师，正在开发SunLyvo Nexus项目。
请阅读以下完整规划文档，理解项目定位、架构、规范、模块和任务后，
严格按照规范执行开发任务。

项目根目录：sunlyvo-nexus/
技术栈：WordPress Multisite + 自研电商引擎 + React 19 + Ant Design 6
开发语言：PHP 8.2+ / TypeScript 5+ / SCSS
代码前缀：slv_
文本域：sunlyvo-nexus
REST命名空间：slv/v1
开发者：李咏燊（微信：getthink-info）

所有开发必须遵守：
1. 配置驱动原则（禁止硬编码）
2. 分层架构原则（五层模型）
3. 安全规范（转义/验证/权限）
4. 命名规范（slv_前缀）
5. Ant Design生态统一
6. 自研电商引擎规范
7. 区块模板与设计Token基于新规划独立构建，不从旧主题迁移
8. 开箱即用交付标准（安装后即刻呈现完整站点结构与示例内容）

请确认你已理解，然后等待我的具体开发指令。
```

## 0.2 快速导航

| 需要了解 | 查看章节 |
|---|---|
| 项目是什么 | 第一部分 |
| 技术怎么选 | 第二部分 |
| 表怎么建 | 第三部分 |
| 电商怎么做 | 第四部分 |
| 合集怎么做 | 第五部分 |
| 模块怎么分 | 第六部分 |
| 代码怎么写 | 第七部分 |
| 规范怎么守 | 第八部分 |
| 任务怎么排 | 第九部分 |
| 怎么验收 | 第十部分 |


# 第一部分：项目上下文

## 1.1 项目定位

**项目名称**：SunLyvo Nexus
**一句话描述**：面向全球市场的多语言多站点内容电商 + 知识付费 + 多商户 + B2B + AI代理商务一体化平台

**不是什么**：
- 不是Amazon（不做纯货架电商）
- 不是Shopify（不做独立站SaaS）
- 不是Udemy（不做纯课程平台）
- 不是知识星球（不做纯社群工具）

**是什么**：
- 商户卖货，流量来自内容和分销
- 创作者生产内容，嵌入商品卡片赚佣金
- 买家消费内容，顺便买东西
- 企业采购，同时获取行业知识
- AI代理自动发现商品、比价、下单、支付
- 站长运营城市分站，获取本地LBS/POI流量

## 1.2 商业模式

**核心公式**：内容即货架，用户即渠道，AI即入口。

**三条链路**：

```
传统电商：用户 → 商品页 → 加购 → 结账 → 支付
内容电商：用户 → 内容 → 内容中的商品卡片 → 直接下单 → 支付
AI代理：AI代理 → 内容被引用 → 商品被调用 → ACP协议自动下单 → 支付完成
```

**最短转化链路**：从"看到内容"到"完成支付"不超过3次点击。

## 1.3 收入模型

| 收入来源 | 费率/价格 | 阶段 |
|---|---|---|
| 商品佣金抽成 | 10-15% | 启动期主收入 |
| 知识付费抽成 | 15-20% | 启动期主收入 |
| 商户入驻费 | $99-499/年 | 规模化期主收入 |
| 会员订阅费 | $9.9-99/月 | 规模化期主收入 |
| 城市分站加盟费 | $500-3000/年 | 规模化期辅收入 |
| 广告/增值/数据 | 竞价/服务费 | 成熟期辅收入 |

## 1.4 三方价值

| 角色 | 核心痛点 | 平台价值 |
|---|---|---|
| 商户 | 流量贵、获客难 | 内容分销网络，按成交付费 |
| 创作者 | 内容难变现 | 嵌入商品赚佣金，零成本带货 |
| 买家 | 信息过载、决策难 | 可信内容推荐，一站式购买 |
| 企业 | 采购效率低 | 批发价、账期、子账户管理 |
| 站长 | 本地流量难变现 | 平台赋能，佣金分成 |
| AI代理 | 商品数据不可用 | ACP协议接入，自动下单 |

## 1.5 项目规模

| 维度 | 数量 |
|---|---|
| CPT | 18+ |
| 自定义表 | 60+ |
| 角色 | 12+ |
| 功能域 | 20 |

## 1.6 项目信息

| 项 | 内容 |
|---|---|
| 品牌 | SunLyvo |
| 项目 | SunLyvo Nexus |
| 开发者 | 李咏燊 |
| 开发者微信 | getthink-info |
| 已有主题 | ai-coding-methodology-wp-theme（仅保留阅读统计与评论系统能力参考） |
| 执行进度 | https://github.com/1lin2049/sunlyvo-nexus |


# 第二部分：技术架构

## 2.1 架构全景图

```
┌─────────────────────────────────────────────────────────────┐
│  用户端：Web / 移动Web / App / AI代理                        │
├─────────────────────────────────────────────────────────────┤
│  表现层：React 19 + Ant Design 6 + ProComponents            │
├─────────────────────────────────────────────────────────────┤
│  应用层：PHP Service / REST Controller                       │
├─────────────────────────────────────────────────────────────┤
│  领域层：业务规则 / 领域模型 / 领域服务                       │
├─────────────────────────────────────────────────────────────┤
│  数据层：Repository / $wpdb / Redis                          │
├─────────────────────────────────────────────────────────────┤
│  基础设施：API客户端 / Logger / Config                       │
├─────────────────────────────────────────────────────────────┤
│  平台层：WordPress Multisite + 自研电商引擎                  │
├─────────────────────────────────────────────────────────────┤
│  外部服务：Stripe / Mux / Elasticsearch / AI API（BYOK）    │
├─────────────────────────────────────────────────────────────┤
│  基础设施：Redis / MySQL / Nginx / S3 / Cloudflare          │
└─────────────────────────────────────────────────────────────┘
```

## 2.2 技术选型清单

| 能力 | 方案 | 类型 | 成本 |
|---|---|---|---|
| 多站点 | WordPress Multisite | 原生 | 免费 |
| 电商引擎 | 自研（slv_commerce模块） | 原生 | 免费 |
| 商品管理 | 自研 slv_products | 原生 | 免费 |
| 购物车 | 自研 slv_carts | 原生 | 免费 |
| 订单 | 自研 slv_orders | 原生 | 免费 |
| 支付 | 自研 + Stripe SDK | 原生+SDK | 2-3%/笔 |
| 库存 | 自研 slv_inventory_logs | 原生 | 免费 |
| 税务 | 自研 + Quaderno API | 原生+API | $29-149/月 |
| 运费 | 自研 slv_shipping_zones | 原生 | 免费 |
| 优惠券 | 自研 slv_coupons | 原生 | 免费 |
| 多币种 | 自研 slv_currencies | 原生 | 免费 |
| 多语言 | 自研分类法 + DeepL API | 混合 | 按用量 |
| 订阅 | 主题原生状态机 | 原生 | — |
| 会员 | 主题原生 + 自定义表 | 原生 | — |
| 积分 | 主题原生 + 自定义表 | 原生 | — |
| 分销 | 主题原生 + 自定义表 | 原生 | — |
| 多商户 | 主题原生 + Stripe Connect | SDK | 0.25-0.5% |
| B2B | 主题原生 | 原生 | — |
| 询盘 | 主题原生 | 原生 | — |
| 合集系统 | 主题原生 | 原生 | — |
| 文库 | 主题原生 | 原生 | — |
| VOD/直播 | Mux PHP SDK | SDK | 按用量 |
| 音频/训练营/咨询/认证/活动 | 主题原生 | 原生 | — |
| 论坛/圈子/朋友圈/图集/百科/FAQ | 主题原生 | 原生 | — |
| 缓存 | 原生持久化对象缓存 + Nginx | 原生 | 免费 |
| 搜索 | MySQL全文索引→Elasticsearch | 混合 | 免费→$50-500/月 |
| CDN | Cloudflare + APO | API | $20-200/月 |
| 邮件 | wp_mail() + API传输层 | 原生+API | $15-100/月 |
| GDPR | 自研 + WP隐私API | 原生 | 免费 |
| SEO/GEO/AEO | 自研 Schema + llms.txt | 原生 | 免费 |
| 分账 | Stripe Connect Transfers | SDK | 0.25-0.5% |
| AI | API接入（OpenRouter/七牛云/官方） | API | 按用量 |
| 前端 | React 19 + Ant Design 6 + ProComponents | 开源 | 免费 |

## 2.3 性能目标

| 指标 | 目标 |
|---|---|
| LCP | < 2.5s |
| INP | < 200ms |
| CLS | < 0.1 |
| TTFB | < 600ms |
| 数据库查询 | < 50 queries/page |
| 同步内容收录率 | > 80% |
| 跨站点重复率 | < 30% |


# 第三部分：数据模型

## 3.1 数据分层原则

| 层级 | 存储 | 内容 |
|---|---|---|
| 状态层 | `wp_usermeta` | 会员等级、积分余额、累计消费、企业归属、订阅状态 |
| 配置层 | 自定义表 | 等级配置、佣金规则、订阅计划、城市配置、服务配置、同步规则 |
| 记录层 | 自定义表 | 积分流水、订单收益、提现、打卡、考试 |
| 关系层 | 自定义表 | 关注、成员、内容-商品、知识关联 |
| 资产层 | 自定义表 | 数字内容交付属性 |
| 进度层 | 自定义表 | 学习进度、播放进度、阅读进度 |
| 交易层 | 自定义表 | 商品、购物车、订单、支付、库存 |

**核心原则**：`wp_usermeta`只存"当前状态值"，自定义表存"历史记录和配置"。

## 3.2 核心表清单（60+张）

### 3.2.1 自研电商引擎表（18张）

```sql
-- 商品主表
CREATE TABLE {$wpdb->prefix}slv_products (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    product_type VARCHAR(30) NOT NULL DEFAULT 'simple',
    sku VARCHAR(100) DEFAULT '',
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description LONGTEXT,
    short_description TEXT,
    status VARCHAR(20) DEFAULT 'draft',
    vendor_id BIGINT(20) DEFAULT 0,
    station_id BIGINT(20) DEFAULT 0,
    price DECIMAL(12,2) DEFAULT 0,
    compare_price DECIMAL(12,2) DEFAULT 0,
    cost_price DECIMAL(12,2) DEFAULT 0,
    wholesale_price DECIMAL(12,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'USD',
    tax_status VARCHAR(20) DEFAULT 'taxable',
    tax_class VARCHAR(50) DEFAULT '',
    manage_stock TINYINT(1) DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    stock_status VARCHAR(20) DEFAULT 'instock',
    weight DECIMAL(10,3) DEFAULT 0,
    length DECIMAL(10,3) DEFAULT 0,
    width DECIMAL(10,3) DEFAULT 0,
    height DECIMAL(10,3) DEFAULT 0,
    hs_code VARCHAR(50) DEFAULT '',
    moq INT DEFAULT 1,
    lead_time VARCHAR(50) DEFAULT '',
    is_featured TINYINT(1) DEFAULT 0,
    is_virtual TINYINT(1) DEFAULT 0,
    is_downloadable TINYINT(1) DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    sale_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_sku (sku),
    UNIQUE KEY uniq_slug (slug),
    KEY idx_vendor_id (vendor_id),
    KEY idx_station_id (station_id),
    KEY idx_status (status),
    KEY idx_product_type (product_type),
    KEY idx_price (price),
    KEY idx_published_at (published_at)
) {$charset_collate};

-- 商品变体表
CREATE TABLE {$wpdb->prefix}slv_product_variants (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    product_id BIGINT(20) NOT NULL,
    sku VARCHAR(100) DEFAULT '',
    attributes TEXT,
    price DECIMAL(12,2) DEFAULT 0,
    wholesale_price DECIMAL(12,2) DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    stock_status VARCHAR(20) DEFAULT 'instock',
    image_id BIGINT(20) DEFAULT 0,
    weight DECIMAL(10,3) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_sku (sku),
    KEY idx_product_id (product_id)
) {$charset_collate};

-- 商品分类关系
CREATE TABLE {$wpdb->prefix}slv_product_categories (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    product_id BIGINT(20) NOT NULL,
    category_id BIGINT(20) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_product_category (product_id, category_id),
    KEY idx_category_id (category_id)
) {$charset_collate};

-- 商品图片关系
CREATE TABLE {$wpdb->prefix}slv_product_images (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    product_id BIGINT(20) NOT NULL,
    attachment_id BIGINT(20) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_product_id (product_id)
) {$charset_collate};

-- 购物车主表
CREATE TABLE {$wpdb->prefix}slv_carts (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    cart_key VARCHAR(64) NOT NULL,
    user_id BIGINT(20) DEFAULT 0,
    session_id VARCHAR(64) DEFAULT '',
    currency VARCHAR(10) DEFAULT 'USD',
    subtotal DECIMAL(12,2) DEFAULT 0,
    discount_total DECIMAL(12,2) DEFAULT 0,
    tax_total DECIMAL(12,2) DEFAULT 0,
    shipping_total DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    coupon_codes TEXT,
    status VARCHAR(20) DEFAULT 'active',
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_cart_key (cart_key),
    KEY idx_user_id (user_id),
    KEY idx_status (status)
) {$charset_collate};

-- 购物车项
CREATE TABLE {$wpdb->prefix}slv_cart_items (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    cart_id BIGINT(20) NOT NULL,
    product_id BIGINT(20) NOT NULL,
    variant_id BIGINT(20) DEFAULT 0,
    vendor_id BIGINT(20) DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    attributes TEXT,
    referrer_id BIGINT(20) DEFAULT 0,
    content_id BIGINT(20) DEFAULT 0,
    content_type VARCHAR(50) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cart_id (cart_id),
    KEY idx_product_id (product_id),
    KEY idx_vendor_id (vendor_id)
) {$charset_collate};

-- 订单主表
CREATE TABLE {$wpdb->prefix}slv_orders (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL,
    user_id BIGINT(20) DEFAULT 0,
    customer_email VARCHAR(100) NOT NULL,
    customer_name VARCHAR(100) DEFAULT '',
    customer_phone VARCHAR(50) DEFAULT '',
    status VARCHAR(20) DEFAULT 'pending',
    currency VARCHAR(10) DEFAULT 'USD',
    subtotal DECIMAL(12,2) DEFAULT 0,
    discount_total DECIMAL(12,2) DEFAULT 0,
    tax_total DECIMAL(12,2) DEFAULT 0,
    shipping_total DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    paid_total DECIMAL(12,2) DEFAULT 0,
    refunded_total DECIMAL(12,2) DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT '',
    payment_status VARCHAR(20) DEFAULT 'pending',
    transaction_id VARCHAR(255) DEFAULT '',
    shipping_method VARCHAR(50) DEFAULT '',
    shipping_address TEXT,
    billing_address TEXT,
    customer_note TEXT,
    admin_note TEXT,
    referrer_id BIGINT(20) DEFAULT 0,
    content_id BIGINT(20) DEFAULT 0,
    content_type VARCHAR(50) DEFAULT '',
    traffic_source VARCHAR(50) DEFAULT 'direct',
    agent_id VARCHAR(100) DEFAULT '',
    station_id BIGINT(20) DEFAULT 0,
    ip_address VARCHAR(45) DEFAULT '',
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    paid_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_order_number (order_number),
    KEY idx_user_id (user_id),
    KEY idx_status (status),
    KEY idx_payment_status (payment_status),
    KEY idx_created_at (created_at),
    KEY idx_station_id (station_id)
) {$charset_collate};

-- 订单项
CREATE TABLE {$wpdb->prefix}slv_order_items (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    order_id BIGINT(20) NOT NULL,
    product_id BIGINT(20) NOT NULL,
    variant_id BIGINT(20) DEFAULT 0,
    vendor_id BIGINT(20) DEFAULT 0,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100) DEFAULT '',
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    commission_rate DECIMAL(5,4) DEFAULT 0,
    commission_amount DECIMAL(12,2) DEFAULT 0,
    vendor_earning DECIMAL(12,2) DEFAULT 0,
    platform_earning DECIMAL(12,2) DEFAULT 0,
    referrer_earning DECIMAL(12,2) DEFAULT 0,
    attributes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_id (order_id),
    KEY idx_product_id (product_id),
    KEY idx_vendor_id (vendor_id)
) {$charset_collate};

-- 订单状态历史
CREATE TABLE {$wpdb->prefix}slv_order_status_history (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    order_id BIGINT(20) NOT NULL,
    from_status VARCHAR(20) DEFAULT '',
    to_status VARCHAR(20) NOT NULL,
    note TEXT,
    changed_by BIGINT(20) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_id (order_id)
) {$charset_collate};

-- 支付记录
CREATE TABLE {$wpdb->prefix}slv_payments (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    order_id BIGINT(20) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    gateway VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(255) DEFAULT '',
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    status VARCHAR(20) DEFAULT 'pending',
    gateway_response TEXT,
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_id (order_id),
    KEY idx_transaction_id (transaction_id),
    KEY idx_status (status)
) {$charset_collate};

-- 退款记录
CREATE TABLE {$wpdb->prefix}slv_refunds (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    order_id BIGINT(20) NOT NULL,
    payment_id BIGINT(20) DEFAULT 0,
    amount DECIMAL(12,2) NOT NULL,
    reason TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    refunded_by BIGINT(20) DEFAULT 0,
    gateway_refund_id VARCHAR(255) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_id (order_id)
) {$charset_collate};

-- 库存日志
CREATE TABLE {$wpdb->prefix}slv_inventory_logs (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    product_id BIGINT(20) NOT NULL,
    variant_id BIGINT(20) DEFAULT 0,
    change_type VARCHAR(30) NOT NULL,
    quantity_change INT NOT NULL,
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    reference_id BIGINT(20) DEFAULT 0,
    note VARCHAR(255) DEFAULT '',
    created_by BIGINT(20) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_product_id (product_id),
    KEY idx_change_type (change_type),
    KEY idx_created_at (created_at)
) {$charset_collate};

-- 税率表
CREATE TABLE {$wpdb->prefix}slv_tax_rates (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    country VARCHAR(5) NOT NULL,
    state VARCHAR(10) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    postcode VARCHAR(20) DEFAULT '',
    tax_class VARCHAR(50) DEFAULT '',
    rate DECIMAL(5,4) NOT NULL,
    tax_name VARCHAR(50) DEFAULT '',
    priority INT DEFAULT 0,
    is_compound TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_location (country, state, city, postcode),
    KEY idx_tax_class (tax_class)
) {$charset_collate};

-- 运费区域
CREATE TABLE {$wpdb->prefix}slv_shipping_zones (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    zone_name VARCHAR(100) NOT NULL,
    regions TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    PRIMARY KEY (id)
) {$charset_collate};

-- 运费方式
CREATE TABLE {$wpdb->prefix}slv_shipping_methods (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    zone_id BIGINT(20) NOT NULL,
    method_type VARCHAR(50) NOT NULL,
    method_name VARCHAR(100) NOT NULL,
    cost DECIMAL(12,2) DEFAULT 0,
    min_amount DECIMAL(12,2) DEFAULT 0,
    max_amount DECIMAL(12,2) DEFAULT 0,
    weight_rate DECIMAL(10,4) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_zone_id (zone_id)
) {$charset_collate};

-- 优惠券
CREATE TABLE {$wpdb->prefix}slv_coupons (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL,
    discount_type VARCHAR(20) NOT NULL,
    discount_value DECIMAL(12,2) NOT NULL,
    min_order_amount DECIMAL(12,2) DEFAULT 0,
    max_discount DECIMAL(12,2) DEFAULT 0,
    usage_limit INT DEFAULT 0,
    usage_limit_per_user INT DEFAULT 0,
    used_count INT DEFAULT 0,
    vendor_id BIGINT(20) DEFAULT 0,
    station_id BIGINT(20) DEFAULT 0,
    applicable_products TEXT,
    applicable_categories TEXT,
    excluded_products TEXT,
    start_date DATETIME DEFAULT NULL,
    end_date DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_code (code),
    KEY idx_vendor_id (vendor_id)
) {$charset_collate};

-- 优惠券使用记录
CREATE TABLE {$wpdb->prefix}slv_coupon_usages (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    coupon_id BIGINT(20) NOT NULL,
    user_id BIGINT(20) NOT NULL,
    order_id BIGINT(20) NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL,
    used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_coupon_id (coupon_id),
    KEY idx_user_id (user_id),
    KEY idx_order_id (order_id)
) {$charset_collate};

-- 货币配置
CREATE TABLE {$wpdb->prefix}slv_currencies (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(15,6) DEFAULT 1,
    decimal_places INT DEFAULT 2,
    is_default TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_code (code)
) {$charset_collate};
```

### 3.2.2 配置类表

```sql
-- 会员等级配置
CREATE TABLE {$wpdb->prefix}slv_member_levels (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    level_name VARCHAR(100) NOT NULL,
    level_slug VARCHAR(100) NOT NULL,
    level_order INT NOT NULL DEFAULT 0,
    required_spent DECIMAL(12,2) DEFAULT 0,
    required_points INT DEFAULT 0,
    discount_rate DECIMAL(5,2) DEFAULT 0,
    points_multiplier DECIMAL(3,2) DEFAULT 1.00,
    benefits TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_level_slug (level_slug)
) {$charset_collate};

-- 统一配置注册表
CREATE TABLE {$wpdb->prefix}slv_config_registry (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    config_key VARCHAR(100) NOT NULL,
    config_group VARCHAR(50) NOT NULL,
    config_type VARCHAR(20) NOT NULL,
    default_value TEXT,
    allowed_values TEXT,
    owner_type VARCHAR(20) NOT NULL,
    owner_id BIGINT(20) DEFAULT 0,
    config_value TEXT,
    is_locked TINYINT(1) DEFAULT 0,
    capability_required VARCHAR(100) DEFAULT '',
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_config_owner (config_key, owner_type, owner_id),
    KEY idx_config_group (config_group)
) {$charset_collate};

-- 配置权限
CREATE TABLE {$wpdb->prefix}slv_config_permissions (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    role VARCHAR(50) NOT NULL,
    config_group VARCHAR(50) NOT NULL,
    can_view TINYINT(1) DEFAULT 1,
    can_edit TINYINT(1) DEFAULT 0,
    can_lock TINYINT(1) DEFAULT 0,
    scope VARCHAR(20) DEFAULT 'own',
    PRIMARY KEY (id),
    UNIQUE KEY uniq_role_group (role, config_group)
) {$charset_collate};
```

### 3.2.3 记录类表

```sql
-- 积分流水
CREATE TABLE {$wpdb->prefix}slv_points_log (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) NOT NULL,
    points INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    reference_id BIGINT(20) DEFAULT 0,
    description VARCHAR(255) DEFAULT '',
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_action (action),
    KEY idx_expires_at (expires_at)
) {$charset_collate};

-- 商户/分销收益
CREATE TABLE {$wpdb->prefix}slv_vendor_earnings (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    vendor_id BIGINT(20) NOT NULL,
    order_id BIGINT(20) NOT NULL,
    gross DECIMAL(12,2) NOT NULL,
    commission DECIMAL(12,2) NOT NULL,
    net DECIMAL(12,2) NOT NULL,
    type VARCHAR(20) DEFAULT 'product',
    status VARCHAR(20) DEFAULT 'pending',
    blog_id BIGINT(20) DEFAULT 0,
    settled_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vendor_id (vendor_id),
    KEY idx_order_id (order_id),
    KEY idx_status (status)
) {$charset_collate};

-- 提现申请
CREATE TABLE {$wpdb->prefix}slv_withdrawals (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method VARCHAR(50) NOT NULL,
    account VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    reviewed_by BIGINT(20) DEFAULT 0,
    reviewed_at DATETIME DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    remark TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_status (status)
) {$charset_collate};
```

### 3.2.4 合集系统表

```sql
-- 合集主表
CREATE TABLE {$wpdb->prefix}slv_collections (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    collection_type VARCHAR(50) NOT NULL,
    description LONGTEXT,
    cover_image_id BIGINT(20) DEFAULT 0,
    author_id BIGINT(20) NOT NULL,
    vendor_id BIGINT(20) DEFAULT 0,
    station_id BIGINT(20) DEFAULT 0,
    price DECIMAL(12,2) DEFAULT 0,
    member_price DECIMAL(12,2) DEFAULT 0,
    points_cost INT DEFAULT 0,
    is_free TINYINT(1) DEFAULT 0,
    member_only TINYINT(1) DEFAULT 0,
    total_items INT DEFAULT 0,
    free_items INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_slug (slug),
    KEY idx_author_id (author_id),
    KEY idx_collection_type (collection_type),
    KEY idx_status (status)
) {$charset_collate};

-- 合集内容项关联
CREATE TABLE {$wpdb->prefix}slv_collection_items (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    collection_id BIGINT(20) NOT NULL,
    object_id BIGINT(20) NOT NULL,
    object_type VARCHAR(50) NOT NULL,
    item_order INT NOT NULL DEFAULT 0,
    is_free_preview TINYINT(1) DEFAULT 0,
    title_override VARCHAR(255) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_collection_object (collection_id, object_id, object_type),
    KEY idx_collection_id (collection_id),
    KEY idx_object_lookup (object_id, object_type)
) {$charset_collate};

-- 合集购买/解锁记录
CREATE TABLE {$wpdb->prefix}slv_collection_access (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    collection_id BIGINT(20) NOT NULL,
    user_id BIGINT(20) NOT NULL,
    access_type VARCHAR(20) NOT NULL,
    order_id BIGINT(20) DEFAULT 0,
    granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_collection_user (collection_id, user_id),
    KEY idx_user_id (user_id)
) {$charset_collate};

-- 合集阅读进度
CREATE TABLE {$wpdb->prefix}slv_collection_progress (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) NOT NULL,
    collection_id BIGINT(20) NOT NULL,
    items_read INT DEFAULT 0,
    last_item_id BIGINT(20) DEFAULT 0,
    progress DECIMAL(5,2) DEFAULT 0,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_user_collection (user_id, collection_id),
    KEY idx_user_id (user_id)
) {$charset_collate};
```

### 3.2.5 关系类表

```sql
-- 内容-商品关系+转化
CREATE TABLE {$wpdb->prefix}slv_content_product (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    content_id BIGINT(20) NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    product_id BIGINT(20) NOT NULL,
    referrer_id BIGINT(20) NOT NULL,
    position INT DEFAULT 0,
    context VARCHAR(50) DEFAULT 'inline',
    clicks BIGINT(20) DEFAULT 0,
    conversions BIGINT(20) DEFAULT 0,
    revenue DECIMAL(12,2) DEFAULT 0,
    commission DECIMAL(12,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_content_product_referrer (content_id, content_type, product_id, referrer_id),
    KEY idx_product_id (product_id),
    KEY idx_referrer_id (referrer_id)
) {$charset_collate};

-- 数字内容资产
CREATE TABLE {$wpdb->prefix}slv_digital_assets (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    post_id BIGINT(20) NOT NULL,
    asset_type VARCHAR(50) NOT NULL,
    asset_format VARCHAR(20) NOT NULL,
    file_path VARCHAR(500) DEFAULT '',
    file_size BIGINT(20) DEFAULT 0,
    page_count INT DEFAULT 0,
    duration INT DEFAULT 0,
    is_free TINYINT(1) DEFAULT 0,
    price DECIMAL(12,2) DEFAULT 0,
    member_only TINYINT(1) DEFAULT 0,
    points_cost INT DEFAULT 0,
    preview_type VARCHAR(20) DEFAULT '',
    preview_count INT DEFAULT 0,
    download_limit INT DEFAULT 0,
    expiry_days INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_post_id (post_id),
    KEY idx_asset_type (asset_type),
    KEY idx_is_free (is_free)
) {$charset_collate};

-- 学习/播放进度
CREATE TABLE {$wpdb->prefix}slv_learning_progress (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) NOT NULL,
    asset_id BIGINT(20) NOT NULL,
    chapter_id BIGINT(20) DEFAULT 0,
    progress DECIMAL(5,2) DEFAULT 0,
    position INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'not_started',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_user_asset_chapter (user_id, asset_id, chapter_id),
    KEY idx_user_id (user_id),
    KEY idx_asset_id (asset_id)
) {$charset_collate};

-- 关注关系
CREATE TABLE {$wpdb->prefix}slv_user_follows (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    follower_id BIGINT(20) NOT NULL,
    following_id BIGINT(20) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_follow_pair (follower_id, following_id),
    KEY idx_following_id (following_id)
) {$charset_collate};

-- 圈子成员
CREATE TABLE {$wpdb->prefix}slv_group_members (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    group_id BIGINT(20) NOT NULL,
    user_id BIGINT(20) NOT NULL,
    role VARCHAR(20) DEFAULT 'member',
    status VARCHAR(20) DEFAULT 'active',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_group_user (group_id, user_id),
    KEY idx_user_id (user_id)
) {$charset_collate};

-- 阅读统计（新规划独立设计，非迁移）
CREATE TABLE {$wpdb->prefix}slv_post_stats (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    post_id BIGINT(20) NOT NULL,
    p25 BIGINT(20) DEFAULT 0,
    p50 BIGINT(20) DEFAULT 0,
    p75 BIGINT(20) DEFAULT 0,
    p100 BIGINT(20) DEFAULT 0,
    avg_scroll DECIMAL(5,2) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_post_id (post_id)
) {$charset_collate};

-- 阅读日志（新规划独立设计，非迁移）
CREATE TABLE {$wpdb->prefix}slv_track_log (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    post_id BIGINT(20) NOT NULL,
    user_id BIGINT(20) DEFAULT 0,
    session_id VARCHAR(64) DEFAULT '',
    scroll_depth INT DEFAULT 0,
    time_spent INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_post_id (post_id),
    KEY idx_user_id (user_id),
    KEY idx_created_at (created_at)
) {$charset_collate};
```

### 3.2.6 多站点与同步类表

```sql
-- 城市配置（网络级）
CREATE TABLE {$wpdb->base_prefix}slv_city_config (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    blog_id BIGINT(20) NOT NULL,
    parent_region_id BIGINT(20) DEFAULT 0,
    city_name VARCHAR(100) NOT NULL,
    city_slug VARCHAR(100) NOT NULL,
    country_code VARCHAR(5) NOT NULL,
    language VARCHAR(10) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    timezone VARCHAR(50) DEFAULT '',
    geo_lat DECIMAL(10,7) DEFAULT 0,
    geo_lng DECIMAL(10,7) DEFAULT 0,
    poi_source VARCHAR(50) DEFAULT '',
    station_master_id BIGINT(20) DEFAULT 0,
    commission_rate DECIMAL(5,2) DEFAULT 20.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_blog_city (blog_id),
    KEY idx_parent_region (parent_region_id),
    KEY idx_country_code (country_code),
    KEY idx_language (language)
) {$charset_collate};

-- 字段同步规则
CREATE TABLE {$wpdb->prefix}slv_field_sync_rules (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    field_key VARCHAR(100) NOT NULL,
    sync_type VARCHAR(20) DEFAULT 'full',
    sync_mode VARCHAR(20) DEFAULT 'state',
    seo_behavior VARCHAR(50) DEFAULT '',
    owner_type VARCHAR(20) DEFAULT 'platform',
    owner_id BIGINT(20) DEFAULT 0,
    priority INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_field_owner (field_key, owner_type, owner_id)
) {$charset_collate};

-- 同步队列
CREATE TABLE {$wpdb->prefix}slv_sync_queue (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    source_blog_id BIGINT(20) NOT NULL,
    target_blog_id BIGINT(20) NOT NULL,
    post_id BIGINT(20) NOT NULL,
    sync_mode VARCHAR(20) NOT NULL,
    payload TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    priority INT DEFAULT 0,
    retry_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_status_priority (status, priority),
    KEY idx_target_blog (target_blog_id)
) {$charset_collate};
```

### 3.2.7 BYOK配置类表

```sql
-- 服务配置
CREATE TABLE {$wpdb->prefix}slv_service_configs (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    owner_type VARCHAR(20) NOT NULL,
    owner_id BIGINT(20) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    config_key VARCHAR(100) NOT NULL,
    config_value TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    priority INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_owner_service_key (owner_type, owner_id, service_type, provider, config_key),
    KEY idx_owner_lookup (owner_type, owner_id, service_type)
) {$charset_collate};

-- 服务调用日志
CREATE TABLE {$wpdb->prefix}slv_service_logs (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    owner_type VARCHAR(20) NOT NULL,
    owner_id BIGINT(20) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL,
    tokens_used INT DEFAULT 0,
    cost DECIMAL(10,4) DEFAULT 0,
    latency_ms INT DEFAULT 0,
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_owner_lookup (owner_type, owner_id, service_type),
    KEY idx_created_at (created_at)
) {$charset_collate};
```

### 3.2.8 完整表清单（60+张）

| # | 表名 | 用途 | 来源 |
|---|---|---|---|
| **电商引擎** | | | |
| 1 | slv_products | 商品主表 | 新规划 |
| 2 | slv_product_variants | 商品变体 | 新规划 |
| 3 | slv_product_categories | 商品分类关系 | 新规划 |
| 4 | slv_product_images | 商品图片关系 | 新规划 |
| 5 | slv_carts | 购物车 | 新规划 |
| 6 | slv_cart_items | 购物车项 | 新规划 |
| 7 | slv_orders | 订单 | 新规划 |
| 8 | slv_order_items | 订单项 | 新规划 |
| 9 | slv_order_status_history | 订单状态历史 | 新规划 |
| 10 | slv_payments | 支付记录 | 新规划 |
| 11 | slv_refunds | 退款记录 | 新规划 |
| 12 | slv_inventory_logs | 库存日志 | 新规划 |
| 13 | slv_tax_rates | 税率 | 新规划 |
| 14 | slv_shipping_zones | 运费区域 | 新规划 |
| 15 | slv_shipping_methods | 运费方式 | 新规划 |
| 16 | slv_coupons | 优惠券 | 新规划 |
| 17 | slv_coupon_usages | 优惠券使用 | 新规划 |
| 18 | slv_currencies | 货币配置 | 新规划 |
| **合集系统** | | | |
| 19 | slv_collections | 合集主表 | 新规划 |
| 20 | slv_collection_items | 合集内容项 | 新规划 |
| 21 | slv_collection_access | 合集访问权限 | 新规划 |
| 22 | slv_collection_progress | 合集阅读进度 | 新规划 |
| **配置** | | | |
| 23 | slv_member_levels | 会员等级 | 新规划 |
| 24 | slv_config_registry | 统一配置 | 新规划 |
| 25 | slv_config_permissions | 配置权限 | 新规划 |
| 26 | slv_config_audit_log | 配置审计 | 新规划 |
| 27 | slv_field_definitions | 字段定义 | 新规划 |
| 28 | slv_notification_rules | 通知规则 | 新规划 |
| 29 | slv_email_templates | 邮件模板 | 新规划 |
| 30 | slv_schema_mappings | Schema映射 | 新规划 |
| **记录** | | | |
| 31 | slv_points_log | 积分流水 | 新规划 |
| 32 | slv_points_rules | 积分规则 | 新规划 |
| 33 | slv_vendor_earnings | 商户收益 | 新规划 |
| 34 | slv_withdrawals | 提现 | 新规划 |
| 35 | slv_subscriptions | 订阅 | 新规划 |
| 36 | slv_subscription_plans | 订阅计划 | 新规划 |
| 37 | slv_tips | 打赏 | 新规划 |
| 38 | slv_crowdfunding | 众筹 | 新规划 |
| 39 | slv_live_sessions | 直播 | 新规划 |
| 40 | slv_campaigns | 训练营 | 新规划 |
| 41 | slv_checkins | 打卡 | 新规划 |
| 42 | slv_consultations | 咨询 | 新规划 |
| 43 | slv_exams | 考试 | 新规划 |
| 44 | slv_exam_records | 考试成绩 | 新规划 |
| 45 | slv_events | 活动 | 新规划 |
| 46 | slv_event_registrations | 活动报名 | 新规划 |
| 47 | slv_api_keys | API密钥 | 新规划 |
| **关系** | | | |
| 48 | slv_content_product | 内容-商品 | 新规划 |
| 49 | slv_digital_assets | 数字资产 | 新规划 |
| 50 | slv_learning_progress | 学习进度 | 新规划 |
| 51 | slv_user_follows | 关注 | 新规划 |
| 52 | slv_group_members | 圈子成员 | 新规划 |
| 53 | slv_social_interactions | 社交互动 | 新规划 |
| 54 | slv_knowledge_relations | 知识图谱 | 新规划 |
| **多站点** | | | |
| 55 | slv_city_config | 城市配置 | 新规划 |
| 56 | slv_field_sync_rules | 同步规则 | 新规划 |
| 57 | slv_sync_queue | 同步队列 | 新规划 |
| **BYOK** | | | |
| 58 | slv_service_configs | 服务配置 | 新规划 |
| 59 | slv_service_logs | 服务日志 | 新规划 |
| **统计（新规划独立设计）** | | | |
| 60 | slv_post_stats | 阅读统计 | 新规划独立设计 |
| 61 | slv_post_daily | 每日统计 | 新规划独立设计 |
| 62 | slv_track_log | 阅读日志 | 新规划独立设计 |
| **仓库** | | | |
| 63 | slv_product_warehouses | 仓库 | 新规划 |
| 64 | slv_warehouse_items | 仓库产品 | 新规划 |
| 65 | slv_warehouse_applications | 入驻申请 | 新规划 |
| 66 | slv_listing_rules | 上架规则 | 新规划 |
| 67 | slv_store_profiles | 独立页 | 新规划 |


# 第四部分：自研电商引擎

## 4.1 核心服务

### 4.1.1 定价引擎

```php
namespace SunLyvo\Nexus\Commerce\Pricing;

class PriceEngine {
    public function get_price( int $product_id, int $user_id = 0, int $quantity = 1 ): float {
        $product = $this->product_repo->find( $product_id );
        if ( ! $product ) return 0.0;
        
        $price = (float) $product->price;
        
        // 1. 角色定价（B2B批发价）
        if ( $user_id ) {
            $price = $this->apply_role_price( $price, $product, $user_id );
        }
        
        // 2. 阶梯定价
        $price = $this->apply_tier_price( $price, $product, $quantity );
        
        // 3. 会员折扣
        $price = $this->apply_membership_discount( $price, $user_id );
        
        // 4. 多币种换算
        $price = $this->apply_currency( $price, $product->currency );
        
        return apply_filters( 'slv_product_price', $price, $product_id, $user_id, $quantity );
    }
    
    private function apply_role_price( float $price, $product, int $user_id ): float {
        $user = get_userdata( $user_id );
        if ( ! $user ) return $price;
        
        $roles = (array) $user->roles;
        $b2b_roles = [ 'wholesale_customer', 'company_admin', 'company_buyer' ];
        
        if ( array_intersect( $roles, $b2b_roles ) && $product->wholesale_price > 0 ) {
            return (float) $product->wholesale_price;
        }
        
        return $price;
    }
}
```

### 4.1.2 购物车服务

```php
namespace SunLyvo\Nexus\Commerce\Cart;

class CartService {
    public function add_item( string $cart_key, int $product_id, int $quantity = 1, array $options = [] ): array {
        $cart = $this->cart_repo->find_by_key( $cart_key );
        if ( ! $cart ) {
            $cart = $this->cart_repo->create( $cart_key );
        }
        
        $product = $this->product_repo->find( $product_id );
        if ( ! $product || $product->status !== 'publish' ) {
            return [ 'success' => false, 'error' => '商品不存在' ];
        }
        
        // 库存检查
        if ( $product->manage_stock && $product->stock_quantity < $quantity ) {
            return [ 'success' => false, 'error' => '库存不足' ];
        }
        
        // MOQ检查
        if ( $quantity < $product->moq ) {
            return [ 'success' => false, 'error' => "最小起订量: {$product->moq}" ];
        }
        
        $unit_price = $this->price_engine->get_price( $product_id, get_current_user_id(), $quantity );
        
        $this->cart_repo->add_item( $cart->id, [
            'product_id'   => $product_id,
            'variant_id'   => $options['variant_id'] ?? 0,
            'vendor_id'    => $product->vendor_id,
            'quantity'     => $quantity,
            'unit_price'   => $unit_price,
            'subtotal'     => $unit_price * $quantity,
            'referrer_id'  => $options['referrer_id'] ?? 0,
            'content_id'   => $options['content_id'] ?? 0,
            'content_type' => $options['content_type'] ?? '',
        ] );
        
        $this->recalculate( $cart->id );
        
        return [ 'success' => true, 'cart' => $this->get_cart( $cart_key ) ];
    }
}
```

### 4.1.3 结账服务

```php
namespace SunLyvo\Nexus\Commerce\Application;

class CheckoutService {
    public function create_order( string $cart_key, array $data ): array {
        $cart = $this->cart_repo->find_by_key( $cart_key );
        if ( ! $cart || $cart->status !== 'active' ) {
            return [ 'success' => false, 'error' => '购物车无效' ];
        }
        
        global $wpdb;
        $wpdb->query( 'START TRANSACTION' );
        
        try {
            $order_id = $this->order_repo->create( [
                'order_number'     => $this->generate_order_number(),
                'user_id'          => get_current_user_id(),
                'customer_email'   => $data['email'],
                'customer_name'    => $data['name'],
                'status'           => 'pending',
                'currency'         => $cart->currency,
                'subtotal'         => $cart->subtotal,
                'discount_total'   => $cart->discount_total,
                'tax_total'        => $cart->tax_total,
                'shipping_total'   => $cart->shipping_total,
                'total'            => $cart->total,
                'payment_method'   => $data['payment_method'],
                'shipping_address' => wp_json_encode( $data['shipping_address'] ),
                'billing_address'  => wp_json_encode( $data['billing_address'] ),
                'station_id'       => get_current_blog_id(),
                'ip_address'       => $_SERVER['REMOTE_ADDR'] ?? '',
            ] );
            
            $items = $this->cart_repo->get_items( $cart->id );
            foreach ( $items as $item ) {
                $commission = $this->commission_engine->calculate( $item );
                
                $this->order_repo->add_item( $order_id, [
                    'product_id'        => $item->product_id,
                    'variant_id'        => $item->variant_id,
                    'vendor_id'         => $item->vendor_id,
                    'product_name'      => get_product_name( $item->product_id ),
                    'quantity'          => $item->quantity,
                    'unit_price'        => $item->unit_price,
                    'subtotal'          => $item->subtotal,
                    'discount'          => $item->discount,
                    'tax'               => $item->tax,
                    'total'             => $item->total,
                    'commission_rate'   => $commission['rate'],
                    'commission_amount' => $commission['amount'],
                    'vendor_earning'    => $commission['vendor'],
                    'platform_earning'  => $commission['platform'],
                    'referrer_earning'  => $commission['referrer'],
                ] );
            }
            
            foreach ( $items as $item ) {
                $this->inventory_service->decrease( $item->product_id, $item->quantity, $order_id );
            }
            
            $this->cart_repo->update_status( $cart->id, 'converted' );
            
            $wpdb->query( 'COMMIT' );
            
            do_action( 'slv_order_created', $order_id );
            
            return [ 'success' => true, 'order_id' => $order_id ];
            
        } catch ( \Exception $e ) {
            $wpdb->query( 'ROLLBACK' );
            return [ 'success' => false, 'error' => $e->getMessage() ];
        }
    }
}
```

### 4.1.4 支付网关

```php
namespace SunLyvo\Nexus\Commerce\Payment;

interface PaymentGateway {
    public function create_payment( int $order_id, array $data ): array;
    public function capture_payment( string $transaction_id ): array;
    public function refund_payment( string $transaction_id, float $amount ): array;
    public function handle_webhook( array $payload ): void;
}

class StripeGateway implements PaymentGateway {
    private string $api_key;
    
    public function __construct() {
        $this->api_key = slv_get_config( 'stripe_secret_key' );
    }
    
    public function create_payment( int $order_id, array $data ): array {
        $order = $this->order_repo->find( $order_id );
        \Stripe\Stripe::setApiKey( $this->api_key );
        
        try {
            $intent = \Stripe\PaymentIntent::create( [
                'amount'   => round( $order->total * 100 ),
                'currency' => strtolower( $order->currency ),
                'metadata' => [
                    'order_id'     => $order_id,
                    'order_number' => $order->order_number,
                ],
            ] );
            
            $this->payment_repo->create( [
                'order_id'       => $order_id,
                'payment_method' => 'card',
                'gateway'        => 'stripe',
                'transaction_id' => $intent->id,
                'amount'         => $order->total,
                'currency'       => $order->currency,
                'status'         => 'pending',
            ] );
            
            return [
                'success'        => true,
                'client_secret'  => $intent->client_secret,
                'transaction_id' => $intent->id,
            ];
        } catch ( \Exception $e ) {
            return [ 'success' => false, 'error' => $e->getMessage() ];
        }
    }
}
```

## 4.2 REST API

```
POST   /wp-json/slv/v1/cart                    创建购物车
GET    /wp-json/slv/v1/cart/{key}              获取购物车
POST   /wp-json/slv/v1/cart/{key}/items        添加商品
PUT    /wp-json/slv/v1/cart/{key}/items/{id}   更新商品
DELETE /wp-json/slv/v1/cart/{key}/items/{id}   删除商品
POST   /wp-json/slv/v1/cart/{key}/coupon       应用优惠券

POST   /wp-json/slv/v1/checkout                创建结账会话
POST   /wp-json/slv/v1/checkout/{id}/complete  完成结账
POST   /wp-json/slv/v1/checkout/{id}/cancel    取消结账

GET    /wp-json/slv/v1/products                商品列表
GET    /wp-json/slv/v1/products/{id}           商品详情

GET    /wp-json/slv/v1/orders                  订单列表
GET    /wp-json/slv/v1/orders/{id}             订单详情
POST   /wp-json/slv/v1/orders/{id}/refund      申请退款

POST   /wp-json/slv/v1/acp/checkout_sessions   ACP协议端点
```


# 第五部分：合集系统

## 5.1 合集类型

| collection_type | 说明 | 内容项类型 |
|---|---|---|
| article_series | 文章合集 | post |
| video_course | 视频课程合集 | video |
| mixed_knowledge | 混合知识合集 | post + video + document |
| document_pack | 文档资料包 | document |
| publication | 正式出版物 | 仅平台自营 |

## 5.2 合集服务

```php
namespace SunLyvo\Nexus\Collections\Application;

class CollectionService {
    public function can_access( int $collection_id, int $user_id = 0 ): bool {
        $collection = $this->repo->find( $collection_id );
        if ( ! $collection ) return false;
        
        // 免费合集
        if ( $collection->is_free ) return true;
        
        if ( ! $user_id ) return false;
        
        // 已购买
        $access = $this->access_repo->find( $collection_id, $user_id );
        if ( $access && ( ! $access->expires_at || strtotime( $access->expires_at ) > time() ) ) {
            return true;
        }
        
        // 会员专属
        if ( $collection->member_only ) {
            $level = get_user_meta( $user_id, '_slv_membership_level', true );
            return in_array( $level, [ 'silver', 'gold', 'diamond' ], true );
        }
        
        return false;
    }
    
    public function can_access_item( int $collection_id, int $item_id, int $user_id = 0 ): bool {
        if ( $this->can_access( $collection_id, $user_id ) ) {
            return true;
        }
        
        $item = $this->item_repo->find( $collection_id, $item_id );
        return $item && $item->is_free_preview;
    }
    
    public function grant_access( int $collection_id, int $user_id, string $type, int $order_id = 0 ): void {
        $this->access_repo->create( [
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'access_type'   => $type,
            'order_id'      => $order_id,
            'granted_at'    => current_time( 'mysql' ),
        ] );
        
        do_action( 'slv_collection_access_granted', $collection_id, $user_id, $type );
    }
    
    public function update_progress( int $collection_id, int $user_id, int $item_id ): void {
        $progress = $this->progress_repo->find( $user_id, $collection_id );
        
        if ( ! $progress ) {
            $this->progress_repo->create( [
                'user_id'       => $user_id,
                'collection_id' => $collection_id,
                'items_read'    => 1,
                'last_item_id'  => $item_id,
                'started_at'    => current_time( 'mysql' ),
            ] );
            return;
        }
        
        $items = $this->item_repo->get_items( $collection_id );
        $read_items = $this->progress_repo->get_read_items( $user_id, $collection_id );
        $read_items[] = $item_id;
        $read_items = array_unique( $read_items );
        
        $new_progress = count( $read_items ) / count( $items ) * 100;
        
        $this->progress_repo->update( $progress->id, [
            'items_read'   => count( $read_items ),
            'last_item_id' => $item_id,
            'progress'     => min( 100, $new_progress ),
        ] );
    }
}
```


# 第六部分：模块全景

## 6.1 20个功能域

| # | 功能域 | 核心内容 | 阶段 |
|---|---|---|---|
| D01 | 基础设施 | 角色、60+张表、安全、配置中心 | 一 |
| D02 | 用户体系 | 多角色、注册登录、用户中心 | 一 |
| D03 | 会员积分 | 等级、折扣、积分赚取消耗 | 一 |
| D04 | 内容基础 | 博客、页面、分类、标签 | 一 |
| D05 | 自研电商 | 商品、购物车、订单、支付、库存 | 一 |
| D06 | 角色定价 | B2B/B2C价格、批发价、阶梯价 | 一 |
| D07 | 询盘系统 | 询盘表单、CPT、邮件通知 | 一 |
| D08 | 数字资产底座 | 统一资产表、权限、进度 | 二 |
| D09 | 合集系统 | 文章合集、视频课程、资料包 | 二 |
| D10 | 分销体系 | 追踪、佣金、收益、提现 | 二 |
| D11 | 内容-商品 | 商品卡片、嵌入、转化追踪 | 二 |
| D12 | 多商户 | 入驻、隔离、订单拆分、店铺 | 三 |
| D13 | B2B企业 | 企业账户、子账户、采购限额 | 三 |
| D14 | 社区互动 | 论坛、圈子、朋友圈、社交 | 三 |
| D15 | 知识进阶 | VOD、课程、音频、百科、FAQ | 四 |
| D16 | 流量引擎 | SEO、GEO、AEO、流量归因 | 四 |
| D17 | 多语言 | 自研、多币种、hreflang | 五 |
| D18 | 多站点+城市分站 | Multisite、三级网络、站长体系 | 五 |
| D19 | 进阶模块 | 直播、训练营、咨询、认证、活动 | 六 |
| D20 | API与AI代理 | REST API、Webhook、ACP接入 | 六 |

## 6.2 内容类型全景（18种CPT）

| # | CPT | 用途 | Schema |
|---|---|---|---|
| 1 | post | 博客 | Article |
| 2 | page | 页面 | WebPage |
| 3 | product | 商品 | Product |
| 4 | service | 服务 | Service |
| 5 | collection | 合集 | Collection |
| 6 | chapter | 章节 | Chapter |
| 7 | document | 文库 | DigitalDocument |
| 8 | video | VOD | VideoObject |
| 9 | audio | 音频 | AudioObject |
| 10 | course | 课程 | Course |
| 11 | live | 直播 | BroadcastEvent |
| 12 | topic | 论坛话题 | DiscussionForumPosting |
| 13 | reply | 论坛回复 | Comment |
| 14 | group | 圈子 | Group |
| 15 | group_post | 圈子动态 | SocialMediaPosting |
| 16 | moment | 朋友圈 | SocialMediaPosting |
| 17 | gallery | 图集 | ImageGallery |
| 18 | wiki | 百科 | DefinedTerm |
| 19 | faq | FAQ | FAQPage |
| 20 | inquiry | 询盘 | ContactPage |
| 21 | company | 企业 | Organization |
| 22 | campaign | 训练营 | Course |

## 6.3 角色体系（12+）

| 角色 | 层级 | 核心权限 |
|---|---|---|
| super_admin | 平台 | 所有权限 |
| region_admin | 区域 | 区域配置、下级管理 |
| station_master | 站点 | 站点配置、上架规则、自营仓库 |
| vendor | 商户 | 店铺管理、产品管理、订单管理 |
| vendor_staff | 商户 | 受限的店铺权限 |
| creator | 创作者 | 内容创作、带货、知识产品 |
| company_admin | 企业 | 子账户管理、采购审批 |
| company_buyer | 企业 | 下单、查看订单 |
| company_viewer | 企业 | 只读 |
| wholesale_customer | B2B | 批发价、询盘 |
| pending_wholesale | B2B | 待审批 |
| customer | B2C | 普通购买 |


# 第七部分：代码骨架

## 7.1 目录结构

```
sunlyvo-nexus/
├── style.css
├── functions.php                   # 只做require_once，≤100行
├── index.php
├── header.php
├── footer.php
├── screenshot.png
│
├── inc/
│   ├── constants.php
│   ├── schema.php                  # 60+张表创建脚本
│   ├── roles.php
│   ├── permissions.php
│   ├── config.php
│   │
│   ├── presentation/               # L5 表现层
│   ├── application/                # L4 应用层
│   ├── domain/                     # L3 领域层
│   ├── data/                       # L2 数据层
│   ├── infrastructure/             # L1 基础设施层
│   │
│   ├── modules/                    # 业务模块
│   │   ├── commerce/               # 自研电商引擎
│   │   │   ├── module.php
│   │   │   ├── domain/
│   │   │   │   ├── Product/
│   │   │   │   ├── Cart/
│   │   │   │   ├── Order/
│   │   │   │   ├── Payment/
│   │   │   │   ├── Pricing/
│   │   │   │   ├── Inventory/
│   │   │   │   ├── Tax/
│   │   │   │   ├── Shipping/
│   │   │   │   └── Discount/
│   │   │   ├── application/
│   │   │   │   ├── CheckoutService.php
│   │   │   │   ├── OrderFulfillmentService.php
│   │   │   │   └── RefundService.php
│   │   │   ├── presentation/
│   │   │   │   ├── rest/
│   │   │   │   └── blocks/
│   │   │   └── infrastructure/
│   │   │       └── migrations/
│   │   ├── collections/            # 合集系统
│   │   ├── membership/
│   │   ├── points/
│   │   ├── affiliate/
│   │   ├── warehouse/
│   │   ├── store-profile/
│   │   ├── sync/
│   │   ├── seo/
│   │   ├── ai/
│   │   ├── acp/
│   │   ├── analytics/              # 阅读统计系统（新规划独立设计）
│   │   ├── comments/               # 评论系统（新规划独立设计）
│   │   ├── security/
│   │   └── performance/
│   │
│   ├── cpt.php
│   ├── taxonomy.php
│   ├── enqueue.php
│   └── helpers.php
│
├── templates/                      # 区块模板（新规划独立构建）
├── parts/                          # 区块部件（新规划独立构建）
├── patterns/                       # 区块模式（新规划独立构建）
├── theme.json                      # 设计Token（新规划为基线重新设计）
├── template-parts/
├── page-templates/
├── assets/
├── languages/
├── src/                            # React前端源码
│   ├── pages/
│   ├── components/
│   ├── services/
│   ├── models/
│   ├── hooks/
│   └── utils/
└── tests/
```

## 7.2 functions.php 骨架

```php
<?php
/**
 * SunLyvo Nexus 主题入口
 *
 * @package SunLyvo_Nexus
 * @since 1.0.0
 * @author 李咏燊 <getthink-info>
 */

declare( strict_types=1 );

require_once get_template_directory() . '/inc/constants.php';

// 核心基础设施
require_once get_template_directory() . '/inc/schema.php';
require_once get_template_directory() . '/inc/roles.php';
require_once get_template_directory() . '/inc/permissions.php';
require_once get_template_directory() . '/inc/config.php';
require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/taxonomy.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/helpers.php';

// 业务模块
$modules = [
    'commerce',      // 自研电商引擎
    'collections',   // 合集系统
    'membership',
    'points',
    'affiliate',
    'warehouse',
    'store-profile',
    'sync',
    'seo',
    'ai',
    'acp',
    'analytics',     // 阅读统计系统
    'comments',      // 评论系统
    'security',
    'performance',
];

foreach ( $modules as $module ) {
    $module_file = get_template_directory() . "/inc/modules/{$module}/module.php";
    if ( file_exists( $module_file ) ) {
        require_once $module_file;
    }
}
```

## 7.3 数据库迁移骨架

```php
<?php
/**
 * 数据库表创建与迁移
 *
 * @package SunLyvo_Nexus
 * @author 李咏燊 <getthink-info>
 */

declare( strict_types=1 );

function slv_create_tables(): void {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
    // 按3.2节定义创建所有表
    
    update_option( 'slv_db_version', SLV_DB_VERSION );
}

function slv_migrate_db(): void {
    $current_version = get_option( 'slv_db_version', '0' );
    
    if ( version_compare( $current_version, '1.0.0', '<' ) ) {
        slv_create_tables();
    }
}

add_action( 'after_switch_theme', 'slv_migrate_db' );
```


# 第八部分：开发规范

## 8.1 命名规范

| 类型 | 前缀 | 示例 |
|---|---|---|
| 函数 | `slv_` | `slv_user_can()` |
| 类 | `SLV_` | `SLV_AI_Client` |
| 常量 | `SLV_` | `SLV_VERSION` |
| 钩子 | `slv_` | `slv_content_synced` |
| 数据库表 | `{$wpdb->prefix}slv_` | `wp_slv_products` |
| Meta key | `_slv_` | `_slv_wholesale_price` |
| Option | `slv_` | `slv_default_commission_rate` |
| CSS类 | `slv-` | `.slv-product-card` |
| 文本域 | `sunlyvo-nexus` | `__( '文本', 'sunlyvo-nexus' )` |
| REST端点 | `slv/v1` | `/wp-json/slv/v1/...` |

## 8.2 安全规范（强制）

| # | 规则 | 说明 |
|---|---|---|
| 1 | 所有输出必须转义 | `esc_html()` / `esc_url()` / `esc_attr()` |
| 2 | 所有输入必须验证 | `sanitize_*()` + `wp_unslash()` |
| 3 | 所有表单必须加nonce | `wp_nonce_field()` + `wp_verify_nonce()` |
| 4 | 所有操作必须检查权限 | `current_user_can()` / `slv_user_can()` |
| 5 | 所有SQL必须预处理 | `$wpdb->prepare()` |
| 6 | 私有文件必须保护 | `.htaccess` + 流式输出 |
| 7 | API Key必须加密 | AES-256-CBC + 环境变量 |
| 8 | 禁止硬编码API Key | 环境变量或加密存储 |
| 9 | 禁止eval/extract | 无替代，禁止 |
| 10 | 禁止直接操作$_POST/$_GET | 必须sanitize |

## 8.3 配置驱动规范（强制）

| # | 规则 | 说明 |
|---|---|---|
| 1 | 禁止硬编码业务值 | 会员等级、佣金、积分必须可配置 |
| 2 | 禁止硬编码枚举 | 所有枚举值从数据库读取 |
| 3 | 禁止硬编码字段 | 所有字段通过`slv_field_definitions`定义 |
| 4 | 禁止硬编码规则 | 所有规则通过配置表定义 |
| 5 | 配置必须有默认值 | 平台级必须提供默认配置 |
| 6 | 配置必须有权限 | 每项配置必须定义谁能修改 |
| 7 | 配置必须可继承 | 下级未配置时继承上级 |
| 8 | 配置必须可锁定 | 上级可锁定下级不可覆盖 |
| 9 | 配置必须可审计 | 所有配置变更记录日志 |
| 10 | 配置必须可导出 | 支持导出为JSON备份 |

## 8.4 性能规范（强制）

| # | 规则 | 说明 |
|---|---|---|
| 1 | 禁止在循环中查询数据库 | 批量查询后循环 |
| 2 | 禁止使用`posts_per_page => -1` | 必须分页 |
| 3 | 禁止在循环中`switch_to_blog()` | 聚合表+异步同步 |
| 4 | 所有高频查询必须缓存 | `wp_cache_get/set` |
| 5 | 所有自定义表必须加索引 | 外键、状态、时间字段 |
| 6 | 资源必须用`wp_enqueue` | 禁止硬编码`<link>`/`<script>` |
| 7 | 脚本必须defer/delay | 使用`strategy`参数 |
| 8 | 图片必须懒加载 | `loading="lazy"` |
| 9 | 必须支持`_fields`参数 | REST API只返回需要字段 |
| 10 | 必须监控慢查询 | 超过1秒必须优化 |

## 8.5 Ant Design规范（强制）

| # | 规则 | 说明 |
|---|---|---|
| 1 | 统一使用Ant Design组件 | 禁止自造重复组件 |
| 2 | 统一使用Design Token | 禁止硬编码颜色、间距 |
| 3 | 统一使用ProComponents | 中后台表格表单用Pro组件 |
| 4 | 统一使用Ant Design X | AI界面用X组件 |
| 5 | 统一使用Ant Motion | 动效遵循Ant Motion规范 |
| 6 | 统一使用Ant Design Icons | 禁止使用其他图标库 |
| 7 | 统一使用ProLayout | 中后台布局用ProLayout |
| 8 | 统一国际化 | 使用Ant Design Pro i18n |

## 8.6 区块主题构建规范（强制）

| # | 规则 | 说明 |
|---|---|---|
| 1 | theme.json为设计Token唯一来源 | 禁止在CSS中硬编码颜色、间距、字体 |
| 2 | 区块模板用.html文件 | 优先使用.html模板文件，非PHP模板 |
| 3 | 区块模式在patterns/目录注册 | 禁止在数据库中创建不可移植的模式 |
| 4 | 模板部件在parts/目录注册 | header/footer/sidebar等 |
| 5 | 样式变体在styles/目录注册 | 支持前端风格切换 |
| 6 | 禁止直接编辑数据库中的模板 | 所有模板以文件形式存在主题中 |
| 7 | 模板必须可通过站点编辑器编辑 | 用户可自定义布局 |
| 8 | 区块模板必须支持开箱即用 | 安装后即刻呈现完整站点结构 |


# 第九部分：开发路线图

## 9.1 六阶段总览（89周）

| 阶段 | 周数 | 交付 | 验收 |
|---|---|---|---|
| 一 | 18 | 基础设施+自研电商引擎 | 能卖货 |
| 二 | 16 | 合集系统+分销 | 能分佣 |
| 三 | 13 | 多商户+B2B+社区 | 能规模化 |
| 四 | 14 | 知识进阶+流量引擎 | 能出海 |
| 五 | 16 | 多语言+多站点+城市分站 | 能扩展 |
| 六 | 12 | 进阶+AI代理 | 能复购 |

## 9.2 阶段一详细任务（第1-18周）

### 第1-4周：主题骨架 + 数据库 + 配置中心 + 设计Token + 区块模板

| # | 任务 | 产出 |
|---|---|---|
| 1 | 创建主题目录结构 | 目录骨架（含templates/、parts/、patterns/） |
| 2 | 编写`style.css` | 主题信息头 |
| 3 | 编写`functions.php` | 模块加载器 |
| 4 | 编写`inc/constants.php` | 常量定义 |
| 5 | 编写`inc/schema.php` | 60+张表创建脚本 |
| 6 | 编写`inc/config.php` | 配置中心核心函数 |
| 7 | 编写`inc/roles.php` | 12+角色注册 |
| 8 | 编写`inc/permissions.php` | 统一权限函数 |
| 9 | 编写`inc/security.php` | 安全加固 |
| 10 | **设计Token定义（theme.json）** | **以新规划为基线，独立设计颜色/字体/间距/圆角/阴影** |
| 11 | **区块模板构建（templates/）** | **首页/博客/页面/404/搜索等，基于新规划独立构建** |
| 12 | **区块部件构建（parts/）** | **header/footer/sidebar等，基于新规划独立构建** |
| 13 | **区块模式构建（patterns/）** | **Hero/Feature/Pricing/CTA等，基于新规划独立构建** |
| 14 | 阅读统计系统 | `slv_post_stats` / `slv_track_log` |
| 15 | 评论系统 | 全新构建，基于Ant Design风格 |

**验收**：主题可激活，60+张表创建成功，12+角色注册成功，配置中心可读写，Theme Check通过，**安装后即刻呈现完整站点结构与示例内容（开箱即用）** 。

### 第5-8周：用户体系 + 会员积分

| # | 任务 | 产出 |
|---|---|---|
| 1 | 注册/登录页面模板 | 区块模板 + 处理逻辑 |
| 2 | B2C快速注册 | 表单+处理逻辑 |
| 3 | B2B企业注册 | 表单+审批流程 |
| 4 | 商户入驻注册 | 表单+审批流程 |
| 5 | 用户中心页面 | 区块模板 + 处理逻辑 |
| 6 | 会员等级CRUD | 后台管理界面 |
| 7 | 会员升级逻辑 | `slv_order_completed`钩子 |
| 8 | 积分流水表 | `slv_points_log` |
| 9 | 积分统一入口 | `slv_add_points()` |
| 10 | 积分获取/消耗/过期 | 完整逻辑 |
| 11 | 会员/积分前端展示 | 用户中心标签页 |

**验收**：B2C/B2B/商户可注册；用户消费后可自动升级会员；积分可获取、可消耗、可过期。

### 第9-14周：自研电商引擎

| # | 任务 | 产出 |
|---|---|---|
| 1 | 商品领域模型 | `Product` / `ProductVariant` |
| 2 | 商品Repository | `ProductRepository` |
| 3 | 商品REST API | `ProductController` |
| 4 | 商品管理后台 | 列表、编辑、批量操作 |
| 5 | 定价引擎 | `PriceEngine` |
| 6 | 购物车领域模型 | `Cart` / `CartItem` |
| 7 | 购物车服务 | `CartService` |
| 8 | 购物车REST API | `CartController` |
| 9 | 订单领域模型 | `Order` / `OrderItem` |
| 10 | 订单服务 | `OrderService` |
| 11 | 结账服务 | `CheckoutService` |
| 12 | 支付网关 | `StripeGateway` |
| 13 | 库存服务 | `InventoryService` |
| 14 | 税率计算 | `TaxCalculator` |
| 15 | 运费计算 | `ShippingCalculator` |
| 16 | 优惠券 | `DiscountService` |
| 17 | 多币种 | `CurrencyService` |

**验收**：商品可创建、编辑、上架；购物车和结账流程正常；支付成功、订单生成；库存正确扣减。

### 第15-16周：角色定价 + 询盘

| # | 任务 | 产出 |
|---|---|---|
| 1 | 批发价字段 | 商品编辑页meta box |
| 2 | 角色定价过滤器 | `slv_product_price`钩子 |
| 3 | 阶梯价支持 | 数量区间定价 |
| 4 | 询盘CPT | `slv_inquiry` |
| 5 | 询盘表单 | 产品页嵌入 |
| 6 | 询盘处理 | `template_redirect`钩子 |
| 7 | 询盘通知 | 客户+管理员邮件 |
| 8 | 询盘后台管理 | 列表、详情、回复 |

**验收**：B2B用户看到批发价；B2C用户看到零售价；访客看到"登录查看价格"；询盘可提交、可管理、可回复。

### 第17-18周：内容基础 + 测试上线

| # | 任务 | 产出 |
|---|---|---|
| 1 | 博客区块模板 | `templates/single.html` / `templates/archive.html` |
| 2 | 页面区块模板 | `templates/page.html` |
| 3 | 分类/标签归档 | `templates/category.html` / `templates/tag.html` |
| 4 | 404区块模板 | `templates/404.html` |
| 5 | 搜索区块模板 | `templates/search.html` |
| 6 | 基础SEO | Schema输出、meta标签 |
| 7 | 性能优化 | Redis缓存、资源优化 |
| 8 | 安全加固 | 登录限流、文件保护 |
| 9 | 全链路测试 | B2C/B2B流程 |
| 10 | 上线部署 | 预发布→生产 |

**验收**：博客、页面、分类正常展示；基础SEO输出正确；性能指标达标；B2C/B2B全流程通过。


# 第十部分：验收标准

## 10.1 每阶段验收

| 阶段 | 标准 |
|---|---|
| 一 | B2C能下单支付，B2B能看批发价并询盘，安装后即刻呈现完整站点（开箱即用） |
| 二 | 合集可解锁，内容可嵌入商品，分销佣金可提现 |
| 三 | 商户能接单，企业能采购，用户能发帖建圈 |
| 四 | 视频可播放，百科被AI引用，FAQ抢占Snippet |
| 五 | 多语言正确显示，城市分站正常，hreflang正确，同步内容收录率>80% |
| 六 | 直播训练营咨询认证活动全部跑通，ACP接入完成 |

## 10.2 上线验收

| 维度 | 标准 |
|---|---|
| 性能 | LCP<2.5s，INP<200ms，CLS<0.1，TTFB<600ms |
| 安全 | Theme Check通过，WPScan无高危，GDPR合规 |
| 兼容 | Chrome/Safari/Firefox/Edge/微信 |
| 多语言 | 所有语言版本正确显示，货币自动切换 |
| 多站点 | 所有子站点正常，跨站点数据一致 |
| 支付 | Stripe全链路通过 |
| 分销 | 佣金计算准确，提现流程完整 |
| SEO/GEO/AEO | Schema全覆盖，llms.txt可访问，hreflang正确 |
| 收录率 | 同步内容收录率>80%，跨站点重复率<30% |
| **开箱即用** | **安装后即刻呈现完整站点结构与示例内容** |
| 文档 | 安装/使用/开发/API文档完整 |

## 10.3 代码质量验收

| 维度 | 标准 |
|---|---|
| PHP编码 | WordPress Coding Standards通过 |
| JS编码 | ESLint + TypeScript无错误 |
| 测试覆盖 | 领域层95%，应用层90% |
| 静态分析 | PHPStan level 8通过 |
| 安全扫描 | WPScan无高危 |
| 性能 | 慢查询<1秒 |


# 第十一部分：附录

## 11.1 快速参考卡

### 11.1.1 命名快速参考

```php
// 函数
slv_get_user_points()
slv_update_membership_level()
slv_calculate_commission()
slv_get_price()

// 类
class SLV_AI_Client {}
class SLV_Sync_Engine {}
class SLV_Service_Config {}
class SLV_Price_Engine {}

// 常量
define( 'SLV_VERSION', '1.0.0' );
define( 'SLV_DB_VERSION', '1.0.0' );

// 钩子
do_action( 'slv_membership_upgraded', $user_id, $old, $new );
apply_filters( 'slv_product_price', $price, $product_id, $user_id );

// 数据库
$wpdb->prefix . 'slv_products'
$wpdb->prefix . 'slv_orders'
get_user_meta( $user_id, '_slv_points_balance', true )
get_option( 'slv_default_commission_rate' )
```

### 11.1.2 安全快速参考

```php
// 输出转义
echo esc_html( $text );
echo esc_url( $url );
echo esc_attr( $attr );
echo wp_kses_post( $content );

// 输入验证
$name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
$email = sanitize_email( wp_unslash( $_POST['email'] ) );
$id = intval( $_POST['id'] );

// Nonce
wp_nonce_field( 'slv_action', 'slv_nonce' );
wp_verify_nonce( $_POST['slv_nonce'], 'slv_action' );

// 权限
current_user_can( 'edit_posts' );
slv_user_can( 'manage_vendor_products' );

// SQL
$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}slv_products WHERE id = %d", $product_id );
```

## 11.2 常见问题

**Q1：配置的优先级是什么？**

A：商户 → 站长 → 区域 → 平台。下级未配置时继承上级。

**Q2：如何处理跨站点查询？**

A：使用聚合表+异步同步，禁止在循环中`switch_to_blog()`。

**Q3：如何处理同步内容的SEO？**

A：canonical按同步模式动态输出：状态同步→指向主站，状态帧同步→指向分站，帧同步→无跨站canonical。

**Q4：如何选择AI模型？**

A：按任务类型路由：简单任务用Luna/Haiku，复杂任务用Sonnet/Sol。

**Q5：BYOK配置的降级策略是什么？**

A：站长/商家配置 → 平台默认 → WordPress原生 → 关闭功能。

## 11.3 关键联系点

| 类型 | 位置 |
|---|---|
| 项目根目录 | `sunlyvo-nexus/` |
| PHP业务逻辑 | `inc/` |
| React源码 | `src/` |
| 区块模板 | `templates/` |
| 区块部件 | `parts/` |
| 区块模式 | `patterns/` |
| 设计Token | `theme.json` |
| 数据库迁移 | `inc/schema.php` |
| 配置中心 | `inc/config.php` |
| 权限判断 | `inc/permissions.php` |
| 自研电商引擎 | `inc/modules/commerce/` |
| 合集系统 | `inc/modules/collections/` |
| 同步引擎 | `inc/modules/sync/` |
| API客户端 | `inc/infrastructure/api-clients/` |
| 测试 | `tests/` |
| 文档 | `docs/` |
| 开发者 | 李咏燊 |
| 开发者微信 | getthink-info |
| 执行进度 | https://github.com/1lin2049/sunlyvo-nexus |


# 文档结束

**本文档为SunLyvo Nexus完整规划文档，可自包含使用。**

**核心调整说明**：

| 调整项 | 原方案 | 新方案 |
|---|---|---|
| 区块模板 | 从已有主题迁移 | **基于新规划独立构建，不从已有主题迁移** |
| 设计Token | 从已有主题迁移并更新 | **以新规划为基线重新设计（theme.json为唯一来源）** |
| 交付标准 | 功能可用 | **开箱即用（安装后即刻呈现完整站点结构与示例内容）** |
| 已有主题 | 独立运行 | **仅保留能力参考，区块模板与设计Token不迁移** |
| 执行进度 | 未记录 | **https://github.com/1lin2049/sunlyvo-nexus** |
| 开发者信息 | 未记录 | **李咏燊（微信：getthink-info）** |

**使用方式**：
1. 将本文档粘贴到任何AI对话中
2. AI将理解项目全部上下文
3. 使用第9部分的执行模板启动具体任务
4. 使用第10部分的验收标准检查结果

**版本**：v9.0
**品牌**：SunLyvo
**项目**：SunLyvo Nexus
**开发者**：李咏燊
**开发者微信**：getthink-info
**维护者**：SunLyvo 架构组