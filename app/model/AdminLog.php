<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\facade\Request;

/**
 * 管理员操作日志模型
 */
class AdminLog extends Model
{
    protected $name = 'admin_log';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $type = [
        'id'       => 'integer',
        'admin_id' => 'integer',
    ];

    // 操作类型常量
    const ACTION_LOGIN       = 'login';
    const ACTION_LOGOUT      = 'logout';
    const ACTION_CREATE      = 'create';
    const ACTION_UPDATE      = 'update';
    const ACTION_DELETE      = 'delete';
    const ACTION_STATUS      = 'status';
    const ACTION_UPLOAD      = 'upload';
    const ACTION_CONFIG      = 'config';
    const ACTION_REPAIR      = 'repair';
    const ACTION_PASSWORD    = 'password';
    const ACTION_CLEAR_CACHE = 'clear_cache';

    /**
     * 记录日志
     */
    public static function record(int $adminId, string $adminName, string $action, string $content): void
    {
        try {
            self::create([
                'admin_id'   => $adminId,
                'admin_name' => $adminName,
                'action'     => $action,
                'content'    => mb_substr($content, 0, 255),
                'ip'         => Request::ip(),
                'user_agent' => mb_substr(Request::header('user-agent', ''), 0, 255),
                'create_time'=> date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            // 日志记录失败不应影响主流程
        }
    }

    /**
     * 动作中文名
     */
    public static function getActionText(string $action): string
    {
        return match ($action) {
            self::ACTION_LOGIN       => '登录',
            self::ACTION_LOGOUT      => '退出',
            self::ACTION_CREATE      => '新增',
            self::ACTION_UPDATE      => '修改',
            self::ACTION_DELETE      => '删除',
            self::ACTION_STATUS      => '状态变更',
            self::ACTION_UPLOAD      => '上传',
            self::ACTION_CONFIG      => '配置',
            self::ACTION_REPAIR      => '工单处理',
            self::ACTION_PASSWORD    => '密码修改',
            self::ACTION_CLEAR_CACHE => '清除缓存',
            default                  => $action,
        };
    }

    /**
     * 动作图标
     */
    public static function getActionIcon(string $action): string
    {
        return match ($action) {
            self::ACTION_LOGIN       => 'fa-sign-in-alt',
            self::ACTION_LOGOUT      => 'fa-sign-out-alt',
            self::ACTION_CREATE      => 'fa-plus-circle',
            self::ACTION_UPDATE      => 'fa-edit',
            self::ACTION_DELETE      => 'fa-trash-alt',
            self::ACTION_STATUS      => 'fa-toggle-on',
            self::ACTION_UPLOAD      => 'fa-cloud-upload-alt',
            self::ACTION_CONFIG      => 'fa-cogs',
            self::ACTION_REPAIR      => 'fa-wrench',
            self::ACTION_PASSWORD    => 'fa-key',
            self::ACTION_CLEAR_CACHE => 'fa-broom',
            default                  => 'fa-circle',
        };
    }
}
