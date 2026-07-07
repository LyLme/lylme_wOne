<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\AdminLog;
use app\model\AdminUser;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;

/**
 * 管理员管理控制器
 */
class Admin extends Base
{
    /**
     * 管理员列表
     */
    public function index()
    {
        $keyword = $this->request->get('keyword', '');
        $page = (int)$this->request->get('page', 1);

        $query = AdminUser::order('id', 'asc');
        if (!empty($keyword)) {
            $query->where('username|nickname', 'like', '%' . $keyword . '%');
        }

        $list = $query->paginate(['list_rows' => 15, 'page' => $page]);

        return View::fetch('admin/admin/index', [
            'page_title' => '管理员管理',
            'list'       => $list,
            'keyword'    => $keyword,
        ]);
    }

    /**
     * 新增管理员
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $this->checkSuperAdmin();

        $data = $this->request->post();
        $validate = $this->getValidate();
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 用户名唯一
        if (AdminUser::where('username', $data['username'])->find()) {
            return $this->error('用户名已存在');
        }

        $admin = AdminUser::create([
            'username' => $data['username'],
            'password' => $data['password'],
            'nickname' => $data['nickname'] ?? '',
            'role'     => (int)($data['role'] ?? AdminUser::ROLE_ADMIN),
            'status'   => 1,
        ]);

        $this->log(AdminLog::ACTION_CREATE, '新增管理员：' . $data['username']);
        return $this->success(['id' => $admin->id], '新增成功');
    }

    /**
     * 编辑管理员
     */
    public function update()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $this->checkSuperAdmin();

        $id = (int)$this->request->post('id', 0);
        $data = $this->request->post();

        $admin = AdminUser::find($id);
        if (!$admin) {
            return $this->error('管理员不存在');
        }
        if ($admin->username === 'admin' && isset($data['role']) && (int)$data['role'] !== AdminUser::ROLE_SUPER) {
            return $this->error('不允许修改默认超级管理员角色');
        }

        $update = [
            'nickname' => $data['nickname'] ?? $admin->nickname,
            'role'     => (int)($data['role'] ?? $admin->role),
            'status'   => isset($data['status']) ? (int)$data['status'] : $admin->status,
        ];
        if (!empty($data['password'])) {
            $update['password'] = $data['password'];
        }

        $admin->save($update);
        $this->log(AdminLog::ACTION_UPDATE, '编辑管理员：' . $admin->username);
        return $this->success(null, '保存成功');
    }

    /**
     * 删除管理员
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $this->checkSuperAdmin();

        $id = (int)$this->request->post('id', 0);
        $admin = AdminUser::find($id);
        if (!$admin) {
            return $this->error('管理员不存在');
        }
        if ($admin->username === 'admin') {
            return $this->error('默认超级管理员不可删除');
        }
        if ($admin->id === ($this->adminInfo['id'] ?? 0)) {
            return $this->error('不能删除当前登录账号');
        }

        $username = $admin->username;
        $admin->delete();
        $this->log(AdminLog::ACTION_DELETE, '删除管理员：' . $username);
        return $this->success(null, '删除成功');
    }

    /**
     * 修改当前管理员密码
     */
    public function changePassword()
    {
        if (!$this->request->isPost()) {
            return View::fetch('admin/admin/password', [
                'page_title' => '修改密码',
            ]);
        }

        $oldPassword = $this->request->post('old_password', '');
        $newPassword = $this->request->post('new_password', '');
        $confirmPassword = $this->request->post('confirm_password', '');

        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->error('请填写完整密码信息');
        }
        if ($newPassword !== $confirmPassword) {
            return $this->error('两次输入的新密码不一致');
        }
        if (strlen($newPassword) < 6) {
            return $this->error('新密码至少6位');
        }

        $admin = AdminUser::find($this->adminInfo['id']);
        if (!$admin || !password_verify($oldPassword, $admin->password)) {
            return $this->error('原密码错误');
        }

        $admin->password = $newPassword;
        $admin->save();

        $this->log(AdminLog::ACTION_PASSWORD, '修改个人密码');
        return $this->success(null, '密码修改成功，请重新登录');
    }

    /**
     * 管理员操作日志
     */
    public function logs()
    {
        $action = $this->request->get('action', '');
        $keyword = $this->request->get('keyword', '');
        $page = (int)$this->request->get('page', 1);

        $query = AdminLog::order('id', 'desc');
        if (!empty($action)) {
            $query->where('action', $action);
        }
        if (!empty($keyword)) {
            $query->where('admin_name|content', 'like', '%' . $keyword . '%');
        }

        $list = $query->paginate(['list_rows' => 20, 'page' => $page]);

        return View::fetch('admin/admin/logs', [
            'page_title' => '操作日志',
            'list'       => $list,
            'action'     => $action,
            'keyword'    => $keyword,
            'actions'    => [
                AdminLog::ACTION_LOGIN,
                AdminLog::ACTION_LOGOUT,
                AdminLog::ACTION_CREATE,
                AdminLog::ACTION_UPDATE,
                AdminLog::ACTION_DELETE,
                AdminLog::ACTION_STATUS,
                AdminLog::ACTION_UPLOAD,
                AdminLog::ACTION_CONFIG,
                AdminLog::ACTION_REPAIR,
                AdminLog::ACTION_PASSWORD,
                AdminLog::ACTION_CLEAR_CACHE,
            ],
        ]);
    }

    /**
     * 验证规则
     */
    private function getValidate(): \think\Validate
    {
        return (new \think\Validate())->rule([
            'username' => 'require|alphaDash|length:3,50',
            'password' => 'require|length:6,32',
            'nickname' => 'max:50',
            'role'     => 'in:0,1,2',
        ])->message([
            'username.require'   => '请输入用户名',
            'username.alphaDash' => '用户名只能是字母、数字、下划线和破折号',
            'username.length'    => '用户名长度为3-50位',
            'password.require'   => '请输入密码',
            'password.length'    => '密码长度为6-32位',
            'nickname.max'       => '昵称最多50个字符',
            'role.in'            => '角色选择错误',
        ]);
    }
}
