<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 产品模型
 */
class Product extends Model
{
    protected $name = 'product';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $json = ['imgs', 'params'];
    protected $jsonAssoc = true;
    
    protected $type = [
        'id'            => 'integer',
        'category_id'   => 'integer',
        'price_type'    => 'integer',
        'price_min'     => 'float',
        'price_max'     => 'float',
        'is_hot'        => 'integer',
        'is_recommend'  => 'integer',
        'view_count'    => 'integer',
        'sort'          => 'integer',
        'status'        => 'integer',
    ];
    
    /**
     * 关联分类
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }
    
    /**
     * 搜索器 - 产品名称
     */
    public function searchNameAttr($query, $value)
    {
        if (!empty($value)) {
            $query->whereLike('name', '%' . $value . '%');
        }
    }
    
    /**
     * 搜索器 - 分类
     */
    public function searchCategoryIdAttr($query, $value)
    {
        if (!empty($value)) {
            $childIds = ProductCategory::getChildIds((int)$value);
            $query->whereIn('category_id', $childIds);
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
     * 搜索器 - 热门
     */
    public function searchIsHotAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('is_hot', $value);
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
    
    /**
     * 获取价格文本
     */
    public function getPriceTextAttr(): string
    {
        if ($this->price_type == 0) {
            return '询价';
        }
        if ($this->price_min && $this->price_max) {
            return '￥' . number_format((float)$this->price_min, 0) . ' - ￥' . number_format((float)$this->price_max, 0);
        }
        if ($this->price_min) {
            return '￥' . number_format((float)$this->price_min, 0) . '起';
        }
        return '询价';
    }
}
