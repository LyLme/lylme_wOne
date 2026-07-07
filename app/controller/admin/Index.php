<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\AdminLog;
use think\facade\Db;
use think\facade\View;

/**
 * 后台仪表盘控制器
 */
class Index extends Base
{
    /**
     * 仪表盘首页
     */
    public function index()
    {
        // 统计数据
        $stats = [
            'product_count'  => Db::name('product')->where('status', 1)->count(),
            'article_count'  => Db::name('article')->where('status', 1)->count(),
            'message_count'  => Db::name('message')->where('is_read', 0)->count(),
            'repair_count'   => Db::name('repair_order')->count(),
            'banner_count'   => Db::name('banner')->where('status', 1)->count(),
            'case_count'     => Db::name('article')->where('type', 'case')->where('status', 1)->count(),
            'partner_count'  => Db::name('partner')->where('status', 1)->count(),
        ];

        // 近30天趋势数据
        $days = 30;
        $dateLabels = [];
        $today = strtotime(date('Y-m-d'));
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateLabels[] = date('m-d', $today - $i * 86400);
        }

        $trend = $this->getTrendData($days);

        return View::fetch('admin/index/index', [
            'page_title' => '仪表盘',
            'stats'      => $stats,
            'dateLabels' => $dateLabels,
            'trend'      => $trend,
        ]);
    }

    /**
     * 获取趋势数据
     */
    private function getTrendData(int $days): array
    {
        $end = date('Y-m-d 23:59:59');
        $start = date('Y-m-d 00:00:00', strtotime("-" . ($days - 1) . " days"));

        // 使用 Db::query 避免 column() 对子查询别名的兼容问题
        $tables = [
            'repair'  => Db::name('repair_order')->getTable(),
            'message' => Db::name('message')->getTable(),
            'article' => Db::name('article')->getTable(),
        ];
        $sql = "SELECT DATE(create_time) AS date_key, COUNT(*) AS cnt FROM `%s` WHERE create_time BETWEEN ? AND ? GROUP BY date_key";

        $repairRows = Db::query(sprintf($sql, $tables['repair']), [$start, $end]);
        $messageRows = Db::query(sprintf($sql, $tables['message']), [$start, $end]);
        $articleRows = Db::query(sprintf($sql, $tables['article']), [$start, $end]);

        $repairMap = [];
        foreach ($repairRows as $row) {
            $repairMap[$row['date_key']] = (int)$row['cnt'];
        }
        $messageMap = [];
        foreach ($messageRows as $row) {
            $messageMap[$row['date_key']] = (int)$row['cnt'];
        }
        $articleMap = [];
        foreach ($articleRows as $row) {
            $articleMap[$row['date_key']] = (int)$row['cnt'];
        }

        $repair = [];
        $message = [];
        $article = [];
        $today = strtotime(date('Y-m-d'));
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', $today - $i * 86400);
            $repair[]  = $repairMap[$d] ?? 0;
            $message[] = $messageMap[$d] ?? 0;
            $article[] = $articleMap[$d] ?? 0;
        }

        return [
            'repair'  => $repair,
            'message' => $message,
            'article' => $article,
        ];
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        \think\facade\Cache::clear();
        $this->log(AdminLog::ACTION_CLEAR_CACHE, '清除系统缓存');
        return $this->success(null, '缓存已清除');
    }
}
