<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

// 仅登录用户可上传
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => '无效请求']);
    exit;
}

$file = $_FILES['image'];
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => '文件上传出错']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => '文件超过5MB']);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt)) {
    echo json_encode(['success' => false, 'message' => '不支持的文件类型']);
    exit;
}

$filename = uniqid() . '.' . $ext;
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$dest = $uploadDir . '/' . $filename;
if (move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => true, 'url' => BASE_URL . '/uploads/' . $filename]);
} else {
    echo json_encode(['success' => false, 'message' => '文件保存失败']);
}