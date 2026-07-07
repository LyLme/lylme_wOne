<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 报修工单时间轴记录模型
 */
class RepairTimeline extends Model
{
    protected $name = 'repair_timeline';
    protected $autoWriteTimestamp = false;
    
    protected $type = [
        'id'          => 'integer',
        'repair_id'   => 'integer',
        'operator_id' => 'integer',
    ];

    protected $field = [
        'id', 'repair_id', 'action', 'title', 'content',
        'operator_id', 'operator_name', 'operator_type',
        'create_time'
    ];
    
    // 操作类型常量
    const ACTION_CREATED   = 'created';   // 提交报修
    const ACTION_ACCEPTED  = 'accepted';  // 已接单
    const ACTION_PAUSED    = 'paused';    // 已暂停
    const ACTION_RESUMED   = 'resumed';   // 恢复处理
    const ACTION_COMPLETED = 'completed'; // 已完成
    const ACTION_CANCELLED = 'cancelled'; // 已撤销
    const ACTION_EDITED    = 'edited';    // 用户编辑
    const ACTION_REMARK    = 'remark';    // 备注
    
    /**
     * 获取操作类型文本
     */
    public static function getActionText(string $action): string
    {
        return match ($action) {
            self::ACTION_CREATED   => '提交报修',
            self::ACTION_ACCEPTED  => '已接单',
            self::ACTION_PAUSED    => '已暂停',
            self::ACTION_RESUMED   => '恢复处理',
            self::ACTION_COMPLETED => '已完成',
            self::ACTION_CANCELLED => '已撤销',
            self::ACTION_EDITED    => '编辑工单',
            self::ACTION_REMARK    => '添加备注',
            default                => $action,
        };
    }
    
    /**
     * 获取操作类型图标
     */
    public static function getActionIcon(string $action): string
    {
        return match ($action) {
            self::ACTION_CREATED   => 'fa-edit',
            self::ACTION_ACCEPTED  => 'fa-user-plus',
            self::ACTION_PAUSED    => 'fa-pause',
            self::ACTION_RESUMED   => 'fa-play',
            self::ACTION_COMPLETED => 'fa-check',
            self::ACTION_CANCELLED => 'fa-ban',
            self::ACTION_EDITED    => 'fa-pencil',
            self::ACTION_REMARK    => 'fa-comment',
            default                => 'fa-circle',
        };
    }
    
    /**
     * 记录时间轴事件
     */
    public static function record(int $repairId, string $action, string $title = '', string $content = '', int $operatorId = 0, string $operatorName = '', string $operatorType = 'system'): void
    {
        $model = new static();
        $model->save([
            'repair_id'     => $repairId,
            'action'        => $action,
            'title'         => $title ?: self::getActionText($action),
            'content'       => $content,
            'operator_id'   => $operatorId,
            'operator_name' => $operatorName,
            'operator_type' => $operatorType,
            'create_time'   => date('Y-m-d H:i:s'),
        ]);
    }
}
