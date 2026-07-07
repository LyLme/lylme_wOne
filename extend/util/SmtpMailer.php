<?php
declare(strict_types=1);

namespace util;

/**
 * SMTP 邮件发送器（基于 cURL，兼容 PHP-FPM 环境）
 * 支持 SSL、TLS、无加密，自动协商最佳连接方式
 */
class SmtpMailer
{
    private string $host;
    private int    $port;
    private string $user = '';
    private string $pass = '';
    private string $encryption; // '', 'ssl', 'tls'
    private int    $timeout = 15;

    public function __construct(string $host, int $port = 25, string $encryption = '')
    {
        $this->host       = $host;
        $this->port       = $port;
        $this->encryption = $encryption;
    }

    public function setAuth(string $user, string $pass): self
    {
        $this->user = $user;
        $this->pass = $pass;
        return $this;
    }

    public function setTimeout(int $sec): self
    {
        $this->timeout = $sec;
        return $this;
    }

    /**
     * 发送 HTML 邮件
     *
     * @param string|array $to       收件人
     * @param string       $subject  主题
     * @param string       $html     HTML 正文
     * @param string       $from     发件人邮箱
     * @param string       $fromName 发件人名称
     * @throws \RuntimeException
     */
    public function send($to, string $subject, string $html, string $from, string $fromName = ''): void
    {
        $toArr = is_array($to) ? $to : array_map('trim', explode(',', (string)$to));
        $toArr = array_filter($toArr, fn($v) => $v !== '');

        if (empty($toArr)) {
            throw new \RuntimeException('收件人不能为空');
        }
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("发件人邮箱格式无效: {$from}，请填写正确的邮箱地址");
        }

        $message = $this->buildMessage($from, $fromName, $toArr, $subject, $html);

        // 自动协商并发送
        $tried = [];
        $combinations = $this->getConnectionCombinations();

        foreach ($combinations as $i => $cfg) {
            try {
                $this->doSend($from, $toArr, $message, $cfg);
                return; // 成功
            } catch (\RuntimeException $e) {
                $tried[] = "{$cfg['label']}: " . $e->getMessage();
                // 如果是明确的认证失败（535），不再重试其他端口
                if (strpos($e->getMessage(), '535') !== false || strpos($e->getMessage(), '认证失败') !== false) {
                    throw $e;
                }
                // 最后一个也失败了，汇总抛出
                if ($i === count($combinations) - 1) {
                    throw new \RuntimeException(
                        "邮件发送失败，已尝试全部连接方式：\n\n" .
                        implode("\n\n", $tried) . "\n\n" .
                        "建议检查：\n" .
                        "• 邮箱账号和授权码是否正确\n" .
                        "• QQ邮箱需使用「授权码」而非登录密码\n" .
                        "• 服务器能否访问 {$this->host}"
                    );
                }
            }
        }
    }

    /**
     * 生成连接方案列表：优先按用户配置，然后回退
     */
    private function getConnectionCombinations(): array
    {
        $list = [];

        // 始终包含用户配置的方案
        $list[] = [
            'port'       => $this->port,
            'encryption' => $this->encryption,
            'label'      => "{$this->host}:{$this->port}(" . ($this->encryption ?: '无加密') . ")",
        ];

        // 补充其他方案作为回退（去重）
        $add = function (int $port, string $enc, string $label) use (&$list) {
            foreach ($list as $item) {
                if ($item['port'] === $port && $item['encryption'] === $enc) {
                    return; // 已存在
                }
            }
            $list[] = ['port' => $port, 'encryption' => $enc, 'label' => $label];
        };

        $add(465, 'ssl', "{$this->host}:465(SSL)");
        $add(587, 'tls', "{$this->host}:587(TLS)");
        $add(25,  '',    "{$this->host}:25(无加密)");

        return $list;
    }

    /**
     * 执行一次 cURL SMTP 发送
     */
    private function doSend(string $from, array $toArr, string $message, array $cfg): void
    {
        $ch = curl_init();

        // 构建 SMTP URL，ssl 加密时加 ssl:// 前缀通知 curl
        $proto = ($cfg['encryption'] === 'ssl') ? 'smtps' : 'smtp';
        curl_setopt($ch, CURLOPT_URL, "{$proto}://{$this->host}:{$cfg['port']}");

        curl_setopt($ch, CURLOPT_MAIL_FROM, "<{$from}>");

        $recipients = [];
        foreach ($toArr as $rcpt) {
            $recipients[] = "<{$rcpt}>";
        }
        curl_setopt($ch, CURLOPT_MAIL_RCPT, $recipients);

        curl_setopt($ch, CURLOPT_USERNAME, $this->user);
        curl_setopt($ch, CURLOPT_PASSWORD, $this->pass);

        // 将消息写入临时内存流
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $message);
        rewind($stream);
        curl_setopt($ch, CURLOPT_INFILE, $stream);
        curl_setopt($ch, CURLOPT_INFILESIZE, strlen($message));
        curl_setopt($ch, CURLOPT_UPLOAD, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // TLS 模式：curl 自动 STARTTLS（CURLUSESSL_TRY）
        if ($cfg['encryption'] === 'tls') {
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_TRY);
        }

        // 忽略 SSL 证书验证（兼容自签名/企业证书）
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 调试日志（需要时取消注释）
        // curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_exec($ch);

        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        fclose($stream);
        curl_close($ch);

        if ($errno !== 0) {
            $msg = match ($errno) {
                CURLE_OPERATION_TIMEOUTED => "连接超时（{$this->timeout}秒）",
                CURLE_COULDNT_RESOLVE_HOST => "无法解析域名 {$this->host}",
                CURLE_COULDNT_CONNECT => "无法连接到 {$this->host}:{$cfg['port']}",
                CURLE_LOGIN_DENIED => "SMTP 认证失败，请检查账号和授权码",
                CURLE_SSL_CONNECT_ERROR => "SSL/TLS 连接失败",
                default => "cURL 错误 [{$errno}]",
            };

            throw new \RuntimeException($msg . "\n" . ($error ? "详情: {$error}" : ""));
        }
    }

    // ================================================================
    //  邮件构建
    // ================================================================

    private function buildMessage(string $from, string $fromName, array $toArr, string $subject, string $html): string
    {
        $fromHeader = $fromName ? "=?UTF-8?B?" . base64_encode($fromName) . "?= <{$from}>" : $from;
        $toHeader   = implode(', ', $toArr);
        $boundary   = '----=_lylmew_' . md5(uniqid((string)mt_rand(), true));

        $msg  = "From: {$fromHeader}\r\n";
        $msg .= "To: {$toHeader}\r\n";
        $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $msg .= "Date: " . date('r') . "\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $msg .= "X-Mailer: lylmew-SmtpMailer\r\n";
        $msg .= "\r\n";

        // 纯文本版本
        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</h1>', '</h2>', '</h3>'], "\n", $html));
        $textBody = html_entity_decode($textBody, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "\r\n";
        $msg .= chunk_split(base64_encode($textBody));

        // HTML 版本
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "\r\n";
        $msg .= chunk_split(base64_encode($html));

        $msg .= "--{$boundary}--\r\n";

        return $msg;
    }
}
