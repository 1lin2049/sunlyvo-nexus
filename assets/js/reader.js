/**
 * Reader 子系统交互
 *
 * 功能：
 * - TOC 自动生成（从 H2/H3 提取）
 * - 阅读位置记忆（localStorage）
 * - 复制 / 引用 / 分享浮层
 * - 深色/浅色主题切换
 * - 快捷键（t/g/Home/j/k）
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    var READER_SELECTOR = '.slv-reader';
    var STORAGE_PREFIX  = 'slv_';

    /**
     * 阅读进度条
     */
    function initProgressBar(root) {
        var bar = root.querySelector('.slv-reader__progress-bar');
        if (!bar) return;

        function update() {
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (docHeight <= 0) { bar.style.width = '0%'; return; }
            var progress = (window.scrollY / docHeight) * 100;
            bar.style.width = Math.min(100, Math.max(0, progress)) + '%';
        }

        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    /**
     * TOC 自动生成
     */
    function initTOC(root) {
        var tocContainer = root.querySelector('[data-slv-toc]');
        if (!tocContainer) return;

        var body = root.querySelector('.slv-chapter__body');
        if (!body) return;

        var headings = body.querySelectorAll('h2, h3');
        if (headings.length < 2) return;

        var list = document.createElement('ol');
        list.className = 'slv-toc-list';

        headings.forEach(function (heading, i) {
            if (!heading.id) {
                heading.id = 'slv-heading-' + i;
            }

            var li = document.createElement('li');
            li.className = 'slv-toc-item slv-toc-item--' + heading.tagName.toLowerCase();

            var link = document.createElement('a');
            link.href = '#' + heading.id;
            link.textContent = heading.textContent;
            link.className = 'slv-toc-link';

            link.addEventListener('click', function (e) {
                e.preventDefault();
                heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', '#' + heading.id);
            });

            li.appendChild(link);
            list.appendChild(li);
        });

        tocContainer.appendChild(list);

        // 滚动高亮
        var tocLinks = list.querySelectorAll('.slv-toc-link');

        function highlight() {
            var scrollY = window.scrollY + 100;
            var current = null;

            headings.forEach(function (h, i) {
                if (h.offsetTop <= scrollY) {
                    current = i;
                }
            });

            tocLinks.forEach(function (link, i) {
                link.classList.toggle('is-active', i === current);
            });
        }

        window.addEventListener('scroll', highlight, { passive: true });
        highlight();
    }

    /**
     * 阅读位置记忆
     */
    function initReadingPosition(root) {
        var postId = root.getAttribute('data-post-id');
        if (!postId) return;

        var key = STORAGE_PREFIX + 'read_pos_' + postId;

        // 恢复
        try {
            var saved = parseFloat(window.localStorage.getItem(key) || '0');
            if (saved > 0.05 && saved < 0.95) {
                showResume(saved);
            }
        } catch (e) { /* 忽略 */ }

        // 保存
        var ticking = false;
        function save() {
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (docHeight > 0) {
                var ratio = window.scrollY / docHeight;
                try {
                    window.localStorage.setItem(key, ratio.toFixed(4));
                } catch (e) { /* 忽略 */ }
            }
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(save);
                ticking = true;
            }
        }, { passive: true });

        // 提示条
        function showResume(ratio) {
            var el = document.createElement('div');
            el.className = 'slv-reader-resume';
            el.innerHTML =
                '<span class="slv-reader-resume__text">上次读到 ' + Math.round(ratio * 100) + '%</span>' +
                '<span class="slv-reader-resume__actions">' +
                    '<button type="button" class="slv-reader-resume__btn slv-reader-resume__btn--primary">继续阅读</button>' +
                    '<button type="button" class="slv-reader-resume__btn slv-reader-resume__btn--dismiss">×</button>' +
                '</span>';
            document.body.appendChild(el);

            setTimeout(function () { el.classList.add('is-visible'); }, 300);

            el.querySelector('.slv-reader-resume__btn--primary').addEventListener('click', function () {
                var docHeight = document.documentElement.scrollHeight - window.innerHeight;
                window.scrollTo({ top: ratio * docHeight, behavior: 'smooth' });
                el.classList.remove('is-visible');
                setTimeout(function () { el.remove(); }, 300);
            });

            el.querySelector('.slv-reader-resume__btn--dismiss').addEventListener('click', function () {
                el.classList.remove('is-visible');
                setTimeout(function () { el.remove(); }, 300);
            });
        }
    }

    /**
     * 选中文本 → 复制 / 引用 / 分享
     */
    function initShare(root) {
        var body = root.querySelector('.slv-chapter__body');
        if (!body) return;

        var toast = document.createElement('div');
        toast.className = 'slv-reader-share__toast';
        document.body.appendChild(toast);

        var showToast = function (msg) {
            toast.textContent = msg;
            toast.classList.add('is-visible');
            setTimeout(function () { toast.classList.remove('is-visible'); }, 2000);
        };

        document.addEventListener('mouseup', function (e) {
            var sel = window.getSelection();
            var text = (sel ? sel.toString() : '').trim();

            if (text.length < 5 || !body.contains(sel.anchorNode)) {
                return;
            }

            var rect = sel.getRangeAt(0).getBoundingClientRect();
            var popup = document.querySelector('.slv-reader-share');
            if (!popup) {
                popup = document.createElement('div');
                popup.className = 'slv-reader-share';
                popup.innerHTML =
                    '<button type="button" class="slv-reader-share__btn" data-action="copy" title="复制">📋</button>' +
                    '<button type="button" class="slv-reader-share__btn" data-action="quote" title="引用">❝</button>';
                document.body.appendChild(popup);
            }

            popup.style.left = (rect.left + window.scrollX + rect.width / 2 - 40) + 'px';
            popup.style.top  = (rect.top + window.scrollY - 44) + 'px';
            popup.classList.add('is-visible');
            popup._text = text;
        });

        document.addEventListener('mousedown', function (e) {
            var popup = document.querySelector('.slv-reader-share');
            if (popup && !popup.contains(e.target)) {
                popup.classList.remove('is-visible');
            }
        });

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.slv-reader-share__btn');
            if (!btn) return;

            var popup = btn.closest('.slv-reader-share');
            var text = popup._text || '';
            var action = btn.getAttribute('data-action');

            if (action === 'copy') {
                navigator.clipboard.writeText(text).then(function () {
                    showToast('已复制');
                }).catch(function () {
                    showToast('复制失败');
                });
            } else if (action === 'quote') {
                var quoted = text.split('\n').map(function (l) { return '> ' + l; }).join('\n');
                var final = quoted + '\n\n—— 摘自《' + document.title + '》';
                navigator.clipboard.writeText(final).then(function () {
                    showToast('已复制引用');
                });
            }

            popup.classList.remove('is-visible');
        });
    }

    /**
     * 主题切换
     */
    function initThemeToggle(root) {
        var STORAGE_KEY = STORAGE_PREFIX + 'theme';

        // 恢复
        try {
            var saved = window.localStorage.getItem(STORAGE_KEY);
            if (saved === 'dark' || saved === 'light') {
                document.documentElement.setAttribute('data-theme', saved);
            }
        } catch (e) { /* 忽略 */ }

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'slv-reader-theme-toggle';
        btn.setAttribute('aria-label', '切换主题');
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
        document.body.appendChild(btn);

        function toggle() {
            var current = document.documentElement.getAttribute('data-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try {
                window.localStorage.setItem(STORAGE_KEY, next);
            } catch (e) { /* 忽略 */ }
        }

        btn.addEventListener('click', toggle);
    }

    /**
     * 快捷键
     */
    function initKeyboard(root) {
        document.addEventListener('keydown', function (e) {
            var tag = (e.target.tagName || '').toLowerCase();
            if (['input', 'textarea', 'select'].indexOf(tag) !== -1) return;

            if (e.key === 't') {
                var btn = document.querySelector('.slv-reader-theme-toggle');
                if (btn) btn.click();
            } else if (e.key === 'j') {
                var next = root.querySelector('.slv-chapter__nav-item--next');
                if (next) window.location.href = next.href;
            } else if (e.key === 'k') {
                var prev = root.querySelector('.slv-chapter__nav-item--prev');
                if (prev) window.location.href = prev.href;
            } else if (e.key === 'Home') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (e.key === 'End') {
                window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
            }
        });
    }

    /**
     * 初始化
     */
    function init() {
        var root = document.querySelector(READER_SELECTOR);
        if (!root) return;

        initProgressBar(root);
        initTOC(root);
        initReadingPosition(root);
        initShare(root);
        initThemeToggle(root);
        initKeyboard(root);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();