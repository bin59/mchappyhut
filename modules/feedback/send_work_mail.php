<?php
// 工单系统专用邮件发送函数（使用 work@ururc.org）
function sendWorkMail($to, $subject, $htmlBody) {
    $host = 'mail.ururc.org';
    $port = 25;
    $user = 'work@ururc.org';
    $pass = '123456789Aa.';
    $from = 'work@ururc.org';
    $fromName = '方块人工单系统';

    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) return false;

    fgets($socket, 512);
    fputs($socket, "HELO " . $_SERVER['HTTP_HOST'] . "\r\n");
    fgets($socket, 512);
    if ($user && $pass) {
        fputs($socket, "AUTH LOGIN\r\n"); fgets($socket, 512);
        fputs($socket, base64_encode($user) . "\r\n"); fgets($socket, 512);
        fputs($socket, base64_encode($pass) . "\r\n");
        if (substr(fgets($socket, 512), 0, 3) != '235') { fclose($socket); return false; }
    }
    fputs($socket, "MAIL FROM:<$from>\r\n"); fgets($socket, 512);
    fputs($socket, "RCPT TO:<$to>\r\n"); fgets($socket, 512);
    fputs($socket, "DATA\r\n"); fgets($socket, 512);

    $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
    $headers .= "To: <$to>\r\nSubject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$htmlBody";
    fputs($socket, $headers . "\r\n.\r\n");
    fgets($socket, 512);
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    return true;
}