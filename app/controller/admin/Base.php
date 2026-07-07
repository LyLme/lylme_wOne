<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\Session;
use think\facade\View;

/**
 * 后台基础控制器
 * 所有后台控制器继承此类，自动进行登录鉴权
 */
abstract class Base extends BaseController
{
    /**
     * 当前登录管理员信息
     */
    protected array $adminInfo = [];

    /**
     * 初始化：鉴权检查 + 共享变量
     */
    protected function initialize(): void
    {
        parent::initialize();

        // 登录检查（Login 控制器跳过）
        $controller = strtolower(class_basename($this));
        if ($controller !== 'login') {
            $this->checkLogin();
        }

        // 加载站点配置（从数据库读取，提供兜底默认值）
        $siteCfg = \app\model\Config::getAllConfig();
        // 关键字段兜底
        $defaults = [
            'company_name_short' => '后台管理系统',
            'company_name'       => '后台管理系统',
            'company_icon'       => '',
        ];
        $siteCfg = array_merge($defaults, $siteCfg);

        // 共享布局变量
        View::assign([
            'admin_info'          => $this->adminInfo,
            'controller'          => strtolower($controller),
            'action'              => strtolower($this->request->action()),
            'company_name_short'  => $siteCfg['company_name_short'] ?? $siteCfg['company_name'] ?? '后台',
            'company_icon'        => $siteCfg['company_icon'] ?? '',
        ]);
    }

    /**
     * 检查登录状态
     */
    protected function checkLogin(): void
    {
        $adminInfo = Session::get('admin_info');
        if (empty($adminInfo) || empty($adminInfo['id'])) {
            if ($this->request->isAjax()) {
                $this->error('请先登录', 401, null)->send();
                exit;
            }
            redirect('/admin/login')->send();
            exit;
        }
        $this->adminInfo = $adminInfo;
    }

    /**
     * 返回JSON成功数据
     */
    protected function success($data = null, string $msg = '操作成功', int $code = 0): \think\response\Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 返回JSON失败数据
     */
    protected function error(string $msg = '操作失败', int $code = 1, $data = null): \think\response\Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 检查是否为超级管理员
     */
    protected function checkSuperAdmin(): void
    {
        if (empty($this->adminInfo) || ($this->adminInfo['role'] ?? 1) !== \app\model\AdminUser::ROLE_SUPER) {
            if ($this->request->isAjax()) {
                $this->error('仅超级管理员可操作', 403)->send();
                exit;
            }
            abort(403, '仅超级管理员可操作');
        }
    }

    /**
     * 记录管理员操作日志
     */
    protected function log(string $action, string $content): void
    {
        \app\model\AdminLog::record(
            $this->adminInfo['id'] ?? 0,
            $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? 'system',
            $action,
            $content
        );
    }
}
