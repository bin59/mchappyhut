<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'host21k5c4');
define('DB_PASS', 'pId5XpS5CcBi');
define('DB_NAME', 'host21k5c4');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("数据库连接失败：" . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

define('BASE_URL', 'https://mchappyhut.club');

define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_GROUP_LEADER', 'group_leader');
define('ROLE_SENIOR_ADVENTURER', 'senior_adventurer');
define('ROLE_ADVENTURER', 'adventurer');
define('ROLE_RESTRICTED', 'restricted');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

function hasRole($role) {
    $user = currentUser();
    return $user && $user['role'] === $role;
}

function isAdmin() {
    return hasRole('super_admin') || hasRole('admin');
}

function canPostInCommunity() {
    $user = currentUser();
    if (!$user) return false;
    return in_array($user['role'], ['senior_adventurer', 'group_leader', 'admin', 'super_admin']);
}

function roleLabel($role) {
    switch ($role) {
        case 'super_admin': return '超级管理员';
        case 'admin': return '管理员';
        case 'group_leader': return '团体负责人';
        case 'senior_adventurer': return '高级冒险家';
        case 'adventurer': return '冒险家';
        case 'restricted': return '受限用户';
        default: return $role;
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/modules/user/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        die("权限不足");
    }
}

function uploadFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $maxSize = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '文件上传失败'];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => '文件大小超过5MB限制'];
    }

    $mime = '';
    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    } else {
        $extMap = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = $extMap[$ext] ?? '';
    }

    if (!in_array($mime, $allowedTypes)) {
        return ['success' => false, 'message' => '不支持的文件类型'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'message' => '无法创建上传目录'];
        }
    }

    $destination = $uploadDir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'url' => BASE_URL . '/uploads/' . $filename];
    }
    return ['success' => false, 'message' => '文件保存失败'];
}

define('SMTP_HOST', 'mail.ururc.org');
define('SMTP_PORT', 25);                 // 验证码邮件: 25 明文 / 587 STARTTLS
define('SMTP_USER', 'workbench@ururc.org');
define('SMTP_PASS', 'NCXQs8X9QAPn00zs');
define('SMTP_SECURE', '');               // 无加密
define('MAIL_FROM', 'workbench@ururc.org');
define('MAIL_FROM_NAME', '方块人快乐小窝');

// 工单邮件专用 (pumpkin)
define('SMTP_HOST_TICKET', 'mail.ururc.org');
define('SMTP_PORT_TICKET', 25);
define('SMTP_USER_TICKET', 'pumpkin@ururc.org');
define('SMTP_PASS_TICKET', 'H7N7By2skN8FxX74');
define('MAIL_FROM_TICKET', 'pumpkin@ururc.org');
define('MAIL_FROM_NAME_TICKET', '方块人快乐小窝');