<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\Product as PM;
use app\model\ProductCategory as PC;
use think\facade\View;

/**
 * 后台产品管理
 */
class Product extends Base
{
    public function index()
    {
        $kw         = $this->request->get('keyword', '');
        $categoryId = (int)$this->request->get('category_id', 0);
        $page       = (int)$this->request->get('page', 1);

        $query = PM::order('sort', 'asc')->order('id', 'desc');

        if (!empty($kw)) $query->whereLike('name', '%' . $kw . '%');
        if ($categoryId > 0) {
            $childIds = PC::getChildIds($categoryId);
            $query->whereIn('category_id', $childIds);
        }

        $list = $query->paginate(['list_rows' => 15, 'page' => $page]);
        $categories = PC::order('sort', 'asc')->select()->toArray();

        return View::fetch('admin/product/index', [
            'page_title'   => '产品管理',
            'list'         => $list,
            'categories'   => $categories,
            'keyword'      => $kw,
            'category_id'  => $categoryId,
        ]);
    }

    public function add()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');

        $data = $this->request->post();
        $err  = $this->validateProduct($data);
        if ($err) return $this->error($err);

        $saveData = $this->buildSaveData($data);

        try {
            $p = new PM();
            $p->save($saveData);
            return $this->success(['id' => $p->id], '添加成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '添加失败：'));
        }
    }

    public function update()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');

        $id   = $this->request->post('id', 0);
        $data = $this->request->post();
        if (empty($id)) return $this->error('参数错误');

        $err = $this->validateProduct($data);
        if ($err) return $this->error($err);

        $saveData = $this->buildSaveData($data);

        try {
            $p = PM::find($id);
            if (!$p) return $this->error('产品不存在');
            $p->save($saveData);
            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '更新失败：'));
        }
    }

    public function delete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        try {
            $p = PM::find($id);
            if (!$p) return $this->error('产品不存在');
            $p->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '删除失败：'));
        }
    }

    public function toggleStatus()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id     = $this->request->post('id', 0);
        $status = $this->request->post('status', 1);

        try {
            $p = PM::find($id);
            if (!$p) return $this->error('产品不存在');
            $p->status = (int)$status;
            $p->save();
            return $this->success(null, $status ? '已启用' : '已禁用');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }

    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) return $this->error('请选择文件');

        try {
            $ext = strtolower($file->getOriginalExtension());
            $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
            if (!in_array($ext, $allow)) {
                return $this->error('不支持的文件类型');
            }
            // 验证真实 MIME 类型
            $allowMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp'];
            if (!in_array($file->getMime(), $allowMime)) {
                return $this->error('不支持的文件类型');
            }
            $subPath  = date('Ymd');
            $fileName = date('His') . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '.' . $ext;
            \think\facade\Filesystem::disk('public')->putFileAs($subPath, $file, $fileName);
            $url = '/static/uploads/' . $subPath . '/' . $fileName;
            return $this->success(['url' => $url, 'name' => $fileName], '上传成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '上传失败：'));
        }
    }

    private function validateProduct(array $data): string
    {
        if (empty($data['name'])) return '产品名称不能为空';
        if (empty($data['category_id'])) return '请选择产品分类';
        return '';
    }

    private function buildSaveData(array $data): array
    {
        $params = [];
        if (!empty($data['param_key'])) {
            foreach ($data['param_key'] as $i => $k) {
                $k = trim($k);
                if ($k !== '') {
                    $params[$k] = trim($data['param_val'][$i] ?? '');
                }
            }
        }

        // imgs：前端以 JSON 字符串提交
        $imgs = [];
        if (!empty($data['imgs'])) {
            if (is_array($data['imgs'])) {
                $imgs = $data['imgs'];
            } else {
                $decoded = json_decode($data['imgs'], true);
                $imgs = is_array($decoded) ? $decoded : explode(',', $data['imgs']);
            }
        }

        return [
            'category_id'  => (int)$data['category_id'],
            'name'         => trim($data['name']),
            'model'        => trim($data['model'] ?? ''),
            'price_type'   => (int)($data['price_type'] ?? 0),
            'price_min'    => (float)($data['price_min'] ?? 0),
            'price_max'    => (float)($data['price_max'] ?? 0),
            'image'        => trim($data['image'] ?? ''),
            'imgs'         => $imgs,
            'params'       => $params,
            'summary'      => trim($data['summary'] ?? ''),
            'description'  => $data['description'] ?? '',
            'is_hot'       => (int)($data['is_hot'] ?? 0),
            'is_recommend' => (int)($data['is_recommend'] ?? 0),
            'sort'         => (int)($data['sort'] ?? 0),
            'status'       => (int)($data['status'] ?? 1),
        ];
    }
}
