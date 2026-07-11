<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\ProductCategory as PC;
use think\facade\View;

/**
 * 后台产品分类管理
 */
class ProductCategory extends Base
{
    public function index()
    {
        $categories = PC::order('sort', 'asc')->select()->toArray();
        $tree = PC::buildTree($categories, 0);

        return View::fetch('admin/product_category/index', [
            'page_title' => '产品分类管理',
            'categories' => $categories,
            'tree'       => $tree,
        ]);
    }

    public function add()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');

        $data = $this->request->only(['parent_id', 'name', 'slug', 'sort', 'status']);
        if (empty($data['name'])) return $this->error('分类名称不能为空');

        $data['parent_id'] = (int)($data['parent_id'] ?? 0);
        $data['slug']      = $data['slug'] ?: '';
        $data['sort']      = (int)($data['sort'] ?? 0);
        $data['status']    = (int)($data['status'] ?? 1);

        try {
            $c = new PC();
            $c->save($data);
            return $this->success(['id' => $c->id], '添加成功');
        } catch (\Exception $e) {
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    public function update()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');

        $id   = $this->request->post('id', 0);
        $data = $this->request->only(['parent_id', 'name', 'slug', 'sort', 'status']);
        if (empty($id)) return $this->error('参数错误');
        if (empty($data['name'])) return $this->error('分类名称不能为空');

        $data['parent_id'] = (int)($data['parent_id'] ?? 0);
        $data['sort']      = (int)($data['sort'] ?? 0);
        $data['status']    = (int)($data['status'] ?? 1);

        try {
            $c = PC::find($id);
            if (!$c) return $this->error('分类不存在');
            if ((int)$data['parent_id'] === (int)$id) return $this->error('不能将分类设为自身的子分类');
            $c->save($data);
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
            $c = PC::find($id);
            if (!$c) return $this->error('分类不存在');
            // 检查子分类
            $childCount = PC::where('parent_id', $id)->count();
            if ($childCount > 0) return $this->error('该分类下存在子分类，请先删除子分类');
            $c->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
