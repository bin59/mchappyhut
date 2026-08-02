<?php
// 使用工单专用邮箱发送验证码，不与注册邮箱冲突
$ticketSmtp = [
    'host' => 'mail.ururc.org',
    'port' => 25,
    'user' => 'work@ururc.org',
    'pass' => '123456789Aa.',
    'secure' => '',
    'from' => 'work@ururc.org',
    'fromName' => '方块人工单系统'
];

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
    exit;
}

$code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
$_SESSION['ticket_email_code'] = $code;

$subject = '【方块人快乐小窝】工单邮箱验证';
$messageHtml = '<p>您的验证码是：<strong>' . $code . '</strong>，10分钟内有效。</p>';

function sendTicketVerifyMail($to, $subject, $html) {
    global $ticketSmtp;
    $socket = @fsockopen($ticketSmtp['host'], $ticketSmtp['port'], $errno, $errstr, 10);
    if (!$socket) return "连接失败: $errstr ($errno)";
    fgets($socket, 512);
    fputs($socket, "HELO " . $_SERVER['HTTP_HOST'] . "\r\n");
    fgets($socket, 512);
    if ($ticketSmtp['user'] && $ticketSmtp['pass']) {
        fputs($socket, "AUTH LOGIN\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode($ticketSmtp['user']) . "\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode($ticketSmtp['pass']) . "\r\n");
        $resp = fgets($socket, 512);
        if (substr($resp, 0, 3) != '235') { fclose($socket); return "认证失败"; }
    }
    fputs($socket, "MAIL FROM:<{$ticketSmtp['from']}>\r\n"); fgets($socket, 512);
    fputs($socket, "RCPT TO:<$to>\r\n"); fgets($socket, 512);
    fputs($socket, "DATA\r\n"); fgets($socket, 512);
    $headers = "From: =?UTF-8?B?" . base64_encode($ticketSmtp['fromName']) . "?= <{$ticketSmtp['from']}>\r\n";
    $headers .= "To: <$to>\r\nSubject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$html";
    fputs($socket, $headers . "\r\n.\r\n");
    $resp = fgets($socket, 512);
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    return (substr($resp, 0, 3) == '250') ? true : "发送失败: $resp";
}

$result = sendTicketVerifyMail($email, $subject, $messageHtml);
echo json_encode(['success' => $result === true, 'message' => $result === true ? '验证码已发送' : $result]);