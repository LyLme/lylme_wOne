<?php
declare(strict_types=1);

namespace app\controller;

use app\controller\FrontBase;
use app\model\Article;
use app\model\ArticleCategory;
use app\model\CaseInfo;
use think\facade\View;

/**
 * 前台客户案例控制器
 * 行业分类优先从数据库 article_category 表（type='case'）读取，配置文件兜底
 */
class Cases extends FrontBase
{
    /**
     * 获取行业分类（数据库优先，配置文件兜底）
     */
    private function getIndustries(): array
    {
        // 数据库优先：从 article_category 表读取案例分类
        $dbCategories = ArticleCategory::getByType('case');
        if (!empty($dbCategories)) {
            $industries = ['all' => '全部'];
            foreach ($dbCategories as $cat) {
                // 使用分类名称作为 key（对应 CaseInfo.industry 字段存储的值）
                $industries[$cat['name']] = $cat['name'];
            }
            return $industries;
        }

        // 配置文件兜底
        return $this->siteConfig['case_industries'] ?? [
            'all'        => '全部',
            'government' => '政府机关',
            'education'  => '教育机构',
            'enterprise' => '企业单位',
            'medical'    => '医疗机构',
            'finance'    => '金融机构',
            'other'      => '其他行业',
        ];
    }

    /**
     * 案例默认空数据（防止模板访问不存在的 key 报错）
     */
    private function emptyCaseInfo(): array
    {
        return [
            'industry'     => '',
            'devices'      => '',
            'service_date' => '',
            'requirement'  => '',
            'solution'     => '',
            'result'       => '',
            'images'       => '',
            'cover'        => '',
            'client_name'  => '',
        ];
    }

    /**
     * 案例列表页
     */
    public function index()
    {
        $industry = $this->request->get('industry', 'all');
        $page     = (int)$this->request->get('page', 1);

        $query = Article::where('status', 1)->where('type', 'case');

        if ($industry !== 'all') {
            $caseIds = CaseInfo::where('industry', $industry)->column('article_id');
            if (!empty($caseIds)) {
                $query->whereIn('id', $caseIds);
            } else {
                $query->where('id', 0);
            }
        }

        $list = $query->order('sort', 'asc')
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => 9,
                'page'      => $page,
            ]);

        $emptyInfo = $this->emptyCaseInfo();
        $caseData = [];
        foreach ($list->items() as $item) {
            $info = CaseInfo::where('article_id', $item->id)->find();
            $caseData[] = [
                'article' => $item->toArray(),
                'info'    => $info ? $info->toArray() : $emptyInfo,
            ];
        }

        return View::fetch('index/cases', [
            'case_data'        => $caseData,
            'list'             => $list,
            'industries'       => $this->getIndustries(),
            'current_industry' => $industry,
            'page_title'       => '客户案例 - ' . $this->site_name,
        ]);
    }

    /**
     * 案例详情页
     */
    public function detail($id)
    {
        $article = Article::where('status', 1)
            ->where('type', 'case')
            ->find($id);

        if (!$article) {
            return response(View::fetch('index/error/404', ['message' => '案例不存在']))->code(404);
        }

        $article->incViewCount();

        $caseInfo = CaseInfo::where('article_id', $id)->find();
        $caseInfo = $caseInfo ? $caseInfo->toArray() : $this->emptyCaseInfo();

        $relatedCases = Article::where('status', 1)
            ->where('type', 'case')
            ->where('id', '<>', $id)
            ->order('sort', 'asc')
            ->limit(3)
            ->select()
            ->toArray();

        return View::fetch('index/case_detail', [
            'article'       => $article->toArray(),
            'case_info'     => $caseInfo,
            'related_cases' => $relatedCases,
            'page_title'    => $article->title . ' - 客户案例 - ' . $this->site_name,
        ]);
    }
}
