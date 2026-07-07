<?php
declare(strict_types=1);

namespace app\controller;

use app\controller\FrontBase;
use app\model\Article as ArticleModel;
use app\model\ArticleCategory;
use think\facade\View;

/**
 * 前台新闻资讯控制器
 */
class Article extends FrontBase
{
    /**
     * 新闻列表页
     */
    public function index()
    {
        $categoryId = $this->request->get('category_id', 0);
        $keyword    = $this->request->get('keyword', '');
        $page       = (int)$this->request->get('page', 1);

        // 新闻分类
        $categories = ArticleCategory::getByType('news');

        $query = ArticleModel::where('status', 1)
            ->where('type', 'news');

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($keyword)) {
            $query->whereLike('title', '%' . $keyword . '%');
        }

        $list = $query->order('create_time', 'desc')
            ->paginate([
                'list_rows' => 10,
                'page'      => $page,
            ]);

        // 当前分类
        $currentCategory = [];
        if (!empty($categoryId)) {
            $currentCategory = ArticleCategory::find($categoryId);
            $currentCategory = $currentCategory ? $currentCategory->toArray() : [];
        }

        return View::fetch('index/news', [
            'list'             => $list,
            'categories'       => $categories,
            'current_category' => $currentCategory,
            'category_id'      => $categoryId,
            'keyword'          => $keyword,
            'page_title'       => '新闻资讯 - ' . $this->site_name,
        ]);
    }

    /**
     * 分类筛选
     */
    public function category($slug)
    {
        // 兜底：如果路由 news/:slug 错误匹配了 news/detail/ID 的请求，自动跳转到详情页
        if (preg_match('/^detail\/(\d+)$/', $slug, $matches)) {
            return $this->detail((int)$matches[1]);
        }

        $category = ArticleCategory::where('status', 1)
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->whereOr('id', $slug);
            })
            ->find();

        if (!$category) {
            // 兜底：slug 可能是纯数字ID，尝试直接访问详情
            if (is_numeric($slug)) {
                return $this->detail((int)$slug);
            }
            return View::fetch('index/error/404', ['message' => '分类不存在']);
        }

        $list = ArticleModel::where('status', 1)
            ->where('type', 'news')
            ->where('category_id', $category->id)
            ->order('create_time', 'desc')
            ->paginate(10);

        $categories = ArticleCategory::getByType('news');

        return View::fetch('index/news', [
            'list'             => $list,
            'categories'       => $categories,
            'current_category' => $category->toArray(),
            'category_id'      => $category->id,
            'keyword'          => '',
            'page_title'       => $category->name . ' - 新闻资讯 - ' . $this->site_name,
        ]);
    }

    /**
     * 新闻详情页
     */
    public function detail($id)
    {
        $article = ArticleModel::with('category')->where('status', 1)
            ->where('type', 'news')
            ->find($id);

        if (!$article) {
            return View::fetch('index/error/404', ['message' => '文章不存在']);
        }

        // 增加浏览量
        $article->incViewCount();

        // 上一篇
        $prevArticle = ArticleModel::where('status', 1)
            ->where('type', 'news')
            ->where('id', '<', $id)
            ->order('id', 'desc')
            ->find();

        // 下一篇
        $nextArticle = ArticleModel::where('status', 1)
            ->where('type', 'news')
            ->where('id', '>', $id)
            ->order('id', 'asc')
            ->find();

        // 相关新闻（同分类）
        $relatedNews = ArticleModel::where('status', 1)
            ->where('type', 'news')
            ->where('category_id', $article->category_id)
            ->where('id', '<>', $id)
            ->order('create_time', 'desc')
            ->limit(4)
            ->select()
            ->toArray();

        return View::fetch('index/news_detail', [
            'article'      => $article->toArray(),
            'prev_article' => $prevArticle ? $prevArticle->toArray() : null,
            'next_article' => $nextArticle ? $nextArticle->toArray() : null,
            'related_news' => $relatedNews,
            'page_title'   => $article->title . ' - 新闻资讯 - ' . $this->site_name,
        ]);
    }
}
