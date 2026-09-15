/**
 * 摘要面板优化
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    function init() {
        var excerpt = document.getElementById('excerpt');
        if (!excerpt) return;

        var hint = document.createElement('div');
        hint.className = 'slv-excerpt-hint';
        hint.style.cssText = 'margin-top:6px;font-size:12px;color:#666;';
        excerpt.parentNode.appendChild(hint);

        function update() {
            var text = excerpt.value || '';
            var len = text.length;
            hint.textContent = len + ' 字符';
        }

        excerpt.addEventListener('input', update);
        update();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();