<?php
declare(strict_types=1);

namespace app\controller;

use app\controller\FrontBase;
use app\model\Partner;
use app\model\SinglePage;
use think\facade\View;

/**
 * 前台关于我们控制器
 * 所有展示数据从数据库读取
 */
class About extends FrontBase
{
    /**
     * 关于我们页面
     */
    public function index()
    {
        // 公司简介（数据库单页优先，配置文件兜底）
        $companyIntro = $this->getSinglePageContent('about');

        // 发展历程
        $timeline = $this->siteConfig['about_timeline'] ?? [];

        // 资质荣誉
        $qualifications = $this->siteConfig['about_qualifications'] ?? [];

        // 核心团队
        $teamMembers = $this->siteConfig['about_team'] ?? [];

        // 招投标信息（新增）
        $biddingInfo = $this->siteConfig['about_bidding'] ?? [];

        // 成立年限展示文案
        $foundedYear = (int)($this->siteConfig['company_founded'] ?? date('Y'));
        $currentYear = date('Y');
        $foundedSince = $currentYear - $foundedYear > 0 ? $currentYear - $foundedYear . '年' : '多年';

        // 合作伙伴：数据库优先，配置文件兜底
        $partners = Partner::getActivePartners();
        if (empty($partners)) {
            $partners = $this->siteConfig['about_partners'] ?? [];
        }

        return View::fetch('index/about', [
            'company_intro'      => $companyIntro,
            'timeline'           => $timeline,
            'qualifications'     => $qualifications,
            'team_members'       => $teamMembers,
            'bidding_info'       => $biddingInfo,
            'partners'           => $partners,
            'founded_since'      => $foundedSince,
            'page_title'         => '关于我们 - ' . $this->site_name,
        ]);
    }

    /**
     * 获取单页内容（数据库优先）
     */
    private function getSinglePageContent(string $key): string
    {
        $page = SinglePage::where('key', $key)->find();
        if ($page && !empty($page->content)) {
            return $page->content;
        }

        // 从配置获取默认内容
        $defaultContent = $this->siteConfig['about_intro'] ?? '';
        if ($defaultContent) {
            // 替换变量占位符
            $defaultContent = str_replace(
                [':company_founded', ':company_city'],
                [$this->siteConfig['company_founded'] ?? '', $this->siteConfig['company_city'] ?? ''],
                $defaultContent
            );
            return $defaultContent;
        }

        return '<p>暂无公司简介</p>';
    }
}
