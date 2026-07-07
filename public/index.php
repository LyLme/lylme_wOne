<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2024 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 根目录入口文件
namespace think;

// 检查是否已安装（install.lock 位于项目根目录）
$installLock = __DIR__ . '/../install.lock';
if (!file_exists($installLock) && !str_contains($_SERVER['REQUEST_URI'] ?? '', 'install.php')) {
    header('Location: /install.php');
    exit;
}

// 定义项目根目录
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS);

// 加载Composer自动加载
require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
