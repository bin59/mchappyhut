<?php
// 单独封装邮件发送，供工单系统使用
function sendTicketMail($to, $subject, $htmlBody) {
    $host = SMTP_HOST_TICKET;
    $port = SMTP_PORT_TICKET;
    $user = SMTP_USER_TICKET;
    $pass = SMTP_PASS_TICKET;
    $from = MAIL_FROM_TICKET;
    $fromName = MAIL_FROM_NAME_TICKET;

    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) return false;

    fgets($socket, 512);
    fputs($socket, "HELO " . $_SERVER['HTTP_HOST'] . "\r\n");
    fgets($socket, 512);

    if ($user && $pass) {
        fputs($socket, "AUTH LOGIN\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode($user) . "\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode($pass) . "\r\n");
        fgets($socket, 512);
    }

    fputs($socket, "MAIL FROM:<$from>\r\n");
    fgets($socket, 512);
    fputs($socket, "RCPT TO:<$to>\r\n");
    fgets($socket, 512);
    fputs($socket, "DATA\r\n");
    fgets($socket, 512);

    $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "\r\n";
    $headers .= $htmlBody;

    fputs($socket, $headers . "\r\n.\r\n");
    fgets($socket, 512);
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    return true;
}