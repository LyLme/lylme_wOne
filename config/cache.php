<?php
// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => env('CACHE_DRIVER', 'file'),
    // 缓存连接方式
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制
            'serialize'  => [],
        ],
        // Redis 缓存
        'redis' => [
            // 驱动方式
            'type'       => 'Redis',
            // 服务器地址
            'host'       => env('REDIS_HOST', '127.0.0.1'),
            // 端口
            'port'       => env('REDIS_PORT', 6379),
            // 密码
            'password'   => env('REDIS_PASSWORD', ''),
            // 缓存前缀
            'prefix'     => 'lylmew_',
            // 缓存有效期
            'expire'     => 3600,
        ],
    ],
];
