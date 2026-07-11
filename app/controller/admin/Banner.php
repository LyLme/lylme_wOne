<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\Banner as BannerModel;
use think\facade\View;

/**
 * 后台 Banner 管理控制器
 */
class Banner extends Base
{
    /**
     * Banner 列表页
     */
    public function index()
    {
        $banners = BannerModel::getAllBanners();

        return View::fetch('admin/banner/index', [
            'page_title' => 'Banner管理',
            'banners'    => $banners,
        ]);
    }

    /**
     * 新增 Banner（AJAX）
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $data = $this->request->only(['title', 'subtitle', 'image', 'link_url', 'sort', 'status']);

        if (empty($data['title'])) {
            return $this->error('Banner标题不能为空');
        }
        if (empty($data['image'])) {
            return $this->error('请上传Banner图片');
        }

        $data['sort']   = (int)($data['sort'] ?? 0);
        $data['status'] = (int)($data['status'] ?? 1);

        try {
            $banner = new BannerModel();
            $banner->save($data);
            return $this->success(['id' => $banner->id], '添加成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '添加失败：'));
        }
    }

    /**
     * 更新 Banner（AJAX）
     */
    public function update()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id   = $this->request->post('id', 0);
        $data = $this->request->only(['title', 'subtitle', 'image', 'link_url', 'sort', 'status']);

        if (empty($id)) {
            return $this->error('参数错误');
        }
        if (empty($data['title'])) {
            return $this->error('Banner标题不能为空');
        }

        $data['sort']   = (int)($data['sort'] ?? 0);
        $data['status'] = (int)($data['status'] ?? 1);

        try {
            $banner = BannerModel::find($id);
            if (!$banner) {
                return $this->error('Banner不存在');
            }
            $banner->save($data);
            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '更新失败：'));
        }
    }

    /**
     * 删除 Banner（AJAX）
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        // $this->checkSuperAdmin(); 允许编辑角色删除

        $id = $this->request->post('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }

        try {
            $banner = BannerModel::find($id);
            if (!$banner) {
                return $this->error('Banner不存在');
            }
            $banner->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '删除失败：'));
        }
    }

    /**
     * 切换状态（AJAX）
     */
    public function toggleStatus()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        // $this->checkSuperAdmin();

        $id     = $this->request->post('id', 0);
        $status = $this->request->post('status', 1);

        try {
            $banner = BannerModel::find($id);
            if (!$banner) {
                return $this->error('Banner不存在');
            }
            $banner->status = (int)$status;
            $banner->save();
            return $this->success(null, $status ? '已启用' : '已禁用');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }

    /**
     * 图片上传（复用 system upload endpoint）
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->error('请选择文件');
        }

        try {
            $ext = strtolower($file->getOriginalExtension());
            $allowExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
            if (!in_array($ext, $allowExt)) {
                return $this->error('不支持的文件类型，仅允许：' . implode(',', $allowExt));
            }
            // 验证真实 MIME 类型
            $allowMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
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
}
