<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$answerId = intval($_GET['id'] ?? 0);
$formId = intval($_GET['form_id'] ?? 0);
$answer = $conn->query("SELECT * FROM form_answers WHERE id = $answerId AND form_id = $formId")->fetch_assoc();
if (!$answer) die('记录不存在');

$fields = $conn->query("SELECT * FROM form_fields WHERE form_id = $formId ORDER BY sort_order ASC");
$originalData = json_decode($answer['data'], true) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newData = [];
    while ($field = $fields->fetch_assoc()) {
        $key = 'field_' . $field['id'];
        $newData[$field['label']] = $_POST[$key] ?? '';
    }
    $json = json_encode($newData, JSON_UNESCAPED_UNICODE);
    $stmt = $conn->prepare("UPDATE form_answers SET data = ? WHERE id = ?");
    $stmt->bind_param("si", $json, $answerId);
    $stmt->execute();
    redirect(BASE_URL . "/modules/feedback/form_manage.php?id=$formId");
}

$fields->data_seek(0);
$pageTitle = '编辑提交内容';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 40px;">
    <h2>编辑提交内容</h2>
    <form method="POST">
        <?php while ($field = $fields->fetch_assoc()): ?>
            <div style="margin-bottom:16px;">
                <label><?php echo htmlspecialchars($field['label']); ?></label>
                <input type="text" name="field_<?php echo $field['id']; ?>" value="<?php echo htmlspecialchars($originalData[$field['label']] ?? ''); ?>" style="width:100%; padding:10px; border-radius:8px;">
            </div>
        <?php endwhile; ?>
        <button type="submit" class="btn-auth">保存修改</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>