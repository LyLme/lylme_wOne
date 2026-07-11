<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;
use app\model\Config;

use util\SmtpMailer;

/**
 * 消息通知服务
 * 支持：钉钉Webhook、邮件、企业微信Webhook
 */
class Notification
{
    const CHANNEL_DINGTALK = 'dingtalk';
    const CHANNEL_EMAIL    = 'email';
    const CHANNEL_WECOM    = 'wecom';
    /**
     * 站点配置 - 所有配置项来源于数据库
     */
    private array $siteConfig;

    protected function __construct()
    {
       // 全部从数据库读取配置
        $this->siteConfig = Config::getAllConfig();
    }

    /**
     * 发送新服务工单通知
     */
    public static function sendRepair(array $data): void
    {
        $mdTemplate = self::getConfig('notification_template_repair');
        $mdText = self::renderTemplate($mdTemplate ?: self::defaultRepairMdTemplate(), $data);

        $emailTemplate = self::getConfig('notification_email_template_repair');
        $emailHtml = self::renderTemplate($emailTemplate ?: self::defaultRepairEmailTemplate(), $data);

        self::dispatch('[服务] 新服务工单', $mdText, $emailHtml);
    }

    /**
     * 发送新留言通知
     */
    public static function sendMessage(array $data): void
    {
        $mdTemplate = self::getConfig('notification_template_message');
        $mdText = self::renderTemplate($mdTemplate ?: self::defaultMessageMdTemplate(), $data);

        $emailTemplate = self::getConfig('notification_email_template_message');
        $emailHtml = self::renderTemplate($emailTemplate ?: self::defaultMessageEmailTemplate(), $data);

        self::dispatch('[留言] 新留言通知', $mdText, $emailHtml);
    }

    /**
     * 分发到各渠道
     */
    private static function dispatch(string $title, string $mdText, string $emailHtml): void
    {
        $enabled = self::getConfig('notification_enabled');
        if (empty($enabled)) {
            return;
        }

        $channels = self::getConfig('notification_channels');
        if (empty($channels)) {
            return;
        }

        if (!is_array($channels)) {
            $channels = json_decode($channels, true) ?: [];
        }

        foreach ($channels as $channel) {
            switch ($channel) {
                case self::CHANNEL_DINGTALK:
                    self::sendDingtalk($title, $mdText);
                    break;
                case self::CHANNEL_EMAIL:
                    self::sendEmail($title, $emailHtml);
                    break;
                case self::CHANNEL_WECOM:
                    self::sendWecom($mdText);
                    break;
            }
        }
    }

    /**
     * 钉钉机器人 Markdown 通知
     */
    private static function sendDingtalk(string $title, string $mdText): void
    {
        $webhook = self::getConfig('notification_dingtalk_webhook');
        $secret  = self::getConfig('notification_dingtalk_secret');

        if (empty($webhook)) return;

        $payload = [
            'msgtype'  => 'markdown',
            'markdown' => [
                'title' => $title,
                'text'  => $mdText,
            ],
        ];

        $url = $webhook;

        // 加签（如果配置了secret）
        if (!empty($secret)) {
            $timestamp = (string)(time() * 1000);
            $sign = self::dingtalkSign($timestamp, $secret);
            $url .= (strpos($url, '?') === false ? '?' : '&') . "timestamp={$timestamp}&sign=" . urlencode($sign);
        }

        self::httpPost($url, json_encode($payload, JSON_UNESCAPED_UNICODE), [
            'Content-Type: application/json',
        ]);
    }

    /**
     * 钉钉加签
     */
    private static function dingtalkSign(string $timestamp, string $secret): string
    {
        $stringToSign = $timestamp . "\n" . $secret;
        return base64_encode(hash_hmac('sha256', $stringToSign, $secret, true));
    }

