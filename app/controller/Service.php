<?php
declare(strict_types=1);

namespace app\controller;

use app\controller\FrontBase;
use app\model\Product;
use think\facade\View;

/**
 * 前台服务控制器
 * 所有服务数据从数据库配置读取
 */
class Service extends FrontBase
{
    /**
     * 获取服务分类（配置项，缓存到属性）
     */
    private function getServiceTypes(): array
    {
        return $this->siteConfig['service_types'] ?? [];
    }

    /**
     * 获取服务详情内容（配置项）
     */
    private function getServiceDetailContent(string $slug): ?array
    {
        $contentMap = $this->siteConfig['service_detail_content'] ?? [];
        return $contentMap[$slug] ?? null;
    }

    /**
     * 获取服务流程图（配置项）
     */
    private function getFlowSteps(): array
    {
        return $this->siteConfig['service_flow_steps'] ?? [];
    }

    /**
     * 驼峰方法名转 slug 兜底
     */
    public function __call($method, $args)
    {
        $slug = strtolower((string) preg_replace('/([A-Z])/', '-$1', lcfirst($method)));
        $slug = ltrim($slug, '-');

        $serviceTypes = $this->getServiceTypes();
        if (isset($serviceTypes[$slug])) {
            return $this->detail($slug);
        }
        throw new \think\exception\HttpException(404, '页面不存在');
    }

    /**
     * 服务支持列表页
     */
    public function index()
    {
        $serviceTypes = $this->getServiceTypes();
        $flowSteps    = $this->getFlowSteps();

        return View::fetch('index/services', [
            'service_types' => $serviceTypes,
            'flow_steps'    => $flowSteps,
            'page_title'    => '服务支持 - ' . $this->site_name,
        ]);
    }

    /**
     * 服务详情页
     */
    public function detail($slug)
    {
        $serviceTypes = $this->getServiceTypes();
        if (!isset($serviceTypes[$slug])) {
            return View::fetch('index/error/404', ['message' => '服务不存在']);
        }

        $service = $serviceTypes[$slug];
        $content = $this->getServiceDetailContent($slug);

        // 相关产品推荐
        $relatedProducts = Product::where('status', 1)
            ->where('is_recommend', 1)
            ->order('sort', 'asc')
            ->limit(4)
            ->select()
            ->toArray();

        return View::fetch('index/service_detail', [
            'service'          => $service,
            'content'          => $content,
            'slug'             => $slug,
            'related_products' => $relatedProducts,
            'page_title'       => $service['name'] . ' - 服务支持 - ' . $this->site_name,
        ]);
    }
}
