<?php
require_once __DIR__ . '/../../config.php';
requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) redirect(BASE_URL . '/modules/player_logs/');

// 检查权限：作者本人或管理员
$stmt = $conn->prepare("SELECT user_id FROM player_logs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) redirect(BASE_URL . '/modules/player_logs/');
if ($row['user_id'] != currentUser()['id'] && !isAdmin()) {
    redirect(BASE_URL . '/modules/player_logs/');
}

$stmt = $conn->prepare("DELETE FROM player_logs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

redirect(BASE_URL . '/modules/player_logs/');
