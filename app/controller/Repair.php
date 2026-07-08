<?php
declare(strict_types=1);

namespace app\controller;

use app\controller\FrontBase;
use app\model\RepairOrder;
use app\model\RepairTimeline;
use think\facade\View;
use think\facade\Filesystem;

/**
 * 前台在线报修控制器
 */
class Repair extends FrontBase
{
    /**
     * 报修首页
     */
    public function index()
    {
        return View::fetch('index/repair', [
            'page_title' => '在线报修 - ' . $this->site_name,
        ]);
    }

    /**
     * 提交报修
     */
    public function submit()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        $data = $this->request->post();

        // 验证码
        $captchaCode = $data['captcha'] ?? '';
        if (empty($captchaCode) || !captcha_check($captchaCode)) {
            return json(['code' => 1, 'msg' => '验证码错误']);
        }

        // 数据验证
        $validate = [
            'client_name' => 'require|max:30',
            'phone'       => 'require',
            'fault_desc'  => 'require|max:1000',
        ];
        $messages = [
            'client_name.require' => '请输入联系人姓名',
            'client_name.max'     => '联系人姓名最多30个字符',
            'phone.require'       => '请输入联系电话',
            'fault_desc.require'  => '请输入故障现象描述',
            'fault_desc.max'      => '故障描述最多1000个字符',
        ];
        $validateObj = new \think\Validate();
        $validateObj->rule($validate)->message($messages);
        if (!$validateObj->check($data)) {
            return json(['code' => 1, 'msg' => $validateObj->getError()]);
        }
        // 单独正则验证手机/座机号（避免 | 被TP规则分隔符拆分）
        if (!preg_match('/^(1[3-9]\d{9}|(0\d{2,3}-?)?\d{7,8})$/', $data['phone'])) {
            return json(['code' => 1, 'msg' => '请输入正确的手机或座机号码']);
        }

