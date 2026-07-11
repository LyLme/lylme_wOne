<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 后台管理员模型
 */
class AdminUser extends Model
{
    protected $name = 'admin_user';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id'     => 'integer',
        'status' => 'integer',
        'role'   => 'integer',
    ];

    // 角色常量
    const ROLE_SUPER = 0; // 超级管理员
    const ROLE_ADMIN = 1; // 普通管理员
    const ROLE_EDITOR = 2; // 员工

    /**
     * 角色文本
     */
    public static function getRoleText(int $role): string
    {
        return match ($role) {
            self::ROLE_SUPER  => '超级管理员',
            self::ROLE_ADMIN  => '普通管理员',
            self::ROLE_EDITOR => '员工',
            default           => '未知',
        };
    }

    /**
     * 角色颜色
     */
    public static function getRoleColor(int $role): string
    {
        return match ($role) {
            self::ROLE_SUPER  => 'bg-danger',
            self::ROLE_ADMIN  => 'bg-primary',
            self::ROLE_EDITOR => 'bg-info',
            default           => 'bg-secondary',
        };
    }

    /**
     * 密码修改器
     */
    public function setPasswordAttr($value): string
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }
}
