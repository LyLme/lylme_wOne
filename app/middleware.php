<?php
// +----------------------------------------------------------------------
// | 全局中间件定义
// +----------------------------------------------------------------------

return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    \think\middleware\SessionInit::class,
    // 表单令牌验证（CSRF防护）- 暂关闭，后台登录表单未适配token
    // \think\middleware\FormTokenCheck::class,
];
