<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$id = intval($_GET['id'] ?? 0);
$form = $conn->query("SELECT * FROM forms WHERE id = $id")->fetch_assoc();
if (!$form) die('表单不存在');

$fields = $conn->query("SELECT id, label FROM form_fields WHERE form_id = $id ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
$answers = $conn->query("SELECT id, data, created_at, user_id FROM form_answers WHERE form_id = $id ORDER BY created_at DESC");

$pageTitle = htmlspecialchars($form['title']) . ' - 结果';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:1800px; margin:0 auto; padding:100px 20px 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:30px;">
        <h1 style="font-size:2.2rem; font-weight:800;"><?php echo htmlspecialchars($form['title']); ?> · 结果</h1>
        <a href="form_export.php?id=<?php echo $id; ?>" class="btn-auth" style="background:#3498db; color:#fff;">导出 Excel</a>
    </div>

    <?php if ($answers->num_rows === 0): ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:80px; text-align:center;">暂无填写记录</div>
    <?php else: ?>
        <div style="overflow-x:auto; background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; border:1px solid var(--border-light);">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--surface-alt);">
                        <?php foreach ($fields as $f): ?>
                            <th style="padding:16px; text-align:left;"><?php echo htmlspecialchars($f['label']); ?></th>
                        <?php endforeach; ?>
                        <th style="padding:16px;">提交者</th>
                        <th style="padding:16px;">时间</th>
                        <th style="padding:16px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($a = $answers->fetch_assoc()):
                        $d = json_decode($a['data'], true) ?: [];
                        $username = '-';
                        if ($a['user_id']) {
                            $u = $conn->query("SELECT username FROM users WHERE id = " . intval($a['user_id']))->fetch_assoc();
                            $username = $u['username'] ?? 'ID:'.$a['user_id'];
                        }
                    ?>
                        <tr style="border-bottom:1px solid var(--border-light);">
                            <?php foreach ($fields as $f): ?>
                                <td style="padding:16px;"><?php echo htmlspecialchars($d[$f['label']] ?? ''); ?></td>
                            <?php endforeach; ?>
                            <td style="padding:16px;"><?php echo htmlspecialchars($username); ?></td>
                            <td style="padding:16px; white-space:nowrap;"><?php echo $a['created_at']; ?></td>
                            <td style="padding:16px;">
                                <a href="answer_edit.php?id=<?php echo $a['id']; ?>&form_id=<?php echo $id; ?>" style="color:var(--mc-green);">编辑</a>
                                <a href="answer_delete.php?id=<?php echo $a['id']; ?>&form_id=<?php echo $id; ?>" style="color:#e74c3c; margin-left:10px;" onclick="return confirm('确定删除？')">删除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>