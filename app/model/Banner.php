<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * Banner模型
 */
class Banner extends Model
{
    protected $name = 'banner';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id'     => 'integer',
        'sort'   => 'integer',
        'status' => 'integer',
    ];

    protected $append = ['desc'];

    protected $schema = [
        'id'          => 'int',
        'title'       => 'string',
        'subtitle'    => 'string',
        'image'       => 'string',
        'link_url'    => 'string',
        'sort'        => 'int',
        'status'      => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    /**
     * desc 访问器：兼容前台模板 {$banner.desc}
     */
    public function getDescAttr($value, $data): string
    {
        return $data['subtitle'] ?? $value ?? '';
    }

    /**
     * 获取启用的Banner列表（前台使用）
     */
    public static function getActiveBanners(): array
    {
        $list = self::where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        // 为每条记录附加 desc 别名
        foreach ($list as &$item) {
            $item['desc'] = $item['subtitle'] ?? '';
        }
        return $list;
    }

    /**
     * 获取全部Banner列表（后台使用）
     */
    public static function getAllBanners(): array
    {
        return self::order('sort', 'asc')
            ->select()
            ->toArray();
    }
}
