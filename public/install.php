<?php
/**
 * 六零同城企服 - 安装程序
 * 
 * 环境要求：PHP >= 8.1, MySQL >= 8.0
 */

// 检测是否已安装
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('LOCK_FILE', ROOT_PATH . 'install.lock');
define('ENV_FILE', ROOT_PATH . '.env');
define('SQL_FILE', ROOT_PATH . 'database' . DIRECTORY_SEPARATOR . 'install.sql');

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// 检查是否已安装
if (file_exists(LOCK_FILE) && $step < 3) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>系统已安装</title>';
    echo '<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Microsoft YaHei",sans-serif;background:#f0f2f5;display:flex;justify-content:center;align-items:center;min-height:100vh}.card{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.08);padding:48px;max-width:520px;width:90%;text-align:center}.icon{margin-bottom:24px}.icon svg{width:64px;height:64px}h1{font-size:20px;color:#1a1a2e;margin-bottom:12px}p{color:#666;font-size:14px;line-height:1.8;margin-bottom:8px}.btn{display:inline-block;margin-top:24px;padding:10px 32px;background:#1677ff;color:#fff;border-radius:6px;text-decoration:none;font-size:14px;transition:all .2s}.btn:hover{background:#4096ff}.danger{color:#ff4d4f;font-weight:500}</style></head><body>';
    echo '<div class="card"><div class="icon"><svg viewBox="0 0 1024 1024" fill="#1677ff"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm193.5 301.7l-210.6 292a31.8 31.8 0 01-51.7 0L318.5 484.9c-3.8-5.3 0-12.7 6.5-12.7h46.9c10.2 0 19.9 4.9 25.9 13.3l71.2 98.8 157.2-218c6-8.3 15.6-13.3 25.9-13.3H699c6.5 0 10.3 7.4 6.5 12.7z"/></svg></div><h1>系统已安装</h1><p>如需重新安装，请删除以下文件：</p><p class="danger">install.lock</p><p style="color:#999;font-size:12px">（位于项目根目录）然后重新访问本页面即可</p><a href="/" class="btn">返回首页</a></div></body></html>';
    exit;
}

