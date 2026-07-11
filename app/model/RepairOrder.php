<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 服务工单模型
 */
class RepairOrder extends Model
{
    protected $name = 'repair_order';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'id'            => 'integer',
        'status'        => 'integer',
        'handler_id'    => 'integer',
        'service_price' => 'float',
    ];

    protected $field = [
        'id', 'order_no', 'visitor_id', 'client_name', 'company', 'phone',
        'address', 'description', 'images', 'status', 'service_price',
        'remark', 'completion_receipt', 'cancel_reason', 'pause_reason',
        'handler_id', 'handler_name', 'handler_note',
        'accepted_time', 'paused_time', 'completed_time',
        'ip', 'create_time', 'update_time'
    ];
    
    // 状态常量
    const STATUS_PENDING    = 0; // 待接单
    const STATUS_PROCESSING = 1; // 处理中
    const STATUS_COMPLETED  = 2; // 已完成
    const STATUS_CANCELLED  = 3; // 已撤销
    const STATUS_PAUSED     = 4; // 已暂停
    
    /**
     * 状态文本
     */
    public static function getStatusText(int $status): string
    {
        return match ($status) {
            self::STATUS_PENDING    => '待接单',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_COMPLETED  => '已完成',
            self::STATUS_CANCELLED  => '已撤销',
            self::STATUS_PAUSED     => '已暂停',
            default                 => '未知',
        };
    }
    
    /**
     * 状态颜色映射(Bootstrap badge)
     */
    public static function getStatusColor(int $status): string
    {
        return match ($status) {
            self::STATUS_PENDING    => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info',
            self::STATUS_COMPLETED  => 'bg-success',
            self::STATUS_CANCELLED  => 'bg-secondary',
            self::STATUS_PAUSED     => 'bg-danger',
            default                 => 'bg-secondary',
        };
    }
    
    /**
     * 获取状态对应的图标
     */
    public static function getStatusIcon(int $status): string
    {
        return match ($status) {
            self::STATUS_PENDING    => 'fa-clock-o',
            self::STATUS_PROCESSING => 'fa-spinner fa-pulse',
            self::STATUS_COMPLETED  => 'fa-check-circle',
            self::STATUS_CANCELLED  => 'fa-times-circle',
            self::STATUS_PAUSED     => 'fa-pause-circle',
            default                 => 'fa-question-circle',
        };
    }
    
    /**
     * 获取流程步骤(用于时间轴)
     * 返回按流程定义的所有步骤及当前到达的步骤
     */
    public static function getFlowSteps(int $currentStatus): array
    {
        $steps = [
            ['key' => 'created',    'label' => '预约上门',  'icon' => 'fa-edit',      'status_field' => null],
            ['key' => 'accepted',   'label' => '已接单',    'icon' => 'fa-user-plus', 'status_field' => self::STATUS_PROCESSING],
            ['key' => 'paused',     'label' => '暂停',      'icon' => 'fa-pause',     'status_field' => self::STATUS_PAUSED],
            ['key' => 'completed',  'label' => '已完成',    'icon' => 'fa-check',     'status_field' => self::STATUS_COMPLETED],
        ];
        // 已撤销终止流程
        if ($currentStatus === self::STATUS_CANCELLED) {
            $steps[] = ['key' => 'cancelled', 'label' => '已撤销', 'icon' => 'fa-ban', 'status_field' => self::STATUS_CANCELLED];
        }
        return $steps;
    }
    
    /**
     * 搜索器 - 状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }
    
    /**
     * 搜索器 - 手机号
     */
    public function searchPhoneAttr($query, $value)
    {
        if (!empty($value)) {
            $query->whereLike('phone', '%' . $value . '%');
        }
    }
    
    /**
     * 搜索器 - 客户姓名
     */
    public function searchClientNameAttr($query, $value)
    {
        if (!empty($value)) {
            $query->whereLike('client_name', '%' . $value . '%');
        }
    }
    
    /**
     * 生成工单号（基于时间戳+微秒+随机数，降低碰撞风险）
     */
    public static function generateOrderNo(): string
    {
        $micro = substr((string)microtime(true), -6);
        $rand  = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        return 'RP' . date('YmdHis') . $micro . $rand;
    }
}
