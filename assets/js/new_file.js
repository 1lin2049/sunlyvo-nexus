/**
 * 前台脚本
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    /**
     * 阅读进度条
     */
    function initProgressBar() {
        var bar = document.querySelector('.slv-reader__progress-bar');

        if (!bar) {
            return;
        }

        function updateProgress() {
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;

            if (docHeight <= 0) {
                bar.style.width = '0%';
                return;
            }

            var progress = (window.scrollY / docHeight) * 100;
            bar.style.width = Math.min(100, Math.max(0, progress)) + '%';
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
        window.addEventListener('resize', updateProgress);
        updateProgress();
    }

    /**
     * 章节阅读位置记忆
     */
    function initReadingPosition() {
        var reader = document.querySelector('.slv-reader[data-post-id]');

        if (!reader) {
            return;
        }

        var postId = reader.getAttribute('data-post-id');

        if (!postId) {
            return;
        }

        var storageKey = 'slv_read_pos_' + postId;

        // 恢复位置
        try {
            var saved = parseFloat(window.localStorage.getItem(storageKey) || '0');

            if (saved > 0.05 && saved < 0.95) {
                // 延迟触发，等待页面渲染完成
                setTimeout(function () {
                    var docHeight = document.documentElement.scrollHeight - window.innerHeight;
                    var target = saved * docHeight;

                    if (target > 200) {
                        window.scrollTo({
                            top: target,
                            behavior: 'instant'
                        });
                    }
                }, 100);
            }
        } catch (e) {
            // localStorage 不可用，忽略
        }

        // 记录位置（节流）
        var ticking = false;

        function savePosition() {
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;

            if (docHeight <= 0) {
                return;
            }

            var ratio = window.scrollY / docHeight;

            try {
                window.localStorage.setItem(storageKey, ratio.toFixed(4));
            } catch (e) {
                // 忽略
            }

            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(savePosition);
                ticking = true;
            }
        }, { passive: true });
    }

    /**
     * 章节导航快捷键
     * j / k：下一章 / 上一章
     */
    function initKeyboardNav() {
        var prevLink = document.querySelector('.slv-chapter__nav-item--prev');
        var nextLink = document.querySelector('.slv-chapter__nav-item--next');

        if (!prevLink && !nextLink) {
            return;
        }

        document.addEventListener('keydown', function (e) {
            // 忽略输入框内的按键
            var tag = (e.target.tagName || '').toLowerCase();

            if (['input', 'textarea', 'select'].indexOf(tag) !== -1) {
                return;
            }

            if (e.key === 'j' && nextLink) {
                window.location.href = nextLink.href;
            } else if (e.key === 'k' && prevLink) {
                window.location.href = prevLink.href;
            }
        });
    }

    /**
     * 初始化
     */
    function init() {
        initProgressBar();
        initReadingPosition();
        initKeyboardNav();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();