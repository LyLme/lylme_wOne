<?php
declare(strict_types=1);

namespace app\controller;

use app\controller\FrontBase;
use app\model\Product as ProductModel;
use app\model\ProductCategory;
use think\facade\View;

/**
 * 前台产品控制器
 */
class Product extends FrontBase
{
    /**
     * 产品列表页
     */
    public function index()
    {
        $categoryId = $this->request->get('category_id', 0);
        $keyword    = $this->request->get('keyword', '');
        $page       = (int)$this->request->get('page', 1);

        // 分类树
        $categoryTree = ProductCategory::getCategoryTree();

        // 查询产品
        $query = ProductModel::where('status', 1);

        if (!empty($categoryId)) {
            $childIds = ProductCategory::getChildIds((int)$categoryId);
            $query->whereIn('category_id', $childIds);
        }

        if (!empty($keyword)) {
            $query->whereLike('name', '%' . $keyword . '%');
        }

        $list = $query->order('is_hot', 'desc')
            ->order('sort', 'asc')
            ->order('id', 'desc')
            ->paginate([
                'list_rows' => 12,
                'page'      => $page,
            ]);

        // 当前分类信息
        $currentCategory = [];
        if (!empty($categoryId)) {
            $currentCategory = ProductCategory::find($categoryId);
            $currentCategory = $currentCategory ? $currentCategory->toArray() : [];
        }

        return View::fetch('index/products', [
            'list'            => $list,
            'category_tree'   => $categoryTree,
            'current_category'=> $currentCategory,
            'category_id'     => $categoryId,
            'keyword'         => $keyword,
            'page_title'      => '产品中心 - ' . $this->site_name,
        ]);
    }

    /**
     * 按分类查看产品
     */
    public function category($slug)
    {
        // 按 slug 查找分类（若无 slug 字段则用 id）
        $category = ProductCategory::where('status', 1)
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->whereOr('id', $slug);
            })
            ->find();

        if (!$category) {
            return View::fetch('index/error/404', ['message' => '分类不存在']);
        }

        $childIds = ProductCategory::getChildIds($category->id);

        $list = ProductModel::where('status', 1)
            ->whereIn('category_id', $childIds)
            ->order('is_hot', 'desc')
            ->order('sort', 'asc')
            ->paginate(12);

        $categoryTree = ProductCategory::getCategoryTree();

        return View::fetch('index/products', [
            'list'            => $list,
            'category_tree'   => $categoryTree,
            'current_category'=> $category->toArray(),
            'category_id'     => $category->id,
            'keyword'         => '',
            'page_title'      => $category->name . ' - 产品中心 - ' . $this->site_name,
        ]);
    }

    /**
     * 产品详情页
     */
    public function detail($id)
    {
        $product = ProductModel::with('category')->where('status', 1)->find($id);

        if (!$product) {
            return View::fetch('index/error/404', ['message' => '产品不存在']);
        }

        // 增加浏览量
        $product->incViewCount();

        // 产品图片（imgs 是 JSON 字段）
        $imgs = $product->imgs ?: [];
        if (!empty($product->image) && !in_array($product->image, $imgs)) {
            array_unshift($imgs, $product->image);
        }

        // 技术参数（params 是 JSON 字段）
        $params = $product->params ?: [];

        // 相关推荐（同分类下其他产品）
        $relatedProducts = ProductModel::where('status', 1)
            ->where('category_id', $product->category_id)
            ->where('id', '<>', $id)
            ->order('sort', 'asc')
            ->limit(4)
            ->select()
            ->toArray();

        return View::fetch('index/product_detail', [
            'product'    => $product->toArray(),
            'imgs'       => $imgs,
            'params'     => $params,
            'related'    => $relatedProducts,
            'page_title' => $product->name . ' - 产品中心 - ' . $this->site_name,
        ]);
    }

    /**
     * 产品搜索（AJAX）
     */
    public function search()
    {
        $keyword = $this->request->get('keyword', '');

        if (empty($keyword)) {
            return json(['code' => 0, 'data' => []]);
        }

        $list = ProductModel::where('status', 1)
            ->whereLike('name', '%' . $keyword . '%')
            ->order('sort', 'asc')
            ->limit(10)
            ->select()
            ->toArray();

        return json(['code' => 0, 'data' => $list]);
    }
}
