<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$id = intval($_GET['id'] ?? 0);
if ($id) {
    // 权限：管理员或建议本人
    $s = $conn->query("SELECT user_id FROM suggestions WHERE id = $id")->fetch_assoc();
    if ($s && (isAdmin() || $s['user_id'] == currentUser()['id'])) {
        $conn->query("DELETE FROM suggestion_comments WHERE suggestion_id = $id");
        $conn->query("DELETE FROM suggestions WHERE id = $id");
    }
}
redirect(BASE_URL . '/modules/feedback/suggestions.php');