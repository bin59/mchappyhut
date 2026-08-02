<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $conn->query("DELETE FROM form_fields WHERE form_id = $id");
    $conn->query("DELETE FROM form_answers WHERE form_id = $id");
    $conn->query("DELETE FROM forms WHERE id = $id");
}
redirect(BASE_URL . '/modules/feedback/');