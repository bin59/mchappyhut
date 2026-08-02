<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM contributors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
redirect(BASE_URL . '/modules/about/');