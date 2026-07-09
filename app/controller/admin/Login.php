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

        // 登录频率限制（同 IP 5 次失败后锁定 15 分钟）
        $ip = $this->request->ip();
        $failKey = 'login_fail_' . md5($ip);
        $lockKey = 'login_lock_' . md5($ip);
        if (Session::get($lockKey)) {
            $remain = Session::get($lockKey) - time();
            if ($remain > 0) {
                return $this->error('登录尝试次数过多，请 ' . ceil($remain / 60) . ' 分钟后重试');
            }
            Session::delete($lockKey);
            Session::delete($failKey);
        }

        $username = $this->request->post('username', '');
        $password = $this->request->post('password', '');

        if (empty($username) || empty($password)) {
            return $this->error('请输入用户名和密码');
        }

        // 查询管理员（先不限制状态，以便区分"账号禁用"和"密码错误"）
        $admin = Db::name('admin_user')
            ->where('username', $username)
            ->find();

        if (!$admin) {
            $this->recordLoginFail($ip, $failKey, $lockKey);
            return $this->error('用户名或密码错误');
        }

        // 检查账号是否已被禁用
        if ((int)$admin['status'] !== 1) {
            $this->recordLoginFail($ip, $failKey, $lockKey);
            return $this->error('该账号已被禁用，请联系超级管理员');
        }

        // 验证密码
        if (!password_verify($password, $admin['password'])) {
            $this->recordLoginFail($ip, $failKey, $lockKey);
            return $this->error('用户名或密码错误');
        }

        // 登录成功，清除失败记录
        Session::delete($failKey);
        Session::delete($lockKey);

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
     * 记录登录失败次数，超过阈值则锁定
     */
    private function recordLoginFail(string $ip, string $failKey, string $lockKey): void
    {
        $fails = (int)Session::get($failKey, 0) + 1;
        Session::set($failKey, $fails);
        if ($fails >= 5) {
            Session::set($lockKey, time() + 900); // 15 分钟锁定
        }
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
