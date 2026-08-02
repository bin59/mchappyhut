<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$answerId = intval($_GET['id'] ?? 0);
$formId = intval($_GET['form_id'] ?? 0);
if ($answerId) {
    $conn->query("DELETE FROM form_answers WHERE id = $answerId AND form_id = $formId");
}
redirect(BASE_URL . "/modules/feedback/form_manage.php?id=$formId");