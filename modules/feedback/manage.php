<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$form = $stmt->get_result()->fetch_assoc();
if (!$form) redirect(BASE_URL . '/modules/feedback/');

$answersStmt = $conn->prepare("SELECT * FROM form_answers WHERE form_id = ? ORDER BY created_at DESC");
$answersStmt->bind_param("i", $id);
$answersStmt->execute();
$answers = $answersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 获取字段名以便显示
$fieldsStmt = $conn->prepare("SELECT label FROM form_fields WHERE form_id = ? ORDER BY sort_order ASC");
$fieldsStmt->bind_param("i", $id);
$fieldsStmt->execute();
$fieldLabels = $fieldsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = htmlspecialchars($form['title']) . ' - 反馈结果';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:1100px; margin:0 auto; padding:100px 20px 40px;">
    <h1 style="font-size:2.2rem; margin-bottom:8px;"><?php echo htmlspecialchars($form['title']); ?> - 反馈结果</h1>
    <p style="color:var(--text-secondary); margin-bottom:32px;">共 <?php echo count($answers); ?> 条回复</p>

    <?php if (empty($answers)): ?>
        <p style="color:var(--text-secondary); text-align:center;">暂无反馈数据。</p>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:12px;">
                <thead>
                    <tr style="background:var(--surface-alt);">
                        <th style="padding:12px; border:1px solid var(--border-light);">提交时间</th>
                        <?php foreach ($fieldLabels as $field): ?>
                            <th style="padding:12px; border:1px solid var(--border-light);"><?php echo htmlspecialchars($field['label']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($answers as $answer): 
                        $data = json_decode($answer['data'], true); ?>
                        <tr>
                            <td style="padding:12px; border:1px solid var(--border-light);"><?php echo date('Y-m-d H:i', strtotime($answer['created_at'])); ?></td>
                            <?php foreach ($fieldLabels as $field): ?>
                                <td style="padding:12px; border:1px solid var(--border-light);"><?php echo htmlspecialchars($data[$field['label']] ?? ''); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top:32px;">
        <a href="index.php" class="btn-auth" style="text-decoration:none;">返回表单列表</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>