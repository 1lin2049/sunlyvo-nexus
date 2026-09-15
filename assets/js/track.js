/**
 * 前端埋点
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    var REST_BASE = (window.slvTrack && window.slvTrack.restBase) || '/wp-json/slv/v1';
    var POST_ID   = (window.slvTrack && window.slvTrack.postId) || 0;
    var VISITOR   = '';
    var STORAGE_KEY = 'slv_visitor_id';

    /**
     * 生成访客ID
     */
    function getVisitorId() {
        try {
            var id = window.localStorage.getItem(STORAGE_KEY);
            if (!id) {
                id = 'v_' + Math.random().toString(36).substring(2, 15) + Date.now().toString(36);
                window.localStorage.setItem(STORAGE_KEY, id);
            }
            return id;
        } catch (e) {
            return 'v_' + Math.random().toString(36).substring(2, 15);
        }
    }

    /**
     * 上报
     */
    function track(event, value) {
        if (!POST_ID || !VISITOR) return;

        var url = REST_BASE + '/track';
        var body = JSON.stringify({
            post_id: POST_ID,
            event: event,
            value: value || 0,
            visitor_id: VISITOR
        });

        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }));
        } else {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body,
                credentials: 'same-origin',
                keepalive: true
            }).catch(function () {});
        }
    }

    /**
     * 初始化
     */
    function init() {
        if (!POST_ID) return;
        VISITOR = getVisitorId();

        // view：立即上报
        track('view');

        // read：10 秒后上报（深度阅读）
        setTimeout(function () { track('read'); }, 10000);

        // progress：滚动到 25/50/75/100% 时上报
        var fired = { 25: false, 50: false, 75: false, 100: false };

        function checkProgress() {
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (docHeight <= 0) return;

            var ratio = Math.round((window.scrollY / docHeight) * 100);

            [25, 50, 75, 100].forEach(function (point) {
                if (!fired[point] && ratio >= point) {
                    fired[point] = true;
                    track('progress', point);
                }
            });
        }

        window.addEventListener('scroll', function () {
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(checkProgress);
            } else {
                checkProgress();
            }
        }, { passive: true });

        // time：每 30 秒上报一次
        var timeAccumulated = 0;
        setInterval(function () {
            if (document.visibilityState === 'visible') {
                timeAccumulated += 30;
                track('time', 30);
            }
        }, 30000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();