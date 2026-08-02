<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '无效请求']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
    exit;
}

// 发送频率限制（60秒内只能发一次）
if (isset($_SESSION['last_mail_time']) && time() - $_SESSION['last_mail_time'] < 60) {
    echo json_encode(['success' => false, 'message' => '发送过于频繁，请60秒后再试']);
    exit;
}

// 生成6位验证码
$code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
$_SESSION['reg_code'] = $code;
$_SESSION['last_mail_time'] = time();

$subject = '【方块人快乐小窝】注册验证码';

// 构建精美 HTML 邮件内容
$baseUrl = BASE_URL;
$logoUrl = $baseUrl . '/logo.png'; // 如果有 logo.png 可以使用，否则下面用纯色块代替

$messageHtml = '
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', \'PingFang SC\', \'Microsoft YaHei\', sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.08);">
                    <!-- 头部 -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4F8A30, #6DB840); padding: 30px 30px 20px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:26px; font-weight:700; margin:0;">方块人快乐小窝</h1>
                            <p style="color:rgba(255,255,255,0.9); font-size:14px; margin-top:8px;">注册验证码</p>
                        </td>
                    </tr>
                    <!-- 内容 -->
                    <tr>
                        <td style="padding: 30px 30px 20px; text-align:center;">
                            <p style="font-size:16px; color:#333333; margin:0 0 10px;">你好！</p>
                            <p style="font-size:15px; color:#555555; line-height:1.6; margin:0 0 20px;">
                                您正在注册 <strong style="color:#4F8A30;">方块人快乐小窝</strong> 账号，<br>
                                请在注册页面输入以下验证码完成验证：
                            </p>
                            <!-- 验证码块 -->
                            <div style="display:inline-block; background-color:#f0f7eb; border:2px dashed #4F8A30; border-radius:12px; padding:16px 36px; margin-bottom:24px;">
                                <span style="font-size:32px; font-weight:800; letter-spacing:8px; color:#4F8A30;">' . $code . '</span>
                            </div>
                            <p style="font-size:13px; color:#999999; margin:0;">
                                验证码 <strong>10分钟内</strong> 有效，请勿泄露给他人。
                            </p>
                        </td>
                    </tr>
                    <!-- 分隔线 -->
                    <tr>
                        <td style="border-top:1px solid #eeeeee; padding:0 30px;"></td>
                    </tr>
                    <!-- 底部提示 -->
                    <tr>
                        <td style="padding: 20px 30px 30px; text-align:center; color:#aaaaaa; font-size:12px;">
                            <p style="margin:0;">如果不是您本人操作，请忽略此邮件。</p>
                            <p style="margin:8px 0 0;">方块人快乐小窝 · 官方服务器</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

// 使用 fsockopen 发送 HTML 邮件
function smtp_mail_html($to, $subject, $htmlBody) {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $user = SMTP_USER;
    $pass = SMTP_PASS;
    $from = MAIL_FROM;
    $fromName = MAIL_FROM_NAME;

    $socket = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) {
        return "连接 SMTP 失败: $errstr ($errno)";
    }

    fgets($socket, 512);
    fputs($socket, "HELO " . $_SERVER['HTTP_HOST'] . "\r\n");
    fgets($socket, 512);

    if ($user && $pass) {
        fputs($socket, "AUTH LOGIN\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode($user) . "\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode($pass) . "\r\n");
        $authResponse = fgets($socket, 512);
        if (substr($authResponse, 0, 3) != '235') {
            fclose($socket);
            return "认证失败: $authResponse";
        }
    }

    fputs($socket, "MAIL FROM:<$from>\r\n");
    fgets($socket, 512);
    fputs($socket, "RCPT TO:<$to>\r\n");
    fgets($socket, 512);
    fputs($socket, "DATA\r\n");
    fgets($socket, 512);

    // 构建邮件头（HTML）
    $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "\r\n";
    $headers .= $htmlBody;

    fputs($socket, $headers . "\r\n.\r\n");
    $response = fgets($socket, 512);
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return (substr($response, 0, 3) == '250') ? true : "发送失败: $response";
}

$sendResult = smtp_mail_html($email, $subject, $messageHtml);

if ($sendResult === true) {
    echo json_encode(['success' => true, 'message' => '验证码已发送，请查看邮箱（若未收到请检查垃圾箱）']);
} else {
    echo json_encode(['success' => false, 'message' => '邮件发送失败：' . $sendResult]);
}