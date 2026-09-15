/**
 * 布局 meta box 交互
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    function init() {
        var select = document.getElementById('slv_layout');
        if (!select) return;

        var customRows = document.querySelectorAll('.slv-layout-custom');

        function apply() {
            var show = select.value === 'custom';
            customRows.forEach(function (el) {
                el.style.display = show ? 'block' : 'none';
            });
        }

        select.addEventListener('change', apply);
        apply();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();