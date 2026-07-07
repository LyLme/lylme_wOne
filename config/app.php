<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('APP_HOST', ''),
    // 应用调试模式
    'app_debug'        => env('APP_DEBUG', false),
    // 应用Trace
    'app_trace'        => false,
    // 默认应用名
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => env('APP.DEFAULT_TIMEZONE', 'Asia/Shanghai'),
    // 应用映射
    'app_map'          => [],
    // 域名绑定
    'domain_bind'      => [],
    // 禁止URL访问的应用
    'deny_app_list'    => [],
    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',
    // 错误显示信息
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => true,
    // 自动多应用模式
    'auto_multi_app'   => false,
    // 后台路径（默认 admin，可改为自定义路径增强安全性）
    'admin_path'       => env('APP.ADMIN_PATH', 'admin'),
];