        // 处理上传图片
        $imagePaths = [];
        try {
            $files = $this->request->file('images');
        } catch (\Exception $e) {
            $files = null;
        }
        if ($files) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                try {
                    $savename = Filesystem::disk('public')->putFile('repair', $file);
                    if ($savename) {
                        $imagePaths[] = '/static/uploads/' . str_replace('\\', '/', $savename);
                    }
                } catch (\Exception $e) {
                    // 忽略上传失败
                }
            }
        }

        try {
            $order = new RepairOrder();
            $order->save([
                'order_no'    => RepairOrder::generateOrderNo(),
                'visitor_id'  => $data['visitor_id'] ?? '',
                'client_name' => $data['client_name'],
                'phone'       => $data['phone'],
                'company'     => $data['company'] ?? '',
                'address'     => $data['address'] ?? '',
                'description' => $data['fault_desc'],
                'images'      => !empty($imagePaths) ? json_encode($imagePaths, JSON_UNESCAPED_UNICODE) : '',
                'status'      => RepairOrder::STATUS_PENDING,
                'ip'          => $this->request->ip(),
            ]);

            // 记录时间轴
            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_CREATED,
                '提交报修',
                '用户提交报修工单：' . mb_substr($data['fault_desc'], 0, 50),
                0,
                $data['client_name'],
                'user'
            );

            // 异步通知
            async_notify(function () use ($data, $order) {
                \app\service\Notification::sendMessage([
                    'name'        => $data['client_name'],
                    'phone'       => $data['phone'],
                    'content'     => '新报修工单：' . $order->order_no . "\n故障描述：" . $data['fault_desc'],
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
            });

            return json(['code' => 0, 'msg' => '报修提交成功', 'data' => ['order_no' => $order->order_no]]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '提交失败：' . $e->getMessage()]);
        }
    }

    /**
     * 我的工单列表
     */
    public function myOrders()
    {
        $visitorId = $this->request->get('visitor_id', '');
        $phone     = $this->request->get('phone', '');

        if (empty($visitorId) && empty($phone)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $query = RepairOrder::order('id', 'desc')->limit(50);
        if (!empty($visitorId)) {
            $query->where('visitor_id', $visitorId);
        }
        if (!empty($phone) && empty($visitorId)) {
            $query->where('phone', $phone);
        }
        if (!empty($visitorId) && !empty($phone)) {
            $query->where(function ($q) use ($visitorId, $phone) {
                $q->where('visitor_id', $visitorId)
                  ->whereOr(function ($q2) use ($phone) {
                      $q2->where('visitor_id', '')
                          ->where('phone', $phone);
                  });
            });
        }

        $list = $query->select();
        $result = [];
        foreach ($list as $item) {
            $timeline = RepairTimeline::where('repair_id', $item->id)->order('id', 'asc')->select();
            $tl = [];
            foreach ($timeline as $t) {
                $tl[] = [
                    'action'        => $t->action,
                    'title'         => $t->title,
                    'content'       => $t->content,
                    'operator_name' => $t->operator_name,
                    'operator_type' => $t->operator_type,
                    'create_time'   => $t->create_time,
                    'icon'          => RepairTimeline::getActionIcon($t->action),
                ];
            }
            $result[] = [
                'id'                => $item->id,
                'order_no'          => $item->order_no,
                'status'            => (int)$item->status,
                'status_text'       => RepairOrder::getStatusText((int)$item->status),
                'status_icon'       => RepairOrder::getStatusIcon((int)$item->status),
                'client_name'       => $item->client_name,
                'phone'             => $item->phone,
                'company'           => $item->company,
                'address'           => $item->address,
                'description'       => $item->description,
                'images'            => !empty($item->images) ? json_decode($item->images, true) : [],
                'remark'            => $item->remark,
                'completion_receipt' => $item->completion_receipt,
                'service_price'     => floatval($item->service_price ?? 0),
                'cancel_reason'     => $item->cancel_reason,
                'pause_reason'      => $item->pause_reason,
                'create_time'       => $item->create_time,
                'update_time'       => $item->update_time,
                'timeline'          => $tl,
            ];
        }

        return json(['code' => 0, 'data' => $result]);
    }

    /**
     * 工单详情
     */
    public function orderDetail()
    {
        $id        = $this->request->get('id', 0);
        $visitorId = $this->request->get('visitor_id', '');
        $phone     = $this->request->get('phone', '');

        if (empty($id)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $order = RepairOrder::find($id);
        if (!$order) {
            return json(['code' => 1, 'msg' => '工单不存在']);
        }

        // 权限校验：仅允许创建者查看
        $hasAccess = false;
        if (!empty($visitorId) && $order->visitor_id === $visitorId) {
            $hasAccess = true;
        } elseif (!empty($phone) && $order->phone === $phone) {
            $hasAccess = true;
        }
        if (!$hasAccess) {
            return json(['code' => 1, 'msg' => '无权查看该工单']);
        }

        $timeline = RepairTimeline::where('repair_id', $id)->order('id', 'asc')->select();
        $tl = [];
        foreach ($timeline as $t) {
            $tl[] = [
                'action'        => $t->action,
                'title'         => $t->title,
                'content'       => $t->content,
                'operator_name' => $t->operator_name,
                'operator_type' => $t->operator_type,
                'create_time'   => $t->create_time,
                'icon'          => RepairTimeline::getActionIcon($t->action),
            ];
        }

        return json([
            'code' => 0,
            'data' => [
                'id'                 => $order->id,
                'order_no'           => $order->order_no,
                'status'             => (int)$order->status,
                'status_text'        => RepairOrder::getStatusText((int)$order->status),
                'status_icon'        => RepairOrder::getStatusIcon((int)$order->status),
                'client_name'        => $order->client_name,
                'phone'              => $order->phone,
                'company'            => $order->company,
                'address'            => $order->address,
                'description'        => $order->description,
                'images'             => !empty($order->images) ? json_decode($order->images, true) : [],
                'remark'             => $order->remark,
                'completion_receipt' => $order->completion_receipt,
                'service_price'      => floatval($order->service_price ?? 0),
                'cancel_reason'      => $order->cancel_reason,
                'pause_reason'       => $order->pause_reason,
                'create_time'        => $order->create_time,
                'update_time'        => $order->update_time,
                'timeline'           => $tl,
            ],
        ]);
    }

    /**
     * 编辑工单（仅待接单状态可编辑）
     */
    public function editOrder()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        $data = $this->request->post();
        $id   = $data['id'] ?? 0;

        if (empty($id)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $order = RepairOrder::find($id);
        if (!$order) {
            return json(['code' => 1, 'msg' => '工单不存在']);
        }
        if ((int)$order->status !== RepairOrder::STATUS_PENDING) {
            return json(['code' => 1, 'msg' => '只能编辑待接单状态的工单']);
        }

        // 数据验证
        $validate = [
            'client_name' => 'require|max:30',
            'phone'       => 'require',
            'fault_desc'  => 'require|max:1000',
        ];
        $messages = [
            'client_name.require' => '请输入联系人姓名',
            'client_name.max'     => '联系人姓名最多30个字符',
            'phone.require'       => '请输入联系电话',
            'fault_desc.require'  => '请输入故障现象描述',
            'fault_desc.max'      => '故障描述最多1000个字符',
        ];
        $validateObj = new \think\Validate();
        $validateObj->rule($validate)->message($messages);
        if (!$validateObj->check($data)) {
            return json(['code' => 1, 'msg' => $validateObj->getError()]);
        }
        // 单独正则验证手机/座机号
        if (!preg_match('/^(1[3-9]\d{9}|(0\d{2,3}-?)?\d{7,8})$/', $data['phone'])) {
            return json(['code' => 1, 'msg' => '请输入正确的手机或座机号码']);
        }

        // 处理上传图片（追加到已有图片）
        $existingImages = !empty($order->images) ? json_decode($order->images, true) : [];
        if (!is_array($existingImages)) $existingImages = [];
        try {
            $files = $this->request->file('images');
        } catch (\Exception $e) {
            $files = null;
        }
        if ($files) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                try {
                    $savename = Filesystem::disk('public')->putFile('repair', $file);
                    if ($savename) {
                        $existingImages[] = '/static/uploads/' . str_replace('\\', '/', $savename);
                    }
                } catch (\Exception $e) {
                    // 忽略上传失败
                }
            }
        }

        try {
            $oldDesc = $order->description;
            $oldName = $order->client_name;
            $oldPhone = $order->phone;
            $order->save([
                'client_name' => $data['client_name'],
                'phone'       => $data['phone'],
                'company'     => $data['company'] ?? '',
                'address'     => $data['address'] ?? '',
                'description' => $data['fault_desc'],
                'images'      => !empty($existingImages) ? json_encode($existingImages, JSON_UNESCAPED_UNICODE) : '',
            ]);

            // 记录编辑事件
            $changes = [];
            if ($oldDesc !== $data['fault_desc']) $changes[] = '故障描述已更新';
            if ($oldPhone !== $data['phone']) $changes[] = '联系电话已更新';
            if ($oldName !== $data['client_name']) $changes[] = '联系人已更新';
            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_EDITED,
                '用户编辑工单',
                !empty($changes) ? implode('；', $changes) : '用户编辑了工单信息',
                0,
                $data['client_name'],
                'user'
            );

            return json(['code' => 0, 'msg' => '修改成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '修改失败：' . $e->getMessage()]);
        }
    }

    /**
     * 撤销工单（仅待接单状态可撤销）
     */
    public function cancelOrder()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        $id        = $this->request->post('id', 0);
        $reason    = $this->request->post('reason', '');
        $visitorId = $this->request->post('visitor_id', '');

        if (empty($id)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $order = RepairOrder::find($id);
        if (!$order) {
            return json(['code' => 1, 'msg' => '工单不存在']);
        }
        if ((int)$order->status !== RepairOrder::STATUS_PENDING) {
            return json(['code' => 1, 'msg' => '只能撤销待接单状态的工单']);
        }

        // 权限校验
        if (!empty($visitorId) && $order->visitor_id !== $visitorId) {
            return json(['code' => 1, 'msg' => '无权操作该工单']);
        }

        try {
            $order->status = RepairOrder::STATUS_CANCELLED;
            $order->cancel_reason = $reason ?: '';
            $order->save();

            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_CANCELLED,
                '用户撤销工单',
                $reason ?: '用户主动撤销了该工单',
                0,
                $order->client_name ?? '',
                'user'
            );

            return json(['code' => 0, 'msg' => '工单已撤销']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '撤销失败：' . $e->getMessage()]);
        }
    }
}
