<?php

return [
    // 默认日志通道
    'default'      => 'file',
    // 日志记录级别
    'level'        => ['error', 'warning', 'info', 'debug'],
    // 日志通道
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'           => 'File',
            // 日志保存目录
            'path'           => app()->getRuntimePath() . 'log',
            // 单文件日志写入
            'single'         => false,
            // 最大日志文件数量
            'max_files'      => 30,
            // 日志文件大小限制
            'file_size'      => 2097152,
            // JSON格式记录
            'json'           => false,
            // 独立日志级别
            'apart_level'    => ['error', 'sql'],
            // 日志时间格式
            'time_format'    => 'Y-m-d H:i:s',
        ],
    ],
];
