<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\AdminLog;
use think\facade\View;

/**
 * 后台站点配置控制器
 * 管理数据库中所有配置项
 */
class Config extends Base
{
    /**
     * 站点配置 - 所有配置项来源于数据库
     */
    private array $siteConfig;

    protected function initialize(): void
    {
        parent::initialize();
        // 全部从数据库读取配置
        $this->siteConfig = \app\model\Config::getAllConfig();
    }

    /**
     * 配置管理主页（Tab 分组展示）
     */
    public function index()
    {
        return View::fetch('admin/config/index', [
            'page_title' => '站点配置',
            'config'     => $this->siteConfig,
            'siteConfig' => $this->siteConfig,
        ]);
    }

    /**
     * 保存全部配置（AJAX）
     */
    public function save()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $data = $this->request->post();

        // 过滤掉空值和系统字段
        unset($data['_token'], $data['__token__'], $data['file']);

        if (empty($data)) {
            return $this->error('没有需要保存的数据');
        }

        try {
            \app\model\Config::batchUpdate($data);
            $this->log(AdminLog::ACTION_CONFIG, '保存站点配置');
            return $this->success(null, '配置保存成功');
        } catch (\Exception $e) {
            return $this->error('保存失败：' . $e->getMessage());
        }
    }

    /**
     * 保存单个分组配置
     */
    public function saveGroup()
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $data = $this->request->post();
        unset($data['_token'], $data['__token__'], $data['file']);

        if (empty($data)) {
            return $this->error('参数错误');
        }

        try {
            \app\model\Config::batchUpdate($data);
            $this->log(AdminLog::ACTION_CONFIG, '保存分组配置');
            return $this->success(null, '保存成功');
        } catch (\Exception $e) {
            return $this->error('保存失败：' . $e->getMessage());
        }
    }

    /**
     * AJAX 上传图片
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->error('请选择文件');
        }

        try {
            // 验证文件类型和大小
            $ext = strtolower($file->getOriginalExtension());
            $allowExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'];
            if (!in_array($ext, $allowExt)) {
                return $this->error('不支持的文件类型，仅允许：' . implode(',', $allowExt));
            }

            // 按日期分目录
            $subPath = date('Ymd');
            $fileName = $this->generateFileName($file);

            // 使用 public 磁盘保存
            \think\facade\Filesystem::disk('public')->putFileAs($subPath, $file, $fileName);

            // 返回可访问URL
            $url = '/static/uploads/' . $subPath . '/' . $fileName;

            return $this->success(['url' => $url, 'name' => $fileName], '上传成功');
        } catch (\Exception $e) {
            return $this->error('上传失败：' . $e->getMessage());
        }
    }

    /**
     * 生成唯一文件名
     */
    private function generateFileName($file): string
    {
        $ext = strtolower($file->getOriginalExtension());
        return date('His') . '_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8) . '.' . $ext;
    }

    /**
     * 重置配置项为默认值
     */
    public function reset()
    {
        $key = $this->request->post('key', '');
        if (empty($key)) {
            return $this->error('请指定要重置的配置项');
        }

        try {
            \think\facade\Db::name('config')->where('key', $key)->delete();
            \think\facade\Cache::delete('system_config');
            return $this->success(null, '已恢复默认值');
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取默认通知模板（供前端"重置模板"使用）
     */
    public function getDefaultTemplates()
    {
        try {
            $templates = \app\service\Notification::getDefaultTemplates();
            return $this->success($templates);
        } catch (\Exception $e) {
            return $this->error('获取默认模板失败：' . $e->getMessage());
        }
    }

    /**
     * 测试消息通知渠道
     */
    public function testNotify()
    {
        $channel = $this->request->post('channel', '');
        if (empty($channel)) {
            return $this->error('请选择测试渠道');
        }

        try {
            switch ($channel) {
                case 'dingtalk':
                    $webhook = \app\model\Config::getConfigValue('notification_dingtalk_webhook');
                    $secret  = \app\model\Config::getConfigValue('notification_dingtalk_secret');
                    if (empty($webhook)) return $this->error('钉钉Webhook地址未配置');

                    $payload = [
                        'msgtype'  => 'markdown',
                        'markdown' => [
                            'title' => '测试消息',
                            'text'  => "## [OK] 测试消息\n\n消息通知配置成功！\n\n> 来自：" . $this->siteConfig['company_name_short'] . "后台",
                        ],
                    ];
                    $url = $webhook;
                    if (!empty($secret)) {
                        $timestamp = (string)(time() * 1000);
                        $sign = base64_encode(hash_hmac('sha256', $timestamp . "\n" . $secret, $secret, true));
                        $url .= (strpos($url, '?') === false ? '?' : '&') . "timestamp={$timestamp}&sign=" . urlencode($sign);
                    }
                    $this->curlPost($url, json_encode($payload, JSON_UNESCAPED_UNICODE));
                    break;

                case 'email':
                    $host       = \app\model\Config::getConfigValue('notification_email_host');
                    $port       = \app\model\Config::getConfigValue('notification_email_port') ?: 25;
                    $encryption = \app\model\Config::getConfigValue('notification_email_encryption') ?: '';
                    $user       = \app\model\Config::getConfigValue('notification_email_user');
                    $pass       = \app\model\Config::getConfigValue('notification_email_pass');
                    $fromName   = \app\model\Config::getConfigValue('notification_email_from') ?: '';
                    $to         = \app\model\Config::getConfigValue('notification_email_to');

                    if (empty($host))  return $this->error('SMTP服务器地址未配置');
                    if (empty($user))  return $this->error('发件邮箱账号未配置');
                    if (empty($pass))  return $this->error('邮箱授权码未配置');
                    if (empty($to))    return $this->error('收件邮箱未配置');

                    // 发件地址：用账号作为发件人邮箱，fromName 仅作为显示名称
                    $fromEmail = $user;
                    $fromDisplay = $fromName ?: $this->siteConfig['company_name_short'];

                    $mailer = new \util\SmtpMailer($host, (int)$port, (string)$encryption);
                    $mailer->setAuth($user, $pass);
                    $mailer->send($to,
                        "[OK] 测试消息 - " . $this->siteConfig['company_name_short'] . " 通知配置成功",
                        "<html><body style='font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;'><div style='max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;'><div style='background:#1A5FDC;color:#fff;padding:18px 24px;font-size:18px;font-weight:bold;'>[OK] 测试消息</div><div style='padding:24px;'><p style='font-size:16px;color:#333;'>消息通知配置成功！</p><p style='color:#666;'>此邮件来自" . $this->siteConfig['company_name_short'] . "后台。</p></div><div style='padding:0 24px 24px;'><hr style='border:0;border-top:1px solid #eee;'><p style='color:#999;font-size:12px;'>此邮件由系统自动发送，请勿回复。</p></div></div></body></html>",
                        $fromEmail,
                        $fromDisplay
                    );
                    break;

                case 'wecom':
                    $webhook = \app\model\Config::getConfigValue('notification_wecom_webhook');
                    if (empty($webhook)) return $this->error('企业微信Webhook地址未配置');

                    $payload = [
                        'msgtype'  => 'markdown',
                        'markdown' => ['content' => "# [OK] 测试消息\n消息通知配置成功！\n\n> 来自：" . $this->siteConfig['company_name_short'] . "后台"],
                    ];
                    $this->curlPost($webhook, json_encode($payload, JSON_UNESCAPED_UNICODE));
                    break;

                default:
                    return $this->error('未知渠道');
            }

            return $this->success(null, '测试消息发送成功，请检查 ' . $channel . ' 是否收到消息');
        } catch (\Exception $e) {
            return $this->error('发送失败：' . $e->getMessage());
        }
    }

    private function curlPost(string $url, string $body): void
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new \Exception($err);
        }

        $result = json_decode($res, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($result['errcode']) && $result['errcode'] !== 0) {
            throw new \Exception($result['errmsg'] ?? '发送失败(errcode=' . $result['errcode'] . ')');
        }
    }
}
