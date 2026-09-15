<?php
/**
 * 全站尾部
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
    <footer id="slv-colophon" class="slv-footer" role="contentinfo">
        <div class="slv-footer__inner">

            <!-- 顶部：四列 -->
            <div class="slv-footer__grid">

                <!-- 品牌列 -->
                <div class="slv-footer__column slv-footer__column--brand">
                    <a class="slv-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                    <?php
                    $slv_tagline = (string) get_bloginfo( 'description' );
                    if ( '' !== $slv_tagline ) :
                        ?>
                        <p class="slv-footer__tagline"><?php echo esc_html( $slv_tagline ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- 页脚菜单 1 -->
                <?php if ( has_nav_menu( 'footer' ) ) : ?>
                    <div class="slv-footer__column">
                        <h3 class="slv-footer__heading"><?php esc_html_e( '导航', 'sunlyvo-nexus' ); ?></h3>
                        <?php
                        wp_nav_menu(
                            [
                                'theme_location' => 'footer',
                                'menu_class'     => 'slv-footer__menu',
                                'container'      => false,
                                'depth'          => 1,
                                'fallback_cb'    => false,
                            ]
                        );
                        ?>
                    </div>
                <?php endif; ?>

                <!-- 快速链接 -->
                <div class="slv-footer__column">
                    <h3 class="slv-footer__heading"><?php esc_html_e( '快速入口', 'sunlyvo-nexus' ); ?></h3>
                    <ul class="slv-footer__menu">
                        <li><a href="<?php echo esc_url( home_url( '/collections/' ) ); ?>"><?php esc_html_e( '全部合集', 'sunlyvo-nexus' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/cart/' ) ); ?>"><?php esc_html_e( '购物车', 'sunlyvo-nexus' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/my-orders/' ) ); ?>"><?php esc_html_e( '我的订单', 'sunlyvo-nexus' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/account/' ) ); ?>"><?php esc_html_e( '用户中心', 'sunlyvo-nexus' ); ?></a></li>
                    </ul>
                </div>

                <!-- 订阅 -->
                <div class="slv-footer__column">
                    <h3 class="slv-footer__heading"><?php esc_html_e( '订阅更新', 'sunlyvo-nexus' ); ?></h3>
                    <p class="slv-footer__desc">
                        <?php esc_html_e( '获取最新内容与专属优惠', 'sunlyvo-nexus' ); ?>
                    </p>
                    <form class="slv-footer__subscribe" method="post" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <input type="email"
                               name="slv_newsletter_email"
                               placeholder="<?php esc_attr_e( '输入邮箱', 'sunlyvo-nexus' ); ?>"
                               required>
                        <button type="submit" class="slv-footer__subscribe-btn" aria-label="<?php esc_attr_e( '订阅', 'sunlyvo-nexus' ); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </form>
                </div>

            </div>

            <!-- 底部：版权 + 社交 -->
            <div class="slv-footer__bottom">
                <p class="slv-footer__copyright">
                    &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                    ·
                    <?php
                    printf(
                        /* translators: %s: project name */
                        esc_html__( '由 %s 强力驱动', 'sunlyvo-nexus' ),
                        '<a href="' . esc_url( SLV_PROJECT_URI ) . '" target="_blank" rel="noopener">SunLyvo Nexus</a>'
                    );
                    ?>
                </p>
            </div>

        </div>
    </footer>

</div><!-- #slv-page -->

<script>
(function(){
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // 搜索开关
        var searchToggle = document.querySelector('.slv-header__search-toggle');
        var searchPanel  = document.querySelector('.slv-header__search-panel');

        if (searchToggle && searchPanel) {
            searchToggle.addEventListener('click', function () {
                var isOpen = searchPanel.classList.toggle('is-open');
                searchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                searchPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

                if (isOpen) {
                    var input = searchPanel.querySelector('input[type="search"]');
                    if (input) { input.focus(); }
                }
            });
        }

        // 移动端菜单
        var menuToggle = document.querySelector('.slv-header__menu-toggle');
        var mobileNav  = document.querySelector('.slv-header__mobile-nav');

        if (menuToggle && mobileNav) {
            menuToggle.addEventListener('click', function () {
                var isOpen = mobileNav.classList.toggle('is-open');
                menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                mobileNav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                document.body.classList.toggle('slv-no-scroll', isOpen);
            });

            mobileNav.addEventListener('click', function (e) {
                if (e.target === mobileNav) {
                    mobileNav.classList.remove('is-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    mobileNav.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('slv-no-scroll');
                }
            });
        }

        // 用户菜单
        var userToggle = document.querySelector('.slv-header__user-toggle');
        var userMenu   = document.querySelector('.slv-header__user-menu');

        if (userToggle && userMenu) {
            userToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = userMenu.classList.toggle('is-open');
                userToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.addEventListener('click', function () {
                userMenu.classList.remove('is-open');
                userToggle.setAttribute('aria-expanded', 'false');
            });
        }
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>