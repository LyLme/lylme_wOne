<?php
// +----------------------------------------------------------------------
// | 视图设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为控制器方法 2 解析为模板文件
    'auto_rule'     => 1,
    // 模板目录名
    'view_dir_name' => 'view',
    // 模板后缀
    'view_suffix'   => 'html',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板根路径（留空默认 view/控制器名/）
    'view_path'     => app()->getRootPath() . 'view' . DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
    // 模板替换输出
    'tpl_replace_string' => [
        '__STATIC__' => '/static',
        '__CSS__'    => '/static/css',
        '__JS__'     => '/static/js',
        '__IMG__'    => '/static/images',
        '__UPLOAD__' => '/static/uploads',
    ],
];
