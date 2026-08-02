<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $stmt = $conn->prepare("SELECT user_id FROM community_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    if ($post && (isAdmin() || $post['user_id'] == currentUser()['id'])) {
        $stmt = $conn->prepare("DELETE FROM community_comments WHERE post_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
redirect(BASE_URL . '/modules/community/');