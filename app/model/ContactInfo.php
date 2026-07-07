<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 公司联系方式模型
 */
class ContactInfo extends Model
{
    protected $name = 'contact_info';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id'   => 'integer',
        'sort' => 'integer',
    ];

    /**
     * 获取所有启用的联系方式（按排序）
     */
    public static function getAll(): array
    {
        try {
            return self::order('sort', 'asc')->select()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取第一个电话号码（用于服务热线）
     */
    public static function getServicePhone(): string
    {
        try {
            $info = self::where('type', 'in', ['手机号', '座机'])
                ->order('sort', 'asc')
                ->find();
            return $info ? $info->value : '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
