<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 文章模型
 */
class Article extends Model
{
    protected $name = 'article';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'id'          => 'integer',
        'category_id' => 'integer',
        'view_count'  => 'integer',
        'sort'        => 'integer',
        'status'      => 'integer',
    ];
    
    /**
     * 关联分类
     */
    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id', 'id');
    }
    
    /**
     * 关联案例信息
     */
    public function caseInfo()
    {
        return $this->hasOne(CaseInfo::class, 'article_id', 'id');
    }
    
    /**
     * 搜索器 - 标题
     */
    public function searchTitleAttr($query, $value)
    {
        if (!empty($value)) {
            $query->whereLike('title', '%' . $value . '%');
        }
    }
    
    /**
     * 搜索器 - 分类
     */
    public function searchCategoryIdAttr($query, $value)
    {
        if (!empty($value)) {
            $query->where('category_id', $value);
        }
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
     * 增加浏览量
     */
    public function incViewCount(): void
    {
        $this->view_count++;
        $this->save();
    }
}
