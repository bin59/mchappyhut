<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
if ($id) {
    $stmt = $conn->prepare("SELECT user_id FROM community_comments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $comment = $stmt->get_result()->fetch_assoc();
    if ($comment && (isAdmin() || $comment['user_id'] == currentUser()['id'])) {
        $stmt = $conn->prepare("DELETE FROM community_comments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
redirect(BASE_URL . "/modules/community/detail.php?id=$post_id");