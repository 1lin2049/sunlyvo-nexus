/**
 * 前台电商脚本
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

(function () {
    'use strict';

    var REST_BASE = (window.slvCommerce && window.slvCommerce.restBase) || '/wp-json/slv/v1';

    /**
     * 简单 fetch 封装
     */
    function apiFetch(path, options) {
        options = options || {};

        var url = REST_BASE + path;
        var opts = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        };

        if (options.body) {
            opts.body = JSON.stringify(options.body);
        }

        return fetch(url, opts).then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, status: res.status, data: data };
            });
        });
    }

    /**
     * 购物车项更新
     */
    function updateCartTotals(totals) {
        document.querySelectorAll('.slv-cart-count').forEach(function (el) {
            el.textContent = String(totals.count || 0);
        });
    }

    /**
     * 处理购物车页交互
     */
    function initCartPage() {
        var page = document.querySelector('.slv-cart-page');

        if (!page) {
            return;
        }

        // 移除
        page.querySelectorAll('.slv-cart-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var itemId = btn.getAttribute('data-item-id');

                if (!itemId) {
                    return;
                }

                btn.disabled = true;

                apiFetch('/cart/items/' + itemId, { method: 'DELETE' })
                    .then(function (res) {
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            btn.disabled = false;
                            alert((res.data && res.data.error) || '移除失败');
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                    });
            });
        });

        // 数量变更
        page.querySelectorAll('.slv-cart-qty').forEach(function (input) {
            var itemId = input.getAttribute('data-item-id');
            var lastVal = input.value;

            input.addEventListener('change', function () {
                var val = parseInt(input.value, 10);

                if (isNaN(val) || val < 1) {
                    input.value = lastVal;
                    return;
                }

                apiFetch('/cart/items/' + itemId + '/quantity', {
                    method: 'POST',
                    body: { quantity: val }
                }).then(function (res) {
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        input.value = lastVal;
                        alert((res.data && res.data.error) || '更新失败');
                    }
                });
            });
        });
    }

    /**
     * 处理结算页交互
     */
    function initCheckoutPage() {
        var form = document.getElementById('slv-checkout-form');

        if (!form) {
            return;
        }

        var message = document.getElementById('slv-checkout-message');
        var submitBtn = document.getElementById('slv-place-order');
        var couponInput = document.getElementById('slv-coupon-input');
        var couponBtn = document.getElementById('slv-coupon-apply');

        if (!submitBtn) {
            return;
        }

        // 优惠券应用
        if (couponBtn && couponInput) {
            couponBtn.addEventListener('click', function () {
                var code = couponInput.value.trim();

                if (!code) {
                    return;
                }

                couponBtn.disabled = true;

                apiFetch('/coupon/validate', {
                    method: 'POST',
                    body: { code: code }
                }).then(function (res) {
                    couponBtn.disabled = false;

                    if (res.ok && res.data.valid) {
                        var url = new URL(window.location.href);
                        url.searchParams.set('coupon', code);
                        window.location.href = url.toString();
                    } else {
                        alert((res.data && res.data.message) || '优惠券无效');
                    }
                }).catch(function () {
                    couponBtn.disabled = false;
                });
            });
        }

        // 提交
        submitBtn.addEventListener('click', function () {
            // 收集表单
            var formData = new FormData(form);
            var payload = {
                nonce: formData.get('slv_checkout_nonce'),
                billing: {},
                customer_note: formData.get('customer_note') || '',
                gateway: '',
                coupon_code: ''
            };

            formData.forEach(function (value, key) {
                if (key.indexOf('billing[') === 0) {
                    var field = key.replace('billing[', '').replace(']', '');
                    payload.billing[field] = value;
                } else if (key === 'gateway') {
                    payload.gateway = value;
                }
            });

            // 优惠券
            var coupon = document.getElementById('slv-coupon-input');
            if (coupon && coupon.value) {
                payload.coupon_code = coupon.value.trim();
            }

            // 清空消息
            if (message) {
                message.textContent = '';
                message.className = 'slv-checkout-message';
            }

            submitBtn.disabled = true;
            submitBtn.textContent = '处理中...';

            apiFetch('/checkout', {
                method: 'POST',
                body: payload
            }).then(function (res) {
                if (res.ok && res.data.success) {
                    if (res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '提交订单';

                    if (message) {
                        message.textContent = (res.data && (res.data.error || res.data.message)) || '提交失败';
                        message.className = 'slv-checkout-message slv-checkout-message--error';
                    }
                }
            }).catch(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = '提交订单';

                if (message) {
                    message.textContent = '网络错误，请重试';
                    message.className = 'slv-checkout-message slv-checkout-message--error';
                }
            });
        });
    }

    /**
     * 初始化
     */
    function init() {
        initCartPage();
        initCheckoutPage();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();