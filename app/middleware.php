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
    // 表单令牌验证 + 自动刷新（每次 POST 后自动生成新 token 通过响应头下发）
    \app\middleware\RefreshTokenCheck::class,
];
