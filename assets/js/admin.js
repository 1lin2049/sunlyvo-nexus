/**
 * SunLyvo Nexus 后台脚本
 *
 * 访问类型字段联动：
 * - 选择"需购买"→ 显示价格、积分
 * - 选择"需会员等级"→ 显示最低会员等级
 * - 其他类型 → 隐藏上述字段
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    /**
     * 初始化访问类型字段联动
     */
    function initAccessTypeToggle() {
        var select = document.getElementById('slv_access_type');

        if (!select) {
            return;
        }

        var panel = select.closest('.slv-access-panel');

        if (!panel) {
            return;
        }

        var rows = panel.querySelectorAll('.slv-access-row[data-access-for]');

        /**
         * 根据当前访问类型，切换字段显示
         */
        function applyVisibility() {
            var type = select.value;

            rows.forEach(function (row) {
                var target = row.getAttribute('data-access-for');
                var show = false;

                if (target === 'purchase' && type === 'purchase') {
                    show = true;
                } else if (target === 'level' && type === 'level') {
                    show = true;
                }

                if (show) {
                    row.classList.remove('is-hidden');
                } else {
                    row.classList.add('is-hidden');
                }
            });
        }

        select.addEventListener('change', applyVisibility);
        applyVisibility();
    }

    /**
     * 初始化试读值 placeholder 联动
     */
    function initPreviewPlaceholder() {
        var typeSelect = document.querySelector('select[name="slv_preview_type"]');

        if (!typeSelect) {
            return;
        }

        var valueInput = document.querySelector('input[name="slv_preview_value"]');

        if (!valueInput) {
            return;
        }

        var placeholders = {
            'none': '',
            'percent': '1-100',
            'words': '例如 800',
            'paragraphs': '例如 3'
        };

        function applyPlaceholder() {
            var type = typeSelect.value;
            valueInput.setAttribute('placeholder', placeholders[type] || '');

            if (type === 'none') {
                valueInput.setAttribute('disabled', 'disabled');
            } else {
                valueInput.removeAttribute('disabled');
            }
        }

        typeSelect.addEventListener('change', applyPlaceholder);
        applyPlaceholder();
    }

    /**
     * 初始化
     */
    function init() {
        initAccessTypeToggle();
        initPreviewPlaceholder();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();