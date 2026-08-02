<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$form = $stmt->get_result()->fetch_assoc();
if (!$form) redirect(BASE_URL . '/modules/feedback/');

// 检查外部链接
if ($form['is_external'] && $form['external_link']) {
    redirect($form['external_link']);
}

// 检查是否需要登录
if ($form['require_login'] && !isLoggedIn()) {
    redirect(BASE_URL . '/modules/user/login.php');
}

// 获取字段
$fieldsStmt = $conn->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY sort_order ASC");
$fieldsStmt->bind_param("i", $id);
$fieldsStmt->execute();
$fields = $fieldsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 处理提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    foreach ($fields as $field) {
        $key = 'field_' . $field['id'];
        $value = $_POST[$key] ?? '';
        if (is_array($value)) {
            $value = implode(', ', $value); // 多选checkbox
        }
        if ($field['required'] && empty($value)) {
            $error = '请填写所有必填项';
            break;
        }
        $data[$field['label']] = $value;
    }

    if (!isset($error)) {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $user_id = isLoggedIn() ? currentUser()['id'] : NULL;
        $stmt = $conn->prepare("INSERT INTO form_answers (form_id, user_id, data) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id, $user_id, $jsonData);
        if ($stmt->execute()) {
            $success = '提交成功！感谢你的反馈。';
        } else {
            $error = '提交失败，请重试';
        }
    }
}

$pageTitle = htmlspecialchars($form['title']) . ' - 反馈';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:700px; margin:0 auto; padding:100px 20px 40px;">
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:16px; padding:32px;">
        <h1 style="font-size:2rem; margin-bottom:8px;"><?php echo htmlspecialchars($form['title']); ?></h1>
        <?php if ($form['description']): ?>
            <p style="color:var(--text-secondary); margin-bottom:24px;"><?php echo htmlspecialchars($form['description']); ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div style="background:#4F8A30; color:#fff; padding:16px; border-radius:8px; margin-bottom:20px;"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
            <div style="background:#e74c3c; color:#fff; padding:16px; border-radius:8px; margin-bottom:20px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!isset($success)): ?>
            <form method="POST">
                <?php foreach ($fields as $field): ?>
                    <div style="margin-bottom:20px;">
                        <label style="font-weight:600; display:block; margin-bottom:6px;">
                            <?php echo htmlspecialchars($field['label']); ?>
                            <?php if ($field['required']): ?><span style="color:#e74c3c;">*</span><?php endif; ?>
                        </label>
                        <?php
                        $name = 'field_' . $field['id'];
                        switch ($field['type']):
                            case 'textarea': ?>
                                <textarea name="<?php echo $name; ?>" rows="4" <?php echo $field['required'] ? 'required' : ''; ?> style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></textarea>
                                <?php break;
                            case 'select':
                                $options = json_decode($field['options'], true) ?: [];
                                ?>
                                <select name="<?php echo $name; ?>" <?php echo $field['required'] ? 'required' : ''; ?> style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
                                    <option value="">请选择</option>
                                    <?php foreach ($options as $opt): ?>
                                        <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php break;
                            case 'radio':
                                $options = json_decode($field['options'], true) ?: [];
                                foreach ($options as $opt): ?>
                                    <label style="display:block; margin-bottom:4px;"><input type="radio" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($opt); ?>" <?php echo $field['required'] ? 'required' : ''; ?>> <?php echo htmlspecialchars($opt); ?></label>
                                <?php endforeach;
                                break;
                            case 'checkbox':
                                $options = json_decode($field['options'], true) ?: [];
                                foreach ($options as $opt): ?>
                                    <label style="display:block; margin-bottom:4px;"><input type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo htmlspecialchars($opt); ?>"> <?php echo htmlspecialchars($opt); ?></label>
                                <?php endforeach;
                                break;
                            default: // text ?>
                                <input type="text" name="<?php echo $name; ?>" <?php echo $field['required'] ? 'required' : ''; ?> style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
                        <?php endswitch; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn-auth" style="width:100%; justify-content:center; margin-top:16px;">提交反馈</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>