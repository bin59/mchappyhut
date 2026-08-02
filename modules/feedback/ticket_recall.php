<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT user_id, status FROM tickets WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
if ($ticket && $ticket['user_id'] == currentUser()['id'] && $ticket['status'] === 'sent') {
    $conn->query("UPDATE tickets SET status = 'draft' WHERE id = $id");
}
redirect(BASE_URL . '/modules/feedback/tickets.php');