    /**
     * 邮件通知（HTML 格式）
     */
    private static function sendEmail(string $title, string $emailHtml): void
    {
        $host       = self::getConfig('notification_email_host');
        $port       = self::getConfig('notification_email_port') ?: 25;
        $encryption = self::getConfig('notification_email_encryption') ?: '';
        $user       = self::getConfig('notification_email_user');
        $pass       = self::getConfig('notification_email_pass');
        $fromName   = self::getConfig('notification_email_from') ?: '';
        $to         = self::getConfig('notification_email_to');

        if (empty($host) || empty($user) || empty($pass) || empty($to)) return;

        // 发件地址用账号，fromName 仅作为显示名称
        $fromEmail   = $user;
        $fromDisplay = $fromName ?: '系统通知';

        // 对邮件模板中的用户输入进行 HTML 转义
        $emailHtml = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8');
        }, $emailHtml);

        $message = "<html><body style='font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;'>";
        $message .= "<div style='max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;'>";
        $message .= "<div style='background:#1A5FDC;color:#fff;padding:18px 24px;font-size:18px;font-weight:bold;'>" . htmlspecialchars($title) . "</div>";
        $message .= "<div style='padding:24px;'>" . $emailHtml . "</div>";
        $message .= "<div style='padding:0 24px 24px;'>";
        $message .= "<hr style='border:0;border-top:1px solid #eee;'>";
        $message .= "<p style='color:#999;font-size:12px;margin:0;'>此邮件由系统自动发送，请勿回复。</p>";
        $message .= "</div></div></body></html>";

        try {
            $mailer = new SmtpMailer($host, (int)$port, (string)$encryption);
            $mailer->setAuth($user, $pass);
            $mailer->send($to, $title, $message, $fromEmail, $fromDisplay);
        } catch (\Exception $e) {
            // 静默处理，不影响主流程
        }
    }

    /**
     * 企业微信机器人 Markdown 通知
     */
    private static function sendWecom(string $mdText): void
    {
        $webhook = self::getConfig('notification_wecom_webhook');
        if (empty($webhook)) return;

        $payload = [
            'msgtype'  => 'markdown',
            'markdown' => ['content' => $mdText],
        ];

        self::httpPost($webhook, json_encode($payload, JSON_UNESCAPED_UNICODE), [
            'Content-Type: application/json',
        ]);
    }

    /**
     * 渲染模板变量，并规范化换行为 \n（兼容钉钉/企业微信 Markdown）
     */
    private static function renderTemplate(string $template, array $data): string
    {
        $text = $template;
        foreach ($data as $key => $value) {
            // 处理 null 值
            $value = $value ?? '';
            // 变量值中的换行统一转为 \n，保留 Markdown 的段落/换行语义
            $val = (string) str_replace(["\r\n", "\r"], "\n", $value);
            // 将多个连续换行压缩为双换行（段落分隔），单换行不变
            $val = preg_replace("/\n{3,}/", "\n\n", $val);
            $text = str_replace('{' . $key . '}', $val, $text);
        }
        // 模板本身的换行统一为 \n，json_encode 时会自动转义为 JSON 换行符
        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    // ============================================================
    //  默认 Markdown 模板（钉钉 / 企业微信）
    // ============================================================

    private static function defaultRepairMdTemplate(): string
    {
        return "## [服务] 新服务工单\n\n"
            . "**工单号**：{order_no}\n\n"
            . "**联系人**：{client_name}\n\n"
            . "**电　话**：{phone}\n\n"
            . "**单　位**：{company}\n\n"
            . "**地　址**：{address}\n\n"
            . "**问题描述**：{description}\n\n"
            . "**提交时间**：{create_time}";
    }

    private static function defaultMessageMdTemplate(): string
    {
        return "## [留言] 新留言通知\n\n"
            . "**姓　名**：{name}\n\n"
            . "**电　话**：{phone}\n\n"
            . "**内　容**：{content}\n\n"
            . "**时　间**：{create_time}";
    }

    // ============================================================
    //  默认 HTML 邮件模板
    // ============================================================

    private static function defaultRepairEmailTemplate(): string
    {
        return "<table style='width:100%;border-collapse:collapse;font-size:14px;'>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;width:90px;color:#666;'>工单号</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'><strong>{order_no}</strong></td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;'>联系人</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{client_name}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;'>电话</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{phone}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;'>单位</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{company}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;'>地址</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{address}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;'>问题描述</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{description}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;color:#666;'>提交时间</td><td style='padding:8px 12px;'>{create_time}</td></tr>"
            . "</table>";
    }

    private static function defaultMessageEmailTemplate(): string
    {
        return "<table style='width:100%;border-collapse:collapse;font-size:14px;'>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;width:60px;color:#666;'>姓名</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{name}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;'>电话</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{phone}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;'>内容</td><td style='padding:8px 12px;border-bottom:1px solid #eee;'>{content}</td></tr>"
            . "<tr><td style='padding:8px 12px;background:#f9f9f9;color:#666;'>时间</td><td style='padding:8px 12px;'>{create_time}</td></tr>"
            . "</table>";
    }

    // ============================================================
    //  获取默认模板（供前端"重置模板"功能使用）
    // ============================================================

    /**
     * 获取所有默认通知模板
     * @return array  key 与前端 data-type 一一对应，便于前端重置
     */
    public static function getDefaultTemplates(): array
    {
        return [
            'md_repair'    => self::defaultRepairMdTemplate(),
            'md_message'   => self::defaultMessageMdTemplate(),
            'email_repair' => self::defaultRepairEmailTemplate(),
            'email_message'=> self::defaultMessageEmailTemplate(),
        ];
    }

    /**
     * 获取通知配置
     */
    private static function getConfig(string $key)
    {
        static $cache = [];
        if (!isset($cache[$key])) {
            $cache[$key] = Config::getConfigValue($key);
        }
        return $cache[$key];
    }

    /**
     * HTTP POST 请求
     */
    private static function httpPost(string $url, string $body, array $headers = []): void
    {
        try {
            $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            // 静默处理
        }
    }
}
