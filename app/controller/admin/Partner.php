<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\Partner as PM;
use think\facade\View;

/**
 * 后台合作伙伴管理
 */
class Partner extends Base
{
    public function index()
    {
        $partners = PM::order('sort', 'asc')->select()->toArray();

        return View::fetch('admin/partner/index', [
            'page_title' => '合作伙伴管理',
            'partners'   => $partners,
        ]);
    }

    public function add()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $data = $this->request->only(['name', 'logo', 'url', 'sort', 'status']);
        if (empty($data['name'])) return $this->error('合作伙伴名称不能为空');

        $data['sort']   = (int)($data['sort'] ?? 0);
        $data['status'] = (int)($data['status'] ?? 1);
        $data['url']    = $data['url'] ?? '';

        try {
            $p = new PM();
            $p->save($data);
            return $this->success(['id' => $p->id], '添加成功');
        } catch (\Exception $e) {
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    public function update()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id   = $this->request->post('id', 0);
        $data = $this->request->only(['name', 'logo', 'url', 'sort', 'status']);
        if (empty($id)) return $this->error('参数错误');
        if (empty($data['name'])) return $this->error('合作伙伴名称不能为空');

        $data['sort']   = (int)($data['sort'] ?? 0);
        $data['status'] = (int)($data['status'] ?? 1);
        $data['url']    = $data['url'] ?? '';

        try {
            $p = PM::find($id);
            if (!$p) return $this->error('合作伙伴不存在');
            $p->save($data);
            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        // $this->checkSuperAdmin(); 允许编辑角色删除
        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        try {
            $p = PM::find($id);
            if (!$p) return $this->error('合作伙伴不存在');
            $p->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    public function toggleStatus()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        // $this->checkSuperAdmin();
        $id     = $this->request->post('id', 0);
        $status = $this->request->post('status', 1);

        try {
            $p = PM::find($id);
            if (!$p) return $this->error('合作伙伴不存在');
            $p->status = (int)$status;
            $p->save();
            return $this->success(null, $status ? '已启用' : '已禁用');
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) return $this->error('请选择文件');

        try {
            $ext = strtolower($file->getOriginalExtension());
            $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
            if (!in_array($ext, $allow)) return $this->error('不支持的文件类型');

            $subPath  = date('Ymd');
            $fileName = date('His') . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '.' . $ext;
            \think\facade\Filesystem::disk('public')->putFileAs($subPath, $file, $fileName);
            $url = '/static/uploads/' . $subPath . '/' . $fileName;
            return $this->success(['url' => $url, 'name' => $fileName], '上传成功');
        } catch (\Exception $e) {
            return $this->error('上传失败：' . $e->getMessage());
        }
    }
}
