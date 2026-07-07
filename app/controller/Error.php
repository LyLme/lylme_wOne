<?php
declare(strict_types=1);

namespace app\controller;

/**
 * 空控制器 - 处理404页面
 */
class Error extends FrontBase
{
    /**
     * 默认方法
     */
    public function index()
    {
        return $this->error404();
    }

    /**
     * 404页面
     */
    public function error404()
    {
        return response('404 - 页面不存在', 404);
    }

    /**
     * 500页面
     */
    public function error500()
    {
        return response('500 - 服务器内部错误', 500);
    }

    /**
     * 魔术方法：处理不存在的操作
     */
    public function __call($method, $args)
    {
        return $this->error404();
    }
}
