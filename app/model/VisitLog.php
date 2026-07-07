<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 访问统计模型
 */
class VisitLog extends Model
{
    protected $name = 'visit_log';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = false;
    
    protected $type = [
        'id' => 'integer',
    ];
    
    /**
     * 记录访问
     */
    public static function log(): void
    {
        try {
            self::create([
                'url'        => request()->url(),
                'ip'         => request()->ip(),
                'user_agent' => request()->header('user-agent', ''),
                'referer'    => request()->header('referer', ''),
            ]);
        } catch (\Exception $e) {
            // 记录失败不影响正常请求
        }
    }
    
    /**
     * 今日访问量
     */
    public static function todayCount(): int
    {
        return self::whereTime('create_time', 'today')->count();
    }
    
    /**
     * 近7天访问趋势
     */
    public static function weekTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = self::whereTime('create_time', 'between', [$date . ' 00:00:00', $date . ' 23:59:59'])->count();
            $data[] = [
                'date'  => $date,
                'count' => $count,
            ];
        }
        return $data;
    }
}
