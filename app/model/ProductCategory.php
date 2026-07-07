<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 产品分类模型
 */
class ProductCategory extends Model
{
    protected $name = 'product_category';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'id'        => 'integer',
        'parent_id' => 'integer',
        'sort'      => 'integer',
        'status'    => 'integer',
    ];
    
    /**
     * 关联产品
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
    
    /**
     * 获取分类树
     */
    public static function getCategoryTree(int $parentId = 0): array
    {
        $categories = self::where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        return self::buildTree($categories, $parentId);
    }
    
    /**
     * 递归构建树
     */
    public static function buildTree(array $categories, int $parentId): array
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = self::buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }
        return $tree;
    }
    
    /**
     * 获取所有子分类ID
     */
    public static function getChildIds(int $parentId): array
    {
        $ids = [$parentId];
        $children = self::where('parent_id', $parentId)->column('id');
        foreach ($children as $childId) {
            $ids = array_merge($ids, self::getChildIds((int)$childId));
        }
        return $ids;
    }
}
