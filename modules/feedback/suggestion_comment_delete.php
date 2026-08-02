<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$commentId = intval($_GET['id'] ?? 0);
$suggestionId = intval($_GET['suggestion_id'] ?? 0);
if ($commentId) {
    $c = $conn->query("SELECT user_id FROM suggestion_comments WHERE id = $commentId")->fetch_assoc();
    if ($c && (isAdmin() || $c['user_id'] == currentUser()['id'])) {
        $conn->query("DELETE FROM suggestion_comments WHERE id = $commentId");
    }
}
redirect(BASE_URL . "/modules/feedback/suggestion_detail.php?id=$suggestionId");