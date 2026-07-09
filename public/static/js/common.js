/* =====================================================
   上云六零科技 - 公共脚本
   ===================================================== */

(function($) {
    'use strict';

    // ==================== 初始化全局命名空间（需放在所有 lylmew.* 使用之前）====================
    window.lylmew = window.lylmew || {};

    // ==================== 导航栏滚动效果 ====================
    function initNavbarScroll() {
        var $navbar = $('#mainNavbar');
        var scrollThreshold = 50;

        $(window).on('scroll', function() {
            if ($(window).scrollTop() > scrollThreshold) {
                $navbar.addClass('scrolled');
            } else {
                // 仅当不在顶部时才移除scrolled类（移动端始终有背景）
                if ($(window).scrollTop() === 0) {
                    $navbar.removeClass('scrolled');
                }
            }
        });

        // 初始化检查
        if ($(window).scrollTop() > scrollThreshold) {
            $navbar.addClass('scrolled');
        }
    }

    // ==================== 移动端菜单切换 ====================
    function initMobileMenu() {
        // Bootstrap 5 的 Collapse 组件会自动处理，这里只需处理额外逻辑
        var $navCollapse = $('#navbarNav');
        if ($navCollapse.length) {
            $navCollapse.on('hidden.bs.collapse', function() {
                // 菜单关闭后可执行额外操作
            });
        }

        // 点击导航链接后自动关闭移动端菜单
        $('.navbar-nav .nav-link').on('click', function() {
            if ($(window).width() < 992) {
                $navCollapse.collapse('hide');
            }
        });
    }

    // ==================== 回到顶部 ====================
    function initBackToTop() {
        var $btn = $('#backToTop');

        $(window).on('scroll', function() {
            if ($(window).scrollTop() > 300) {
                $btn.addClass('visible');
            } else {
                $btn.removeClass('visible');
            }
        });

        $btn.on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 500);
        });
    }

    // ==================== CSRF Token 处理 ====================
    function initCsrfToken() {
        // 对所有 AJAX POST/PUT/DELETE 请求自动注入 __token__ 字段
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(options.type)) {
                var token = $('meta[name="csrf-token"]').attr('content');
                if (token) {
                    if (options.data instanceof FormData) {
                        options.data.append('__token__', token);
                    } else if ($.isPlainObject(options.data)) {
                        options.data.__token__ = token;
                    } else if (typeof options.data === 'string') {
                        options.data += (options.data ? '&' : '') + '__token__=' + encodeURIComponent(token);
                    }
                }
            }
        });

        // 每次 AJAX 完成后自动更新 meta 中的 token，实现连续提交
        $(document).ajaxComplete(function(event, jqXHR) {
            var newToken = jqXHR.getResponseHeader('X-CSRF-TOKEN');
            if (newToken) {
                $('meta[name="csrf-token"]').attr('content', newToken);
                // 同步更新页面中所有 __token__ 隐藏字段
                $('input[name="__token__"]').val(newToken);
            }
        });
    }

    // fetch() API 用 CSRF helper
    lylmew.getCsrfToken = function() {
        var $token = $('meta[name="csrf-token"]');
        return $token.length ? $token.attr('content') : '';
    };

    lylmew.appendCsrfToken = function(formData) {
        var token = lylmew.getCsrfToken();
        if (token) formData.append('__token__', token);
        return formData;
    };

    // ==================== 平滑滚动（锚点链接）====================
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 500);
            }
        });
    }

    // ==================== 图片懒加载（简单版本）====================
    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            var lazyImages = document.querySelectorAll('img[data-lazy]');
            var imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.lazy;
                        img.removeAttribute('data-lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    }

    // ==================== 访客标识（浏览器本地存储）====================
    lylmew.VISITOR_KEY = 'lylmew_visitor_info';

    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    lylmew.getVisitorInfo = function() {
        try {
            var info = JSON.parse(localStorage.getItem(lylmew.VISITOR_KEY) || '{}');
            if (!info.visitor_id) {
                info.visitor_id = generateUUID();
                // 必须立即持久化，否则每次调用都会生成不同的 UUID
                localStorage.setItem(lylmew.VISITOR_KEY, JSON.stringify(info));
            }
            return info;
        } catch (e) {
            return { visitor_id: generateUUID() };
        }
    };

    lylmew.saveVisitorInfo = function(info) {
        try {
            var current = lylmew.getVisitorInfo();
            var merged = Object.assign({}, current, info);
            localStorage.setItem(lylmew.VISITOR_KEY, JSON.stringify(merged));
        } catch (e) {}
    };

    lylmew.fillVisitorForm = function(formSelector) {
        var info = lylmew.getVisitorInfo();
        var $form = $(formSelector);
        if (info.name) $form.find('[name="name"], [name="client_name"]').val(info.name);
        if (info.phone) $form.find('[name="phone"]').val(info.phone);
    };

    lylmew.appendVisitorId = function(formData) {
        var info = lylmew.getVisitorInfo();
        formData.append('visitor_id', info.visitor_id);
        return info;
    };

    // ==================== 初始化 ====================
    $(document).ready(function() {
        initNavbarScroll();
        initMobileMenu();
        initBackToTop();
        initCsrfToken();
        initSmoothScroll();
        initLazyLoad();
    });

})(jQuery);
