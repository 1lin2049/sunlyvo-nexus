/**
 * 后台电商脚本
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    function init() {
        // 订单状态变更确认
        document.querySelectorAll('.slv-admin-order-action').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                var message = btn.getAttribute('data-confirm');

                if (message && !window.confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();