/**
 * 后台编辑器底部统计状态栏
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    function init() {
        if (typeof window.slvStats === 'undefined') return;

        var s = window.slvStats;

        function makeBar() {
            var bar = document.getElementById('slv-admin-stats-bar');
            if (bar) return bar;

            bar = document.createElement('div');
            bar.id = 'slv-admin-stats-bar';
            bar.style.cssText = [
                'position:fixed',
                'bottom:0',
                'left:160px',
                'right:0',
                'background:#1d2327',
                'color:#e6edf3',
                'font-size:12px',
                'padding:6px 24px',
                'z-index:9999',
                'display:flex',
                'gap:20px',
                'justify-content:flex-end',
                'align-items:center',
                'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
                'border-top:1px solid #000',
                'pointer-events:none'
            ].join(';');

            document.body.appendChild(bar);

            // 折叠后台侧栏时调整
            var observer = new MutationObserver(function () {
                var collapsed = document.body.classList.contains('folded');
                bar.style.left = collapsed ? '36px' : '160px';
            });
            observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

            return bar;
        }

        function render() {
            var bar = makeBar();
            bar.innerHTML =
                '<span>浏览 <strong style="color:#00ff88;">' + s.views + '</strong></span>' +
                '<span style="opacity:0.4;">·</span>' +
                '<span>深度阅读 <strong style="color:#00ff88;">' + s.reads + '</strong></span>' +
                '<span style="opacity:0.4;">·</span>' +
                '<span>字数 <strong>' + s.words + '</strong></span>' +
                '<span style="opacity:0.4;">·</span>' +
                '<span>预计阅读 <strong>' + s.time + ' 分钟</strong></span>' +
                '<span style="opacity:0.4;">·</span>' +
                '<span>评论 <strong>' + s.comments + '</strong></span>';
        }

        if (document.getElementById('wpbody')) {
            render();
        } else {
            document.addEventListener('DOMContentLoaded', render);
        }
    }

    init();
})();