<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\facade\Cache;
use think\facade\Db;

/**
 * 系统配置模型
 * 存储键值对配置，支持自动 JSON 序列化/反序列化
 */
class Config extends Model
{
    protected $name = 'config';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id'   => 'integer',
        'sort' => 'integer',
    ];

    /**
     * 获取配置值
     */
    public static function getConfigValue(string $key, $default = null)
    {
        $config = self::getAllConfig();
        return $config[$key] ?? $default;
    }

    /**
     * 获取所有配置（自动 JSON 反序列化）
     */
    public static function getAllConfig(): array
    {
        return Cache::remember('system_config', function () {
            $list = self::order('sort asc')->select()->toArray();
            $config = [];
            foreach ($list as $item) {
                $val = $item['value'];
                // 自动检测并反序列化 JSON 值
                if (is_string($val) && strlen($val) > 0) {
                    $decoded = json_decode($val, true);
                    if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                        $val = $decoded;
                    }
                }
                $config[$item['key']] = $val;
            }
            return $config;
        }, 3600);
    }

    /**
     * 批量更新配置（存在则更新，不存在则插入）
     */
    public static function batchUpdate(array $data): void
    {
        // 确保连接使用 utf8mb4，支持 emoji 等 4 字节字符
        Db::execute('SET NAMES utf8mb4');

        foreach ($data as $key => $value) {
            // 过滤无效键名
            if (empty($key) || is_numeric($key)) {
                continue;
            }

            // 如果值是数组，序列化为 JSON
            if (is_array($value)) {
                $dbValue = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $dbValue = (string) $value;
            }

            $exists = self::where('key', $key)->find();
            if ($exists) {
                self::where('key', $key)->update([
                    'value'       => $dbValue,
                    'update_time' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $group = 'base';
                if (strpos($key, 'contact_') === 0 || in_array($key, ['icp_number', 'wechat_qrcode', 'map_src'])) {
                    $group = 'contact';
                } elseif (strpos($key, 'theme_') === 0) {
                    $group = 'theme';
                } elseif (strpos($key, 'notif') === 0) {
                    $group = 'notify';
                } elseif (strpos($key, 'meta_') === 0 || strpos($key, 'keyword') !== false || strpos($key, '_desc') !== false) {
                    $group = 'seo';
                } elseif (strpos($key, 'nav_') === 0) {
                    $group = 'nav';
                } elseif (strpos($key, 'home_') === 0) {
                    $group = 'home';
                } elseif (strpos($key, 'service_') === 0) {
                    $group = 'service';
                } elseif (strpos($key, 'about_') === 0) {
                    $group = 'about';
                } elseif (strpos($key, 'footer_') === 0 || strpos($key, 'case_') === 0) {
                    $group = 'footer';
                }

                self::insert([
                    'key'         => $key,
                    'value'       => $dbValue,
                    'title'       => $key,
                    'group'       => $group,
                    'type'        => strlen($dbValue) > 500 ? 'textarea' : 'text',
                    'sort'        => 0,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        Cache::delete('system_config');
    }
}
