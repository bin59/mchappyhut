<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$id = intval($_GET['id'] ?? 0);
$form = $conn->query("SELECT title FROM forms WHERE id = $id")->fetch_assoc();
if (!$form) die('表单不存在');

$fields = $conn->query("SELECT label FROM form_fields WHERE form_id = $id ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
$answers = $conn->query("SELECT data, created_at, user_id FROM form_answers WHERE form_id = $id ORDER BY created_at DESC");

// 生成 CSV（Excel 可直接打开）
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . urlencode($form['title'] . '_结果.csv') . '"');
echo "\xEF\xBB\xBF"; // BOM for UTF-8

$fp = fopen('php://output', 'w');
$headers = array_column($fields, 'label');
$headers[] = '提交者';
$headers[] = '提交时间';
fputcsv($fp, $headers);

while ($row = $answers->fetch_assoc()) {
    $data = json_decode($row['data'], true) ?: [];
    $line = [];
    foreach ($fields as $f) {
        $line[] = $data[$f['label']] ?? '';
    }
    $username = '-';
    if ($row['user_id']) {
        $u = $conn->query("SELECT username FROM users WHERE id = " . intval($row['user_id']))->fetch_assoc();
        $username = $u['username'] ?? 'ID:'.$row['user_id'];
    }
    $line[] = $username;
    $line[] = $row['created_at'];
    fputcsv($fp, $line);
}
fclose($fp);
exit;