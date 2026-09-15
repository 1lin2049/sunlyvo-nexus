<?php
/**
 * SEO 后台 Tab 内容渲染
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 渲染诊断 Tab
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_diagnostics_tab(): void {
    ?>
    <div class="slv-seo-diagnostics">
        <p class="slv-seo-diag-intro">
            <?php esc_html_e( '诊断工具帮助验证 SEO / GEO / AEO 输出是否正常。所有预览均为实时数据。', 'sunlyvo-nexus' ); ?>
        </p>

        <?php slv_seo_render_diag_status(); ?>
        <?php slv_seo_render_diag_schema(); ?>
        <?php slv_seo_render_diag_llms(); ?>
        <?php slv_seo_render_diag_robots(); ?>
    </div>
    <?php
}

/**
 * 渲染诊断状态卡片
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_diag_status(): void {
    $checks = [
        [
            'label' => esc_html__( 'Schema 输出', 'sunlyvo-nexus' ),
            'value' => '1' === (string) slv_seo_get_config( 'enable_schema', '1' ),
            'link'  => admin_url( 'admin.php?page=slv-seo&tab=basic' ),
        ],
        [
            'label' => esc_html__( 'Open Graph 输出', 'sunlyvo-nexus' ),
            'value' => '1' === (string) slv_seo_get_config( 'enable_open_graph', '1' ),
            'link'  => admin_url( 'admin.php?page=slv-seo&tab=basic' ),
        ],
        [
            'label' => esc_html__( 'Canonical 输出', 'sunlyvo-nexus' ),
            'value' => '1' === (string) slv_seo_get_config( 'enable_canonical', '1' ),
            'link'  => admin_url( 'admin.php?page=slv-seo&tab=basic' ),
        ],
        [
            'label' => esc_html__( 'llms.txt', 'sunlyvo-nexus' ),
            'value' => '1' === (string) slv_seo_get_config( 'enable_llms_txt', '1' ),
            'link'  => admin_url( 'admin.php?page=slv-seo&tab=geo' ),
        ],
        [
            'label' => esc_html__( 'llms-full.txt', 'sunlyvo-nexus' ),
            'value' => '1' === (string) slv_seo_get_config( 'enable_llms_full_txt', '1' ),
            'link'  => admin_url( 'admin.php?page=slv-seo&tab=geo' ),
        ],
        [
            'label' => esc_html__( 'AI 爬虫白名单', 'sunlyvo-nexus' ),
            'value' => '1' === (string) slv_seo_get_config( 'allow_ai_crawlers', '1' ),
            'link'  => admin_url( 'admin.php?page=slv-seo&tab=geo' ),
        ],
    ];
    ?>
    <div class="slv-seo-diag-section">
        <h2><?php esc_html_e( '功能状态', 'sunlyvo-nexus' ); ?></h2>
        <div class="slv-seo-diag-grid">
            <?php foreach ( $checks as $check ) : ?>
                <div class="slv-seo-diag-card <?php echo $check['value'] ? 'is-on' : 'is-off'; ?>">
                    <div class="slv-seo-diag-card__status">
                        <?php echo $check['value'] ? '✓' : '×'; ?>
                    </div>
                    <div class="slv-seo-diag-card__label">
                        <?php echo esc_html( $check['label'] ); ?>
                    </div>
                    <a href="<?php echo esc_url( $check['link'] ); ?>" class="slv-seo-diag-card__link">
                        <?php esc_html_e( '配置', 'sunlyvo-nexus' ); ?> →
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * 渲染 Schema 预览
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_diag_schema(): void {
    // 取最近一篇章节作为样本
    $sample = get_posts(
        [
            'post_type'      => 'slv_chapter',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        ]
    );

    $sample_url = ! empty( $sample ) ? (string) get_permalink( $sample[0] ) : home_url( '/' );
    ?>
    <div class="slv-seo-diag-section">
        <h2><?php esc_html_e( 'Schema 实时预览', 'sunlyvo-nexus' ); ?></h2>
        <p class="description">
            <?php
            printf(
                /* translators: %s: sample url */
                esc_html__( '样本页面：%s', 'sunlyvo-nexus' ),
                '<a href="' . esc_url( $sample_url ) . '" target="_blank">' . esc_html( $sample_url ) . '</a>'
            );
            ?>
        </p>

        <div class="slv-seo-diag-preview">
            <div class="slv-seo-diag-preview__actions">
                <a href="<?php echo esc_url( $sample_url ); ?>" target="_blank" class="button">
                    <?php esc_html_e( '打开样本页面', 'sunlyvo-nexus' ); ?>
                </a>
                <a href="https://validator.schema.org/#url=<?php echo esc_attr( urlencode( $sample_url ) ); ?>"
                   target="_blank" rel="noopener" class="button">
                    <?php esc_html_e( '用 Schema.org 验证', 'sunlyvo-nexus' ); ?> ↗
                </a>
                <a href="https://search.google.com/test/rich-results?url=<?php echo esc_attr( urlencode( $sample_url ) ); ?>"
                   target="_blank" rel="noopener" class="button">
                    <?php esc_html_e( 'Google 富媒体测试', 'sunlyvo-nexus' ); ?> ↗
                </a>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 渲染 llms.txt 预览
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_diag_llms(): void {
    ?>
    <div class="slv-seo-diag-section">
        <h2><?php esc_html_e( 'llms.txt / GEO 预览', 'sunlyvo-nexus' ); ?></h2>

        <div class="slv-seo-diag-preview">
            <div class="slv-seo-diag-preview__actions">
                <a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" class="button button-primary">
                    <?php esc_html_e( '打开 /llms.txt', 'sunlyvo-nexus' ); ?> ↗
                </a>
                <a href="<?php echo esc_url( home_url( '/llms-full.txt' ) ); ?>" target="_blank" class="button">
                    <?php esc_html_e( '打开 /llms-full.txt', 'sunlyvo-nexus' ); ?> ↗
                </a>
            </div>

            <?php
            $llms_content = '';

            if ( '1' === (string) slv_seo_get_config( 'enable_llms_txt', '1' ) ) {
                ob_start();
                slv_seo_render_llms_index_txt();
                $llms_content = (string) ob_get_clean();
            }

            if ( '' !== $llms_content ) :
                ?>
                <pre class="slv-seo-diag-code"><?php echo esc_html( mb_substr( $llms_content, 0, 3000 ) ); ?><?php echo mb_strlen( $llms_content ) > 3000 ? "\n\n… （已截断）" : ''; ?></pre>
                <?php
            else :
                ?>
                <p><?php esc_html_e( 'llms.txt 已禁用。', 'sunlyvo-nexus' ); ?></p>
                <?php
            endif;
            ?>
        </div>
    </div>
    <?php
}

/**
 * 渲染 robots.txt 预览
 *
 * @since 1.0.0
 * @return void
 */
function slv_seo_render_diag_robots(): void {
    $robots_content = '';

    if ( function_exists( 'slv_seo_custom_robots_txt' ) ) {
        $robots_content = slv_seo_custom_robots_txt( '', true );
    }
    ?>
    <div class="slv-seo-diag-section">
        <h2><?php esc_html_e( 'robots.txt 预览', 'sunlyvo-nexus' ); ?></h2>

        <div class="slv-seo-diag-preview">
            <div class="slv-seo-diag-preview__actions">
                <a href="<?php echo esc_url( home_url( '/robots.txt' ) ); ?>" target="_blank" class="button button-primary">
                    <?php esc_html_e( '打开 /robots.txt', 'sunlyvo-nexus' ); ?> ↗
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=slv-seo&tab=robots' ) ); ?>" class="button">
                    <?php esc_html_e( '编辑规则', 'sunlyvo-nexus' ); ?> →
                </a>
            </div>

            <pre class="slv-seo-diag-code"><?php echo esc_html( $robots_content ); ?></pre>
        </div>
    </div>
    <?php
}