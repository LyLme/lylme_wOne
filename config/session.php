<?php
// +----------------------------------------------------------------------
// | Session设置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'lylmewSESSION',
    // SESSION_ID前缀
    'prefix'         => 'lylmew_',
    // 驱动方式 支持file cache redis
    'type'           => 'file',
    // 是否自动开启
    'auto_start'     => true,
    // 域名设置
    'cookie'         => [
        // path
        'path'     => '/',
        // 有效域名
        'domain'   => '',
        // 是否仅HTTPS（生产环境自动启用）
        'secure'   => env('SESSION.SECURE', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        // 是否httponly
        'httponly' => true,
        // 有效期
        'lifetime' => 7200,
        // samesite
        'samesite' => 'Lax',
    ],
];
