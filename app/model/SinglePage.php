<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 单页模型
 */
class SinglePage extends Model
{
    protected $name = 'single_page';
    protected $autoWriteTimestamp = false;
    protected $updateTime = 'update_time';
    
    protected $type = [
        'id' => 'integer',
    ];
    
    /**
     * 按slug获取
     */
    public static function getBySlug(string $slug): ?array
    {
        $page = self::where('slug', $slug)->find();
        return $page ? $page->toArray() : null;
    }
}
