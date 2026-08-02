<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$currentUser = currentUser();
$currentUserId = $currentUser['id'];
$currentUserRole = $currentUser['role'];
$isAdmin = ($currentUserRole === 'super_admin' || $currentUserRole === 'admin');

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT user_id, status FROM tickets WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
if ($ticket) {
    if ($isAdmin || ($ticket['user_id'] == $currentUserId && $ticket['status'] === 'draft')) {
        $conn->query("DELETE FROM ticket_attachments WHERE ticket_id = $id");
        $conn->query("DELETE FROM ticket_replies WHERE ticket_id = $id");
        $conn->query("DELETE FROM tickets WHERE id = $id");
    }
}
redirect(BASE_URL . '/modules/feedback/tickets.php');