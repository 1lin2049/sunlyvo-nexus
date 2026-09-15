/**
 * 隐藏原生统计面板
 *
 * WordPress 的"速览"和"活动"面板与主题统计重复，隐藏以减少视觉噪音。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    function hideNativeWidgets() {
        // 只在仪表盘页面执行
        var dashboard = document.getElementById('dashboard-widgets-wrap');
        if (!dashboard) return;

        var targets = [
            'dashboard_right_now',
            'dashboard_activity',
            'dashboard_quick_press',
            'dashboard_primary',
            'dashboard_site_health'
        ];

        targets.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.style.display = 'none';
            }
        });
    }

    function init() {
        hideNativeWidgets();

        // WordPress 的"屏幕选项"可能导致面板重新渲染
        var observer = new MutationObserver(function () {
            hideNativeWidgets();
        });

        var container = document.getElementById('dashboard-widgets');
        if (container) {
            observer.observe(container, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();