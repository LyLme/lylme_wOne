<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\ContactInfo as ContactInfoModel;

/**
 * 后台公司联系方式管理（集成于关于我们Tab）
 */
class ContactInfo extends Base
{
    /**
     * 列表（AJAX）
     */
    public function list()
    {
        $list = ContactInfoModel::order('sort', 'asc')->select();
        return json(['code' => 0, 'data' => $list]);
    }

    /**
     * 新增
     */
    public function add()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');

        $type = $this->request->post('type', '');
        $value = $this->request->post('value', '');
        $person = $this->request->post('contact_person', '');
        $sort = (int)$this->request->post('sort', 0);

        if (empty($type) || empty($value)) {
            return $this->error('类型和联系方式不能为空');
        }

        try {
            $info = new ContactInfoModel();
            $info->save([
                'type'           => $type,
                'value'          => $value,
                'contact_person' => $person,
                'sort'           => $sort,
            ]);
            return $this->success(['id' => $info->id], '添加成功');
        } catch (\Exception $e) {
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    /**
     * 更新
     */
    public function update()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');

        $id = (int)$this->request->post('id', 0);
        if ($id <= 0) return $this->error('参数错误');

        $info = ContactInfoModel::find($id);
        if (!$info) return $this->error('记录不存在');

        $type = $this->request->post('type', $info->type);
        $value = $this->request->post('value', $info->value);
        $person = $this->request->post('contact_person', $info->contact_person);
        $sort = (int)$this->request->post('sort', $info->sort);

        if (empty($type) || empty($value)) {
            return $this->error('类型和联系方式不能为空');
        }

        try {
            $info->save([
                'type'           => $type,
                'value'          => $value,
                'contact_person' => $person,
                'sort'           => $sort,
            ]);
            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        // $this->checkSuperAdmin();  允许编辑角色删除

        $id = (int)$this->request->post('id', 0);
        if ($id <= 0) return $this->error('参数错误');

        try {
            $info = ContactInfoModel::find($id);
            if ($info) $info->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量保存（用于“保存联系方式”按钮）
     */
    public function batchSave()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $this->checkSuperAdmin();

        $itemsJson = $this->request->post('items', '[]');
        $items = json_decode($itemsJson, true);
        if (!\is_array($items)) {
            return $this->error('数据格式错误');
        }

        try {
            ContactInfoModel::startTrans();
            ContactInfoModel::where('id', '>', 0)->delete();
            foreach ($items as $item) {
                $type = trim($item['type'] ?? '');
                $value = trim($item['value'] ?? '');
                if (empty($type) || empty($value)) continue;
                ContactInfoModel::create([
                    'type'           => $type,
                    'value'          => $value,
                    'contact_person' => trim($item['contact_person'] ?? ''),
                    'sort'           => (int)($item['sort'] ?? 0),
                ]);
            }
            ContactInfoModel::commit();
            return $this->success(null, '联系方式保存成功');
        } catch (\Exception $e) {
            ContactInfoModel::rollback();
            return $this->error('保存失败：' . $e->getMessage());
        }
    }
}
