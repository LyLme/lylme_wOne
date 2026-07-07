<?php
// +----------------------------------------------------------------------
// | ThinkPHP CLI 路由
// +----------------------------------------------------------------------
// | 用于开发模式下的内置服务器路由
// +----------------------------------------------------------------------
namespace think;

// CLI 模式直接返回
if (PHP_SAPI !== 'cli') {
    // 内置服务器路由
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $file = __DIR__ . $uri;

    if ($uri !== '/' && is_file($file)) {
        return false;
    }
}

// 加载Composer自动加载
require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
