<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\AdminLog;
use app\model\RepairOrder;
use app\model\RepairTimeline;
use think\facade\View;

/**
 * 后台报修工单管理 - 支持完整工作流
 */
class Repair extends Base
{
    public function index()
    {
        $status = $this->request->get('status', '');
        $keyword = $this->request->get('keyword', '');
        $page   = (int)$this->request->get('page', 1);

        $query = RepairOrder::order('id', 'desc');
        if ($status !== '') $query->where('status', (int)$status);
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('order_no', '%' . $keyword . '%')
                  ->whereOr('client_name', 'like', '%' . $keyword . '%')
                  ->whereOr('phone', 'like', '%' . $keyword . '%');
            });
        }

        $list = $query->paginate(['list_rows' => 15, 'page' => $page]);

        // 统计各状态数量
        $counts = [
            'pending'    => RepairOrder::where('status', RepairOrder::STATUS_PENDING)->count(),
            'processing' => RepairOrder::where('status', RepairOrder::STATUS_PROCESSING)->count(),
            'paused'     => RepairOrder::where('status', RepairOrder::STATUS_PAUSED)->count(),
            'completed'  => RepairOrder::where('status', RepairOrder::STATUS_COMPLETED)->count(),
            'cancelled'  => RepairOrder::where('status', RepairOrder::STATUS_CANCELLED)->count(),
            'total'      => RepairOrder::count(),
        ];

        return View::fetch('admin/repair/index', [
            'page_title' => '报修工单',
            'list'       => $list,
            'status'     => $status,
            'keyword'    => $keyword,
            'counts'     => $counts,
        ]);
    }

    /**
     * 工单详情（含时间轴）
     */
    public function detailJson()
    {
        $id = $this->request->get('id', 0);
        $order = RepairOrder::find($id);
        if (!$order) return json(['code' => 1, 'msg' => '工单不存在']);

        $data = $order->toArray();
        $data['status_text'] = RepairOrder::getStatusText((int)$data['status']);
        $data['images'] = !empty($data['images']) ? json_decode($data['images'], true) : [];
        $data['service_price'] = floatval($order->service_price ?? 0);

        // 获取时间轴
        $timeline = RepairTimeline::where('repair_id', $id)->order('id', 'asc')->select();
        $data['timeline'] = [];
        foreach ($timeline as $t) {
            $data['timeline'][] = [
                'action'        => $t->action,
                'title'         => $t->title,
                'content'       => $t->content,
                'operator_name' => $t->operator_name,
                'operator_type' => $t->operator_type,
                'create_time'   => $t->create_time,
                'icon'          => RepairTimeline::getActionIcon($t->action),
            ];
        }

        return json(['code' => 0, 'data' => $data]);
    }

    /**
     * 接单 - 待接单 → 处理中
     */
    public function accept()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        $order = RepairOrder::find($id);
        if (!$order) return $this->error('工单不存在');
        if ((int)$order->status !== RepairOrder::STATUS_PENDING) {
            return $this->error('只能接单待接单状态的工单');
        }

        try {
            $adminName = $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? '';
            $order->status = RepairOrder::STATUS_PROCESSING;
            $order->handler_id = $this->adminInfo['id'] ?? 0;
            $order->handler_name = $adminName;
            $order->accepted_time = date('Y-m-d H:i:s');
            $order->save();

            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_ACCEPTED,
                '已接单',
                '服务人员 ' . $adminName . ' 已接单，开始处理',
                $this->adminInfo['id'] ?? 0,
                $adminName,
                'admin'
            );

            $this->log(AdminLog::ACTION_REPAIR, '接单工单：' . $order->order_no);
            return $this->success(null, '接单成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }

    /**
     * 暂停工单 - 处理中 → 已暂停
     */
    public function pause()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id = $this->request->post('id', 0);
        $reason = $this->request->post('reason', '');

        if (empty($id)) return $this->error('参数错误');

        $order = RepairOrder::find($id);
        if (!$order) return $this->error('工单不存在');
        if ((int)$order->status !== RepairOrder::STATUS_PROCESSING) {
            return $this->error('只能暂停处理中的工单');
        }

        try {
            $adminName = $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? '';
            $order->status = RepairOrder::STATUS_PAUSED;
            $order->pause_reason = $reason ?: '';
            $order->paused_time = date('Y-m-d H:i:s');
            $order->save();

            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_PAUSED,
                '工单已暂停',
                $reason ?: '服务人员 ' . $adminName . ' 暂停了工单',
                $this->adminInfo['id'] ?? 0,
                $adminName,
                'admin'
            );

            $this->log(AdminLog::ACTION_REPAIR, '暂停工单：' . $order->order_no);
            return $this->success(null, '工单已暂停');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }

    /**
     * 恢复处理 - 已暂停 → 处理中
     */
    public function resume()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        $order = RepairOrder::find($id);
        if (!$order) return $this->error('工单不存在');
        if ((int)$order->status !== RepairOrder::STATUS_PAUSED) {
            return $this->error('只能恢复已暂停的工单');
        }

        try {
            $adminName = $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? '';
            $order->status = RepairOrder::STATUS_PROCESSING;
            $order->save();

            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_RESUMED,
                '恢复处理',
                '服务人员 ' . $adminName . ' 恢复了工单处理',
                $this->adminInfo['id'] ?? 0,
                $adminName,
                'admin'
            );

            $this->log(AdminLog::ACTION_REPAIR, '恢复工单：' . $order->order_no);
            return $this->success(null, '工单已恢复');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }

    /**
     * 完成工单（含回执 + 服务价格）
     */
    public function complete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id = $this->request->post('id', 0);
        $receipt = $this->request->post('receipt', '');
        $price = $this->request->post('price', '');

        if (empty($id)) return $this->error('参数错误');

        $order = RepairOrder::find($id);
        if (!$order) return $this->error('工单不存在');

        $currentStatus = (int)$order->status;
        if ($currentStatus !== RepairOrder::STATUS_PROCESSING && $currentStatus !== RepairOrder::STATUS_PAUSED) {
            return $this->error('只能完成处理中或已暂停的工单');
        }

        try {
            $adminName = $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? '';
            $order->status = RepairOrder::STATUS_COMPLETED;
            $order->completion_receipt = $receipt ?: '';
            $order->service_price = $price !== '' ? (float)$price : 0;
            // 备注追加回执信息，不覆盖已有备注
            if ($receipt) {
                $order->remark = trim(($order->remark ? $order->remark . "\n" : '') . '[回执] ' . $receipt);
            }
            $order->completed_time = date('Y-m-d H:i:s');
            $order->save();

            $content = '服务人员 ' . $adminName . ' 完成工单处理';
            if (floatval($order->service_price) > 0) {
                $content .= '，服务价格：¥' . number_format(floatval($order->service_price), 2);
            }
            if ($receipt) {
                $content .= "\n回执：{$receipt}";
            }

            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_COMPLETED,
                '工单已完成',
                $content,
                $this->adminInfo['id'] ?? 0,
                $adminName,
                'admin'
            );

            $this->log(AdminLog::ACTION_REPAIR, '完成工单：' . $order->order_no);
            return $this->success(null, '工单已完成');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }

    /**
     * 更新状态 + 备注（保留通用方法，带状态转换校验）
     */
    public function updateStatus()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id     = $this->request->post('id', 0);
        $status = $this->request->post('status', 0);
        $remark = $this->request->post('remark', '');

        if (empty($id)) return $this->error('参数错误');

        $order = RepairOrder::find($id);
        if (!$order) return $this->error('工单不存在');

        // 校验目标状态合法性
        $oldStatus = (int)$order->status;
        $newStatus = (int)$status;
        $validStatuses = [
            RepairOrder::STATUS_PENDING,
            RepairOrder::STATUS_PROCESSING,
            RepairOrder::STATUS_PAUSED,
            RepairOrder::STATUS_COMPLETED,
            RepairOrder::STATUS_CANCELLED,
        ];
        if (!in_array($newStatus, $validStatuses)) {
            return $this->error('无效的状态值');
        }
        // 仅允许合法的状态转换
        $allowedTransitions = [
            RepairOrder::STATUS_PENDING    => [RepairOrder::STATUS_PROCESSING, RepairOrder::STATUS_CANCELLED],
            RepairOrder::STATUS_PROCESSING => [RepairOrder::STATUS_PAUSED, RepairOrder::STATUS_COMPLETED, RepairOrder::STATUS_CANCELLED],
            RepairOrder::STATUS_PAUSED     => [RepairOrder::STATUS_PROCESSING, RepairOrder::STATUS_COMPLETED, RepairOrder::STATUS_CANCELLED],
            RepairOrder::STATUS_COMPLETED  => [],
            RepairOrder::STATUS_CANCELLED  => [],
        ];
        if ($oldStatus !== $newStatus && !in_array($newStatus, $allowedTransitions[$oldStatus] ?? [])) {
            return $this->error('不允许从"' . RepairOrder::getStatusText($oldStatus) . '"变更为"' . RepairOrder::getStatusText($newStatus) . '"');
        }

        try {
            $order->status = $newStatus;
            if ($remark !== '') $order->remark = $remark;
            $order->handler_id = $this->adminInfo['id'] ?? 0;
            $order->handler_name = $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? '';
            $order->save();

            // 记录时间轴
            if ($oldStatus !== $newStatus) {
                $adminName = $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? '';
                RepairTimeline::record(
                    $order->id,
                    RepairTimeline::ACTION_REMARK,
                    '状态变更：' . RepairOrder::getStatusText($newStatus),
                    ($remark ?: '无备注') . ' (由 ' . $adminName . ' 操作)',
                    $this->adminInfo['id'] ?? 0,
                    $adminName,
                    'admin'
                );
            }

            $this->log(AdminLog::ACTION_REPAIR, '修改工单状态：' . $order->order_no . ' → ' . RepairOrder::getStatusText($newStatus));
            return $this->success(null, '状态更新成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '更新失败：'));
        }
    }

    /**
     * 删除工单（同时删除时间轴记录；仅超级管理员可操作）
     */
    public function delete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $this->checkSuperAdmin();

        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        try {
            $order = RepairOrder::find($id);
            if (!$order) return $this->error('工单不存在');

            // 删除关联时间轴
            RepairTimeline::where('repair_id', $id)->delete();
            $orderNo = $order->order_no;
            $order->delete();

            $this->log(AdminLog::ACTION_DELETE, '删除工单：' . $orderNo);
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '删除失败：'));
        }
    }

    /**
     * 添加工单备注
     */
    public function addRemark()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id = $this->request->post('id', 0);
        $remark = $this->request->post('remark', '');

        if (empty($id) || empty($remark)) return $this->error('参数错误');

        $order = RepairOrder::find($id);
        if (!$order) return $this->error('工单不存在');

        try {
            $adminName = $this->adminInfo['nickname'] ?? $this->adminInfo['username'] ?? '';
            $order->remark = trim(($order->remark ? $order->remark . "\n" : '') . '[' . date('m-d H:i') . '] ' . $remark);
            $order->save();

            RepairTimeline::record(
                $order->id,
                RepairTimeline::ACTION_REMARK,
                '添加备注',
                $remark,
                $this->adminInfo['id'] ?? 0,
                $adminName,
                'admin'
            );

            $this->log(AdminLog::ACTION_REPAIR, '工单备注：' . $order->order_no);
            return $this->success(null, '备注已添加');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }
}