// ======== 处理表单提交 ========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2 && isset($_POST['action']) && $_POST['action'] === 'check_env') {
        // 环境检测 AJAX
        header('Content-Type: application/json; charset=utf-8');
        $results = [];
        
        // PHP版本检测
        $results[] = [
            'name' => 'PHP版本 >= 8.1',
            'check' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'value' => PHP_VERSION,
            'required' => '8.1.0+'
        ];
        
        // 扩展检测
        $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'fileinfo', 'openssl', 'gd', 'json', 'xml', 'curl'];
        foreach ($extensions as $ext) {
            $results[] = [
                'name' => 'PHP扩展 - ' . $ext,
                'check' => extension_loaded($ext),
                'value' => extension_loaded($ext) ? '已安装' : '未安装',
                'required' => '必须'
            ];
        }
        
        // 目录权限检测
        $dirs = [
            ROOT_PATH . 'runtime',
            ROOT_PATH . 'public',
            ROOT_PATH . 'database',
        ];
        foreach ($dirs as $dir) {
            $writable = true;
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!is_writable($dir)) {
                $writable = false;
            }
            $results[] = [
                'name' => '目录可写 - ' . str_replace(ROOT_PATH, '', $dir),
                'check' => $writable,
                'value' => $writable ? '可写' : '不可写',
                'required' => '必须'
            ];
        }
        
        // .env 文件可写检测
        $envWritable = !file_exists(ENV_FILE) || is_writable(ENV_FILE);
        $results[] = [
            'name' => '配置文件 .env',
            'check' => $envWritable,
            'value' => $envWritable ? '可写' : '不可写',
            'required' => '必须'
        ];
        
        $allPass = true;
        foreach ($results as $r) {
            if (!$r['check']) $allPass = false;
        }
        
        echo json_encode(['pass' => $allPass, 'items' => $results], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($step === 3 && isset($_POST['action']) && $_POST['action'] === 'install') {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $dbHost = trim($_POST['db_host'] ?? '');
            $dbPort = trim($_POST['db_port'] ?? '3306');
            $dbName = trim($_POST['db_name'] ?? '');
            $dbUser = trim($_POST['db_user'] ?? '');
            $dbPass = trim($_POST['db_pass'] ?? '');
            $dbPrefix = trim($_POST['db_prefix'] ?? 'lylmew_');
            $adminPath = trim($_POST['admin_path'] ?? 'admin');
            $adminPath = $adminPath ?: 'admin';
            // 安全过滤：只允许字母数字下划线横线
            $adminPath = preg_replace('/[^a-zA-Z0-9_\-]/', '', $adminPath);
            if (empty($adminPath)) $adminPath = 'admin';
            // 禁止使用前台路由路径作为后台目录
            $reservedPaths = ['home', 'index', 'products', 'product', 'services', 'service', 'cases', 'case', 'news', 'article', 'about', 'contact', 'repair', 'search', 'captcha', 'sitemap.xml', 'robots.txt', 'login', 'logout'];
            if (in_array(strtolower($adminPath), $reservedPaths)) {
                echo json_encode(['success' => false, 'msg' => '"' . $adminPath . '" 已被注册，不能作为后台入口，请更换'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
                echo json_encode(['success' => false, 'msg' => '请填写完整的数据库信息'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 测试数据库连接
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            try {
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'msg' => '数据库连接失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 检查数据库是否存在，不存在则创建
            $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbName}'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            $pdo->exec("USE `{$dbName}`");
            
            // 导入SQL
            if (file_exists(SQL_FILE)) {
                $sql = file_get_contents(SQL_FILE);
                // 分割SQL语句
                $statements = parseSql($sql);
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (empty($statement)) continue;
                    // 替换表前缀
                    if ($dbPrefix !== 'lylmew_') {
                        $statement = str_replace('`lylmew_', '`' . $dbPrefix, $statement);
                    }
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // 忽略部分可忽略的错误
                        if (stripos($e->getMessage(), 'DROP') === false && stripos($e->getMessage(), 'duplicate') === false) {
                            throw $e;
                        }
                    }
                }
            } else {
                echo json_encode(['success' => false, 'msg' => 'SQL文件不存在：database/install.sql'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 写入 .env 配置
            $envContent = "APP_DEBUG = false\n\n";
            $envContent .= "[APP]\n";
            $envContent .= "DEFAULT_TIMEZONE = Asia/Shanghai\n";
            $envContent .= "ADMIN_PATH = {$adminPath}\n\n";
            $envContent .= "[DATABASE]\n";
            $envContent .= "TYPE = mysql\n";
            $envContent .= "HOSTNAME = {$dbHost}\n";
            $envContent .= "DATABASE = {$dbName}\n";
            $envContent .= "USERNAME = {$dbUser}\n";
            $envContent .= "PASSWORD = {$dbPass}\n";
            $envContent .= "HOSTPORT = {$dbPort}\n";
            $envContent .= "CHARSET = utf8mb4\n";
            $envContent .= "DEBUG = false\n";
            $envContent .= "PREFIX = {$dbPrefix}\n\n";
            $envContent .= "[LANG]\n";
            $envContent .= "default_lang = zh-cn\n\n";
            $envContent .= "[JWT]\n";
            $envContent .= "SECRET = " . generateRandomString(32) . "\n";
            $envContent .= "EXPIRE = 7200\n\n";
            $envContent .= "[UPLOAD]\n";
            $envContent .= "MAX_SIZE = 10485760\n";
            $envContent .= "SAVE_PATH = uploads\n";
            
            file_put_contents(ENV_FILE, $envContent);
            
            // 创建锁定文件
            file_put_contents(LOCK_FILE, date('Y-m-d H:i:s') . "\n" . $_SERVER['REMOTE_ADDR']);
            
            echo json_encode(['success' => true, 'msg' => '安装成功！', 'admin_path' => $adminPath], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'msg' => '安装失败：' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

/**
 * 解析SQL文件中的多条语句
 */
function parseSql(string $sql): array
{
    $sql = str_replace("\r\n", "\n", $sql);
    $sql = str_replace("\r", "\n", $sql);
    
    // 移除注释
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
    
    $statements = [];
    $statement = '';
    $delimiter = ';';
    
    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // 检测 DELIMITER 切换
        if (stripos($line, 'DELIMITER') === 0) {
            $parts = explode(' ', $line);
            if (count($parts) >= 2 && $parts[1] !== ';') {
                $delimiter = $parts[1];
            } else {
                $delimiter = ';';
            }
            continue;
        }
        
        $statement .= $line . "\n";
        
        if (substr(trim($line), -strlen($delimiter)) === $delimiter) {
            $statement = trim(substr(trim($statement), 0, -strlen($delimiter)));
            if (!empty($statement)) {
                $statements[] = $statement;
            }
            $statement = '';
            $delimiter = ';';
        }
    }
    
    if (!empty(trim($statement))) {
        $statements[] = trim($statement);
    }
    
    return $statements;
}

/**
 * 生成随机字符串
 */
function generateRandomString(int $length = 32): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $result;
}

// ======== 纯配置: 需要写入的 Admin 账户密码（不参与生成逻辑,仅复用） ========
// 本系统安装后默认使用 login.php 手工登入/RBAC初始化, 此处不做额外操作.
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>六零同城企服 - 安装向导</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Microsoft YaHei",sans-serif;
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px
        }
        .container{width:100%;max-width:720px}
        .card{
            background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);
            overflow:hidden
        }
        .header{
            background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);
            padding:32px 40px;text-align:center
        }
        .header h1{font-size:22px;color:#fff;font-weight:600;letter-spacing:1px}
        .header p{color:#a0aec0;font-size:13px;margin-top:6px}
        .steps{display:flex;padding:0;list-style:none;background:#f8f9fa;border-bottom:1px solid #eee}
        .steps li{
            flex:1;text-align:center;padding:16px 10px;font-size:13px;color:#999;
            position:relative;cursor:default;transition:all .3s
        }
        .steps li.active{color:#1677ff;font-weight:600}
        .steps li.done{color:#52c41a}
        .steps li::after{
            content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);
            width:0;height:3px;background:#1677ff;transition:all .3s;border-radius:3px 3px 0 0
        }
        .steps li.active::after,.steps li.done::after{width:40px}
        .steps li.done::after{background:#52c41a}
        .body{padding:32px 40px;min-height:300px}
        .footer{padding:20px 40px;background:#f8f9fa;text-align:right;border-top:1px solid #eee}
        .btn{
            display:inline-block;padding:10px 28px;border-radius:6px;font-size:14px;
            cursor:pointer;border:none;transition:all .2s;font-weight:500;text-decoration:none;outline:none
        }
        .btn-primary{background:#1677ff;color:#fff}
        .btn-primary:hover{background:#4096ff}
        .btn-primary:disabled{background:#b3d4ff;cursor:not-allowed}
        .btn-success{background:#52c41a;color:#fff}
        .btn-success:hover{background:#73d13d}
        .btn-outline{background:#fff;color:#666;border:1px solid #ddd;margin-right:10px}
        .btn-outline:hover{border-color:#1677ff;color:#1677ff}
        .env-list{list-style:none;padding:0}
        .env-list li{
            display:grid;grid-template-columns:28px 1fr 100px 60px;gap:12px;
            align-items:center;padding:10px 16px;margin-bottom:6px;
            background:#f8f9fa;border-radius:8px;font-size:13px;transition:all .2s
        }
        .env-list li:hover{background:#f0f5ff}
        .env-icon{display:flex;align-items:center;justify-content:center}
        .env-icon svg{width:20px;height:20px}
        .env-name{color:#333;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .env-value{color:#999;font-size:12px;text-align:right}
        .env-status{font-size:12px;font-weight:500;text-align:right}
        .env-pass{color:#52c41a}
        .env-fail{color:#ff4d4f}
        .env-wait{color:#faad14}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;font-size:13px;color:#555;margin-bottom:6px;font-weight:500}
        .form-group label .required{color:#ff4d4f;margin-left:2px}
        .form-control{
            width:100%;padding:10px 14px;border:1px solid #d9d9d9;border-radius:6px;
            font-size:14px;transition:all .2s;outline:none;font-family:inherit
        }
        .form-control:focus{border-color:#1677ff;box-shadow:0 0 0 2px rgba(22,119,255,.15)}
        .form-control:disabled{background:#f5f5f5;color:#999}
        .form-hint{font-size:12px;color:#999;margin-top:4px}
        .form-row{display:flex;gap:16px}
        .form-row .form-group{flex:1}
        .alert{padding:14px 18px;border-radius:8px;font-size:13px;margin-bottom:20px;line-height:1.8}
        .alert-success{background:#f6ffed;border:1px solid #b7eb8f;color:#389e0d}
        .alert-danger{background:#fff2f0;border:1px solid #ffccc7;color:#cf1322}
        .alert-info{background:#e6f7ff;border:1px solid #91d5ff;color:#096dd9}
        .progress-area{display:none;margin-top:20px}
        .progress-bar-bg{background:#eee;border-radius:10px;height:8px;overflow:hidden;margin-bottom:10px}
        .progress-bar-fill{height:100%;background:linear-gradient(90deg,#1677ff,#52c41a);border-radius:10px;transition:width .5s;width:0}
        .progress-text{font-size:12px;color:#666;text-align:center}
        .spinner{display:inline-block;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:8px}
        @keyframes spin{to{transform:rotate(360deg)}}
        .install-complete{text-align:center}
        .install-complete .check-icon{margin:20px auto 24px}
        .install-complete h2{font-size:22px;color:#1a1a2e;margin-bottom:12px}
        .install-complete .info-box{background:#f8f9fa;border-radius:8px;padding:20px;text-align:left;margin:24px 0;line-height:2;font-size:13px}
        .install-complete .info-box strong{color:#333}
        .install-complete .warning{color:#ff4d4f;font-weight:500}
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <h1>🔧 六零同城企服</h1>
            <p>安装向导</p>
        </div>
        <ul class="steps">
            <li class="<?php echo $step === 1 ? 'active' : ''; echo $step > 1 ? ' done' : ''; ?>">① 环境检测</li>
            <li class="<?php echo $step === 2 ? 'active' : ''; echo $step > 2 ? ' done' : ''; ?>">② 数据库配置</li>
            <li class="<?php echo $step === 3 ? 'active' : ''; echo $step > 3 ? ' done' : ''; ?>">③ 安装完成</li>
        </ul>

        <div class="body">
            <?php if ($step === 1): ?>
            <!-- ===== 步骤1: 环境检测 ===== -->
            <div id="step1">
                <div class="alert alert-info">
                    <strong>环境要求：</strong>PHP >= 8.1 | MySQL >= 8.0 | Nginx >= 1.18
                </div>
                <ul class="env-list" id="envList">
                    <li style="justify-content:center;padding:30px;color:#999;">正在检测环境...</li>
                </ul>
                <div id="envResult" style="text-align:center;margin-top:12px;"></div>
            </div>
            <?php elseif ($step === 2): ?>
            <!-- ===== 步骤2: 数据库配置 ===== -->
            <form id="dbForm">
                <div class="alert alert-info">
                    请填写以下数据库连接信息，确保数据库服务已启动。
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>数据库主机<span class="required">*</span></label>
                        <input type="text" name="db_host" class="form-control" value="127.0.0.1" required>
                    </div>
                    <div class="form-group">
                        <label>端口号<span class="required">*</span></label>
                        <input type="number" name="db_port" class="form-control" value="3306" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>数据库名<span class="required">*</span></label>
                    <input type="text" name="db_name" class="form-control" value="lylmew" placeholder="不存在将自动创建" required>
                    <p class="form-hint">如果数据库不存在，系统将自动创建</p>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>数据库用户名<span class="required">*</span></label>
                        <input type="text" name="db_user" class="form-control" value="root" required>
                    </div>
                    <div class="form-group">
                        <label>数据库密码</label>
                        <input type="password" name="db_pass" class="form-control" value="root">
                    </div>
                </div>
                <div class="form-group">
                    <label>数据表前缀</label>
                    <input type="text" name="db_prefix" class="form-control" value="lylmew_">
                </div>
                <div class="form-group">
                    <label>后台管理路径<span class="required">*</span></label>
                    <input type="text" name="admin_path" class="form-control" value="admin" required>
                    <p class="form-hint">自定义后台入口路径，仅允许字母数字下划线横线，不可使用前台路径（如 news/product/about 等）</p>
                </div>
                <div class="progress-area" id="installProgress">
                    <div class="progress-bar-bg"><div class="progress-bar-fill" id="progressBar"></div></div>
                    <div class="progress-text" id="progressText"></div>
                </div>
                <div id="installMsg"></div>
            </form>
            <?php else: ?>
            <!-- ===== 步骤3: 安装完成 ===== -->
            <?php
            // 读取后台路径（优先从 URL 参数，否则从 .env 文件读取）
            $installedAdminPath = isset($_GET['admin_path']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['admin_path']) : 'admin';
            if (file_exists(ENV_FILE)) {
                $envLines = file(ENV_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $inApp = false;
                foreach ($envLines as $line) {
                    $line = trim($line);
                    if ($line === '[APP]') { $inApp = true; continue; }
                    if ($inApp && strpos($line, '[') === 0) { $inApp = false; }
                    if ($inApp && strpos($line, 'ADMIN_PATH') === 0) {
                        $parts = explode('=', $line, 2);
                        $installedAdminPath = trim($parts[1] ?? 'admin');
                        break;
                    }
                }
            }
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;
            $frontUrl = $baseUrl . '/';
            $adminUrl = $baseUrl . '/' . $installedAdminPath . '/index';
            ?>
            <div class="install-complete" id="step3">
                <div class="check-icon">
                    <svg viewBox="0 0 80 80" width="80" height="80"><circle cx="40" cy="40" r="38" fill="#f6ffed" stroke="#52c41a" stroke-width="2"/><path d="M28 42l8 8 16-16" fill="none" stroke="#52c41a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h2>安装成功！</h2>
                <p style="color:#666;margin-top:8px;font-size:14px">系统已安装完成，以下是您的站点信息</p>
                
                <!-- 信息卡片 -->
                <div class="info-box" style="background:#f0f5ff;border:1px solid #adc6ff;margin:24px 0">
                    <table style="width:100%;border-collapse:collapse;font-size:13px">
                        <tr style="border-bottom:1px solid #e6f0ff">
                            <td style="padding:10px 14px;color:#555;width:90px"><strong>前台地址</strong></td>
                            <td style="padding:10px 14px"><a href="/" target="_blank" style="color:#1677ff"><?= $frontUrl ?></a></td>
                        </tr>
                        <tr style="border-bottom:1px solid #e6f0ff">
                            <td style="padding:10px 14px;color:#555"><strong>后台地址</strong></td>
                            <td style="padding:10px 14px"><a href="/<?= $installedAdminPath ?>/index" target="_blank" style="color:#1677ff"><?= $adminUrl ?></a></td>
                        </tr>
                        <tr style="border-bottom:1px solid #e6f0ff">
                            <td style="padding:10px 14px;color:#555"><strong>后台账号</strong></td>
                            <td style="padding:10px 14px;color:#1a1a2e;font-weight:600"><code style="background:#e8f0fe;padding:2px 8px;border-radius:4px">admin</code></td>
                        </tr>
                        <tr>
                            <td style="padding:10px 14px;color:#555"><strong>后台密码</strong></td>
                            <td style="padding:10px 14px;color:#1a1a2e;font-weight:600"><code style="background:#e8f0fe;padding:2px 8px;border-radius:4px">admin123</code></td>
                        </tr>
                    </table>
                </div>
                
                <p class="warning" style="color:#ff4d4f;font-size:13px;margin-bottom:24px">
                    请妥善保管以上信息，首次登录后建议立即修改密码！
                </p>
                
                <!-- 按钮组 -->
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                    <a href="/" class="btn btn-outline" style="padding:10px 28px;font-size:14px">返回首页</a>
                    <a href="/<?= $installedAdminPath ?>/index" class="btn btn-primary" style="padding:10px 28px;font-size:14px;background:#1677ff;color:#fff;border-radius:6px;text-decoration:none">登录后台</a>
                </div>
                
                <p style="color:#999;font-size:12px;margin-top:28px">
                    如需重新安装，请删除项目根目录下的 <code style="background:#f5f5f5;padding:1px 6px;border-radius:3px">install.lock</code> 文件后重新访问本页面
                </p>
            </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <?php if ($step === 1): ?>
            <button class="btn btn-primary" id="nextBtn" onclick="checkEnv()" disabled>重新检测</button>
            <?php elseif ($step === 2): ?>
            <a href="?step=1" class="btn btn-outline">上一步</a>
            <button class="btn btn-primary" id="installBtn" onclick="startInstall()">开始安装</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// ========== 步骤1: 环境检测 ==========
function checkEnv() {
    var btn = document.getElementById('nextBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>检测中...';
    
    fetch('?step=2', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=check_env'
    })
    .then(r => r.json())
    .then(data => {
        var list = document.getElementById('envList');
        list.innerHTML = '';
        
        data.items.forEach(function(item) {
            var statusClass = item.check ? 'env-pass' : 'env-fail';
            var icon = item.check
                ? '<svg viewBox="0 0 24 24" fill="#52c41a"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'
                : '<svg viewBox="0 0 24 24" fill="#ff4d4f"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';
            
            list.innerHTML += '<li>' +
                '<div class="env-icon">' + icon + '</div>' +
                '<div class="env-name">' + item.name + '</div>' +
                '<div class="env-value">' + item.value + '</div>' +
                '<div class="env-status ' + statusClass + '">' + (item.check ? '✔ 通过' : '✘ 失败') + '</div>' +
            '</li>';
        });
        
        if (data.pass) {
            document.getElementById('envResult').innerHTML = '<span style="color:#52c41a;font-weight:500">✅ 环境检测全部通过！</span>';
            btn.disabled = false;
            btn.textContent = '下一步';
            btn.onclick = function() { window.location.href = '?step=2'; };
        } else {
            document.getElementById('envResult').innerHTML = '<span style="color:#ff4d4f;font-weight:500">❌ 部分检测未通过，请修复后重新检测</span>';
            btn.disabled = false;
            btn.textContent = '重新检测';
        }
    })
    .catch(err => {
        document.getElementById('envList').innerHTML = '<li style="justify-content:center;padding:30px;color:#ff4d4f;">检测失败: ' + err.message + '</li>';
        btn.disabled = false;
        btn.textContent = '重新检测';
    });
}

// ========== 步骤2: 安装 ==========
function startInstall() {
    var form = document.getElementById('dbForm');
    var btn = document.getElementById('installBtn');
    var progress = document.getElementById('installProgress');
    var msg = document.getElementById('installMsg');
    
    // 简单验证
    var host = form.querySelector('[name="db_host"]').value.trim();
    var port = form.querySelector('[name="db_port"]').value.trim();
    var db = form.querySelector('[name="db_name"]').value.trim();
    var user = form.querySelector('[name="db_user"]').value.trim();
    
    if (!host || !db || !user) {
        msg.innerHTML = '<div class="alert alert-danger">请填写完整的数据库信息</div>';
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>安装中...';
    progress.style.display = 'block';
    msg.innerHTML = '';
    
    var progressBar = document.getElementById('progressBar');
    var progressText = document.getElementById('progressText');
    
    // 模拟进度
    var progressSteps = [
        {percent: 20, text: '正在连接数据库...'},
        {percent: 45, text: '正在创建数据表...'},
        {percent: 70, text: '正在写入配置文件...'},
        {percent: 90, text: '正在完成安装...'},
    ];
    var stepIdx = 0;
    
    var progressInterval = setInterval(function() {
        if (stepIdx < progressSteps.length) {
            var step = progressSteps[stepIdx];
            progressBar.style.width = step.percent + '%';
            progressText.textContent = step.text;
            stepIdx++;
        }
    }, 800);
    
    // 收集表单数据
    var formData = 'action=install';
    var inputs = form.querySelectorAll('input');
    inputs.forEach(function(input) {
        formData += '&' + encodeURIComponent(input.name) + '=' + encodeURIComponent(input.value);
    });
    
    fetch('?step=3', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        clearInterval(progressInterval);
        
        if (data.success) {
            progressBar.style.width = '100%';
            progressText.textContent = '安装完成！';
            msg.innerHTML = '<div class="alert alert-success">✅ ' + data.msg + '</div>';
            setTimeout(function() {
                window.location.href = '?step=3&admin_path=' + encodeURIComponent(data.admin_path || 'admin');
            }, 1500);
        } else {
            progressBar.style.width = '0';
            progressText.textContent = '';
            msg.innerHTML = '<div class="alert alert-danger">❌ ' + data.msg + '</div>';
            btn.disabled = false;
            btn.textContent = '重新安装';
        }
    })
    .catch(err => {
        clearInterval(progressInterval);
        progressBar.style.width = '0';
        progressText.textContent = '';
        msg.innerHTML = '<div class="alert alert-danger">请求失败: ' + err.message + '</div>';
        btn.disabled = false;
        btn.textContent = '重新安装';
    });
}

// 页面加载时自动执行环境检测
if (<?php echo $step; ?> === 1) {
    window.addEventListener('DOMContentLoaded', checkEnv);
}
</script>
</body>
</html>
