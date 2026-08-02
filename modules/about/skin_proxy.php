<?php
$url = $_GET['url'] ?? '';
if (filter_var($url, FILTER_VALIDATE_URL)) {
    $content = false;
    // 优先使用 CURL
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $content = curl_exec($ch);
        curl_close($ch);
    } elseif (ini_get('allow_url_fopen')) {
        $content = @file_get_contents($url);
    }

    if ($content !== false) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);
        if (strpos($mime, 'image/') === 0) {
            $base64 = base64_encode($content);
            echo 'data:' . $mime . ';base64,' . $base64;
            exit;
        }
    }
}
// 失败时返回透明像素
header('Content-Type: text/plain');
echo 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';