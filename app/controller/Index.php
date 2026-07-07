<?php
declare(strict_types=1);

namespace app\controller;

use app\model\Banner;
use app\model\Product;
use app\model\Article;
use app\model\Partner;
use think\facade\View;

/**
 * 前台首页控制器
 * 展示数据从数据库查询，汇总数据从数据库配置读取
 */
class Index extends FrontBase
{
    /**
     * 首页
     */
    public function index()
    {
        // Banner列表（数据库）
        $banners = Banner::getActiveBanners();

        // 热门产品（数据库）
        $hotProducts = Product::where('status', 1)
            ->where('is_recommend', 1)
            ->order('sort', 'asc')
            ->limit(8)
            ->select()
            ->toArray();

        // 最新新闻（数据库）
        $latestNews = Article::where('status', 1)
            ->where('type', 'news')
            ->order('create_time', 'desc')
            ->limit(4)
            ->select()
            ->toArray();

        // 客户案例（数据库）
        $caseList = Article::where('status', 1)
            ->where('type', 'case')
            ->order('sort', 'asc')
            ->limit(6)
            ->select()
            ->toArray();

        // 合作伙伴：数据库优先，配置文件兜底
        $partners = Partner::getActivePartners();
        if (empty($partners)) {
            $partners = $this->siteConfig['about_partners'] ?? [];
        }

        // 核心数据（配置）
        $coreData = $this->siteConfig['home_core_data'] ?? [];

        // 服务入口（配置）
        $services = $this->siteConfig['home_services'] ?? [];

        // 服务优势（配置）
        $advantages = $this->siteConfig['home_advantages'] ?? [];

        // 关于我们简介文本（配置）
        $homeAboutText = $this->siteConfig['home_about_text'] ?? '';

        return View::fetch('index', [
            'banners'        => $banners,
            'hot_products'   => $hotProducts,
            'latest_news'    => $latestNews,
            'case_list'      => $caseList,
            'partners'           => $partners,
            'core_data'          => $coreData,
            'services'           => $services,
            'advantages'         => $advantages,
            'home_about_text'    => $homeAboutText,
            'page_title'     => $this->site_name . ' - ' . $this->site_slogan,
        ]);
    }

    /**
     * 验证码
     */
    public function captcha()
    {
        return captcha();
    }

    /**
     * 全站搜索
     */
    public function search()
    {
        $keyword = $this->request->get('keyword', '');
        $results = [];
        $total   = 0;

        if (!empty($keyword)) {
            $products = Product::where('status', 1)
                ->whereLike('name', '%' . $keyword . '%')
                ->order('sort', 'asc')
                ->limit(20)
                ->select()
                ->toArray();

            $articles = Article::where('status', 1)
                ->whereLike('title', '%' . $keyword . '%')
                ->order('create_time', 'desc')
                ->limit(20)
                ->select()
                ->toArray();

            foreach ($products as $item) {
                $results[] = [
                    'type'  => 'product',
                    'title' => $item['name'],
                    'url'   => '/product/' . $item['id'],
                    'desc'  => $item['description'] ?? '',
                ];
            }
            foreach ($articles as $item) {
                $results[] = [
                    'type'  => 'article',
                    'title' => $item['title'],
                    'url'   => '/news/detail/' . $item['id'],
                    'desc'  => $item['summary'] ?? '',
                ];
            }

            $total = count($results);
        }

        return View::fetch('index/search', [
            'keyword'    => $keyword,
            'results'    => $results,
            'total'      => $total,
            'page_title' => '搜索结果 - ' . $this->site_name,
        ]);
    }

    /**
     * XML站点地图
     */
    public function sitemap()
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        $baseUrl = $this->request->domain();

        $pages = [
            ['loc' => '/', 'priority' => '1.0'],
            ['loc' => '/products', 'priority' => '0.9'],
            ['loc' => '/services', 'priority' => '0.8'],
            ['loc' => '/cases', 'priority' => '0.8'],
            ['loc' => '/news', 'priority' => '0.8'],
            ['loc' => '/about', 'priority' => '0.7'],
            ['loc' => '/contact', 'priority' => '0.7'],
            ['loc' => '/repair', 'priority' => '0.6'],
        ];

        foreach ($pages as $page) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . $page['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <priority>' . $page['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $productIds = Product::where('status', 1)->column('id');
        foreach ($productIds as $id) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . '/product/' . $id) . '</loc>' . PHP_EOL;
            $xml .= '    <priority>0.7</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $articleIds = Article::where('status', 1)->column('id');
        foreach ($articleIds as $id) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . '/news/detail/' . $id) . '</loc>' . PHP_EOL;
            $xml .= '    <priority>0.6</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * robots.txt
     */
    public function robots()
    {
        $domain = $this->request->domain();
        $content  = 'User-agent: *' . PHP_EOL;
        $content .= 'Allow: /' . PHP_EOL;
        $content .= 'Disallow: /' . config('app.admin_path', 'admin') . '/' . PHP_EOL;
        $content .= 'Disallow: /static/uploads/' . PHP_EOL;
        $content .= 'Disallow: /route/' . PHP_EOL;
        $content .= 'Disallow: /config/' . PHP_EOL;
        $content .= 'Disallow: /database/' . PHP_EOL;
        $content .= 'Disallow: /runtime/' . PHP_EOL;
        $content .= '' . PHP_EOL;
        $content .= 'Sitemap: ' . $domain . '/sitemap.xml' . PHP_EOL;

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
