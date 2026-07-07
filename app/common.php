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
