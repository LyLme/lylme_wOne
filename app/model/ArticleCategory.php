<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 文章分类模型
 */
class ArticleCategory extends Model
{
    protected $name = 'article_category';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'id'     => 'integer',
        'sort'   => 'integer',
        'status' => 'integer',
    ];
    
    /**
     * 关联文章
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }
    
    /**
     * 按类型获取分类
     */
    public static function getByType(string $type): array
    {
        return self::where('status', 1)
            ->where('type', $type)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }
}
