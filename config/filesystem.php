<?php
// +----------------------------------------------------------------------
// | 文件系统设置
// +----------------------------------------------------------------------

return [
    // 默认磁盘
    'default' => env('FILESYSTEM_DRIVER', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            // 本地磁盘
            'type' => 'local',
            // 磁盘路径
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 对外公开磁盘
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/static/uploads',
            // 外部URL
            'url'        => '/static/uploads',
            // 是否可见
            'visibility' => 'public',
        ],
    ],
];
