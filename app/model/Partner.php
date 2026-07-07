<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 合作伙伴模型
 */
class Partner extends Model
{
    protected $name = 'partner';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'id'     => 'integer',
        'sort'   => 'integer',
        'status' => 'integer',
    ];
    
    /**
     * 获取启用的合作伙伴列表
     */
    public static function getActivePartners(): array
    {
        return self::where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }
}
