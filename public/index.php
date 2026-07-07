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

// 定义项目根目录
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// 检查是否已安装（install.lock 位于项目根目录）
$installLock = ROOT_PATH . 'install.lock';
if (!file_exists($installLock) && !str_contains($_SERVER['REQUEST_URI'] ?? '', 'install.php')) {
    header('Location: /install.php');
    exit;
}

// 加载Composer自动加载
require ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
