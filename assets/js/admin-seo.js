/**
 * SEO 后台脚本
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    /**
     * 处理图片字段
     */
    function initImageFields() {
        // 媒体库选择
        document.querySelectorAll('.slv-seo-image-select').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                if (typeof wp === 'undefined' || !wp.media) {
                    alert('媒体库未加载，请刷新页面重试。');
                    return;
                }

                var targetId = btn.getAttribute('data-target');
                var input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                var frame = wp.media({
                    title: '选择图片',
                    button: { text: '使用此图片' },
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var url = attachment.url || '';

                    input.value = url;

                    var preview = input.parentNode.querySelector('.slv-seo-image-preview');

                    if (preview) {
                        preview.innerHTML = url
                            ? '<img src="' + url + '" alt="">'
                            : '';
                    }
                });

                frame.open();
            });
        });

        // 移除图片
        document.querySelectorAll('.slv-seo-image-remove').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                var targetId = btn.getAttribute('data-target');
                var input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                input.value = '';

                var preview = input.parentNode.querySelector('.slv-seo-image-preview');

                if (preview) {
                    preview.innerHTML = '';
                }
            });
        });
    }

    /**
     * 初始化
     */
    function init() {
        // 只在 SEO 页面初始化
        if (!document.querySelector('.slv-seo-wrap')) {
            return;
        }

        initImageFields();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();