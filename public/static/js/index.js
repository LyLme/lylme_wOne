/* =====================================================
   上云六零科技 - 首页脚本
   ===================================================== */

(function($) {
    'use strict';

    // ==================== Banner轮播 ====================
    function initBannerSlider() {
        var $slider     = $('.banner-slider');
        var $slides     = $('.banner-slide');
        var $indicators = $('.banner-indicators .indicator');
        var currentIndex = 0;
        var slideCount   = $slides.length;
        var intervalId   = null;
        var isHovering   = false;
        var intervalTime = 5000; // 5秒切换

        // 如果没有轮播图或只有一张，退出
        if (slideCount <= 1) {
            $indicators.hide();
            return;
        }

        // 显示指定幻灯片
        function showSlide(index) {
            if (index === currentIndex) return;

            $slides.removeClass('active');
            $indicators.removeClass('active');

            currentIndex = index;
            $slides.eq(currentIndex).addClass('active');
            $indicators.eq(currentIndex).addClass('active');
        }

        // 下一张
        function nextSlide() {
            var nextIndex = (currentIndex + 1) % slideCount;
            showSlide(nextIndex);
        }

        // 自动播放
        function startAutoPlay() {
            stopAutoPlay();
            intervalId = setInterval(nextSlide, intervalTime);
        }

        // 停止自动播放
        function stopAutoPlay() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }

        // 绑定指示器点击
        $indicators.on('click', function() {
            var index = parseInt($(this).data('slide'), 10);
            if (isNaN(index) || index < 0 || index >= slideCount) return;
            showSlide(index);
            if (!isHovering) {
                startAutoPlay();
            }
        });

        // 鼠标悬停时暂停
        $slider.on('mouseenter', function() {
            isHovering = true;
            stopAutoPlay();
        }).on('mouseleave', function() {
            isHovering = false;
            startAutoPlay();
        });

        // 初始化显示第一张（若模板已标记 active 则不再重置，避免页面加载时闪烁）
        if (!$slides.filter('.active').length) {
            $slides.first().addClass('active');
            $indicators.first().addClass('active');
        }

        // 启动自动播放
        startAutoPlay();
    }

    // ==================== 数字递增动画 ====================
    function initCounterAnimation() {
        var $counters = $('.counter');
        if (!$counters.length) return;

        // 使用 Intersection Observer 检测元素是否进入视口
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var $el = $(entry.target);
                        var raw = $el.data('target');
                        var target = parseInt(raw, 10);
                        animateCounter($el, target, raw);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            $counters.each(function() {
                observer.observe(this);
            });
        } else {
            // 降级处理：直接显示
            $counters.each(function() {
                var $this = $(this);
                var raw = $this.data('target');
                var target = parseInt(raw, 10);
                if (isNaN(target) || raw === '') {
                    $this.text(raw);
                } else {
                    $this.text(target);
                }
            });
        }
    }

    function animateCounter($el, target, raw) {
        // 如果 target 不是数字（比如"全境"），直接显示原始文本
        if (isNaN(target) || raw === '') {
            $el.text(raw || '');
            return;
        }

        var current = 0;
        var step    = Math.ceil(target / 50);
        var duration = 1500; // 总时间 1.5秒
        var interval = duration / (target / step);

        var timer = setInterval(function() {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            $el.text(current);
        }, interval);
    }

    // ==================== 产品卡片悬浮效果 ====================
    function initProductCardHover() {
        $('.product-card').on('mouseenter', function() {
            $(this).find('.product-img img').css('transform', 'scale(1.05)');
        }).on('mouseleave', function() {
            $(this).find('.product-img img').css('transform', 'scale(1)');
        });
    }

    // ==================== 服务卡片悬浮效果 ====================
    function initServiceCardHover() {
        $('.service-card, .advantage-card').on('mouseenter', function() {
            $(this).find('.service-icon, .advantage-icon').css('transform', 'scale(1.1)');
        }).on('mouseleave', function() {
            $(this).find('.service-icon, .advantage-icon').css('transform', 'scale(1)');
        });
    }

    // ==================== 初始化 ====================
    $(document).ready(function() {
        initBannerSlider();
        initCounterAnimation();
        initProductCardHover();
        initServiceCardHover();
    });

})(jQuery);
