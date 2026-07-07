<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\Db;
use think\facade\Session;
use think\facade\View;

/**
 * 后台登录控制器
 */
class Login extends Base
{
    /**
     * 登录页面
     */
    public function index()
    {
        // 已登录跳转至仪表盘
        $adminInfo = Session::get('admin_info');
        if (!empty($adminInfo['id'])) {
            return redirect('/' . config('app.admin_path', 'admin') . '/index');
        }

        return View::fetch('admin/login/index', [
            'page_title' => '后台登录',
            'admin_path' => config('app.admin_path', 'admin'),
        ]);
    }

    /**
     * 执行登录
     */
    public function doLogin()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $username = $this->request->post('username', '');
        $password = $this->request->post('password', '');

        if (empty($username) || empty($password)) {
            return $this->error('请输入用户名和密码');
        }

        // 查询管理员
        $admin = Db::name('admin_user')
            ->where('username', $username)
            ->where('status', 1)
            ->find();

        if (!$admin) {
            return $this->error('用户名或密码错误');
        }

        // 验证密码
        if (!password_verify($password, $admin['password'])) {
            return $this->error('用户名或密码错误');
        }

        // 更新登录信息
        Db::name('admin_user')
            ->where('id', $admin['id'])
            ->update([
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_ip'   => $this->request->ip(),
            ]);

        // 保存会话
        $adminInfo = [
            'id'       => $admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'] ?: $admin['username'],
            'role'     => (int)$admin['role'],
            'avatar'   => $admin['avatar'] ?? '',
        ];
        Session::set('admin_info', $adminInfo);

        // 记录登录日志
        \app\model\AdminLog::record(
            $admin['id'],
            $adminInfo['nickname'],
            \app\model\AdminLog::ACTION_LOGIN,
            '管理员登录成功'
        );

        return $this->success(null, '登录成功');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $adminInfo = Session::get('admin_info');
        if (!empty($adminInfo['id'])) {
            \app\model\AdminLog::record(
                $adminInfo['id'],
                $adminInfo['nickname'] ?? $adminInfo['username'],
                \app\model\AdminLog::ACTION_LOGOUT,
                '管理员退出登录'
            );
        }
        Session::delete('admin_info');
        return redirect('/' . config('app.admin_path', 'admin') . '/login');
    }
}
