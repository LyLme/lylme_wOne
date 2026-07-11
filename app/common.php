<?php
declare(strict_types=1);

if (!function_exists('async_notify')) {
    /**
     * 异步执行通知任务
     * 在 PHP-FPM 环境下先结束请求响应，再在后台继续执行通知；
     * 非 FPM 环境（如 CLI）则同步执行。
     *
     * @param callable $callback 通知任务回调
     */
    function async_notify(callable $callback): void
    {
        register_shutdown_function(function () use ($callback) {
            try {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
                $callback();
            } catch (\Throwable $e) {
                \think\facade\Log::error('异步通知执行失败：' . $e->getMessage());
            }
        });
    }
}

if (!function_exists('purify_html')) {
    /**
     * 净化富文本 HTML，只保留安全的标签和属性，移除 XSS 攻击向量
     *
     * @param string|null $html 待净化的 HTML 字符串
     * @return string 净化后的安全 HTML
     */
    function purify_html(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. 白名单过滤标签，只允许安全的结构/格式化标签
        $allowed = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6>'
            . '<ul><ol><li><dl><dt><dd>'
            . '<a><img><span><div><sub><sup><hr>'
            . '<table><caption><colgroup><col><thead><tbody><tfoot><tr><td><th>'
            . '<blockquote><pre><code><kbd><samp><var>'
            . '<section><figure><figcaption><small><mark><del><ins>';

        $html = strip_tags($html, $allowed);

        // 2. 移除所有 on* 事件处理器属性（onerror/onclick/onload 等）
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>\/]*)/i', '', $html);

        // 3. 移除 javascript: / data: 危险协议
        $html = preg_replace(
            '/(\s)(href|src|action|formaction)\s*=\s*["\']?\s*j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:/i',
            '$1$2="#"',
            $html
        );
        $html = preg_replace(
            '/(\s)(href|src)\s*=\s*["\']?\s*data\s*:/i',
            '$1$2="#"',
            $html
        );

        // 4. 移除 style 属性中的 CSS expression() 表达式（旧版 IE 攻击面）
        $html = preg_replace(
            '/style\s*=\s*"[^"]*expression\s*\([^"]*"/i',
            '',
            $html
        );

        return $html;
    }
}
