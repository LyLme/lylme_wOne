<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\controller\admin\Base;
use app\model\ArticleCategory as AC;
use think\facade\View;

/**
 * 后台文章分类管理
 */
class ArticleCategory extends Base
{
    public function index()
    {
        $categories = AC::order('sort', 'asc')->order('id', 'desc')->select();
        return View::fetch('admin/article_category/index', [
            'page_title' => '文章分类管理',
            'categories' => $categories,
        ]);
    }

    public function add()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $data = $this->request->post();
        if (empty($data['name'])) return $this->error('分类名称不能为空');

        try {
            $category = new AC();
            $category->save([
                'name'   => trim($data['name']),
                'slug'   => trim($data['slug'] ?? ''),
                'type'   => $data['type'] ?? 'news',
                'sort'   => (int)($data['sort'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ]);
            return $this->success(null, '添加成功');
        } catch (\Exception $e) {
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    public function update()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id   = (int)$this->request->post('id', 0);
        $data = $this->request->post();
        if (empty($id)) return $this->error('参数错误');
        if (empty($data['name'])) return $this->error('分类名称不能为空');

        try {
            $category = AC::find($id);
            if (!$category) return $this->error('分类不存在');
            $category->save([
                'name'   => trim($data['name']),
                'slug'   => trim($data['slug'] ?? ''),
                'type'   => $data['type'] ?? 'news',
                'sort'   => (int)($data['sort'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ]);
            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id = (int)$this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        try {
            $category = AC::find($id);
            if (!$category) return $this->error('分类不存在');
            $category->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
