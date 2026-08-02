<?php
require_once __DIR__ . '/../../config.php';
$id = intval($_GET['id'] ?? 0);
$form = $conn->query("SELECT * FROM forms WHERE id = $id")->fetch_assoc();
if (!$form) redirect(BASE_URL . '/modules/feedback/forms.php');

if ($form['is_external'] && $form['external_link']) redirect($form['external_link']);
if (($form['require_login'] || ($form['is_voting'] ?? 0)) && !isLoggedIn()) redirect(BASE_URL . '/modules/user/login.php');

$fields = $conn->query("SELECT * FROM form_fields WHERE form_id = $id ORDER BY sort_order ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = []; $errors = [];
    while ($field = $fields->fetch_assoc()) {
        $key = 'field_' . $field['id'];
        $value = $_POST[$key] ?? '';
        if (is_array($value)) $value = implode(', ', $value);
        if ($field['required'] && trim($value) === '') $errors[] = '请填写「'.$field['label'].'」';
        $data[$field['label']] = $value;
    }
    $fields->data_seek(0);
    if (empty($errors)) {
        $userId = isLoggedIn() ? currentUser()['id'] : null;
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO form_answers (form_id, user_id, data) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id, $userId, $jsonData);
        if ($stmt->execute()) $success = true;
        else $errors[] = '提交失败';
    }
}

$pageTitle = htmlspecialchars($form['title']) . ' - 表单';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 60px;">
    <!-- 表单头部（含封面） -->
    <div style="border-radius:24px; overflow:hidden; box-shadow:var(--shadow-lg); margin-bottom:30px; background:var(--surface-glass); backdrop-filter:blur(16px);">
        <?php if (!empty($form['cover'])): ?>
            <img src="<?php echo htmlspecialchars($form['cover']); ?>" style="width:100%; height:240px; object-fit:cover;">
        <?php endif; ?>
        <div style="padding:32px;">
            <h1 style="font-size:2.2rem; font-weight:800; margin-bottom:8px;"><?php echo htmlspecialchars($form['title']); ?></h1>
            <?php if ($form['description']): ?><p style="color:var(--text-secondary); font-size:1rem;"><?php echo htmlspecialchars($form['description']); ?></p><?php endif; ?>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div style="background:rgba(46,204,113,0.15); backdrop-filter:blur(8px); border-radius:20px; padding:50px; text-align:center; border:1px solid rgba(46,204,113,0.3);">
            <div style="font-size:3rem; margin-bottom:12px;">✅</div>
            <h2 style="font-weight:700;">提交成功</h2>
            <p style="color:var(--text-secondary);">感谢您的参与！</p>
            <a href="forms.php" style="margin-top:16px; display:inline-block; padding:12px 28px; background:var(--mc-green); color:#fff; border-radius:12px; text-decoration:none;">返回</a>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; border-radius:14px; padding:16px; margin-bottom:24px; color:#e74c3c;">
                <?php foreach ($errors as $e) echo '<div>• '.$e.'</div>'; ?>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <?php while ($field = $fields->fetch_assoc()): ?>
                <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid var(--border-light);">
                    <label style="font-weight:600; display:block; margin-bottom:12px;"><?php echo htmlspecialchars($field['label']); ?> <?php if ($field['required']): ?><span style="color:#e74c3c;">*</span><?php endif; ?></label>
                    <?php $name = 'field_'.$field['id'];
                    switch ($field['type']) {
                        case 'textarea': echo "<textarea name='$name' rows='3' required style='width:100%; padding:12px; border-radius:10px; background:var(--bg); color:var(--text); border:1px solid var(--border);'></textarea>"; break;
                        case 'select':
                            $opts = json_decode($field['options'], true) ?: [];
                            echo "<select name='$name' style='width:100%; padding:12px; border-radius:10px; background:var(--bg); color:var(--text); border:1px solid var(--border);'><option value=''>请选择</option>";
                            foreach ($opts as $o) echo "<option>".htmlspecialchars($o)."</option>";
                            echo "</select>"; break;
                        case 'radio':
                            $opts = json_decode($field['options'], true) ?: [];
                            foreach ($opts as $o) echo "<label style='display:flex; align-items:center; gap:8px; margin-bottom:8px;'><input type='radio' name='$name' value='".htmlspecialchars($o)."' style='accent-color:var(--mc-green);'> ".htmlspecialchars($o)."</label>"; break;
                        case 'checkbox':
                            $opts = json_decode($field['options'], true) ?: [];
                            foreach ($opts as $o) echo "<label style='display:flex; align-items:center; gap:8px; margin-bottom:8px;'><input type='checkbox' name='{$name}[]' value='".htmlspecialchars($o)."' style='accent-color:var(--mc-green);'> ".htmlspecialchars($o)."</label>"; break;
                        case 'image':
                            echo "<input type='file' name='$name' accept='image/*' style='width:100%;'>";
                            break;
                        default: echo "<input type='text' name='$name' placeholder='请输入' style='width:100%; padding:12px; border-radius:10px; background:var(--bg); color:var(--text); border:1px solid var(--border);'>";
                    } ?>
                </div>
            <?php endwhile; ?>
            <button type="submit" class="btn-auth" style="width:100%; padding:16px; background:var(--mc-green); color:#fff; border-radius:14px; font-size:1.1rem; font-weight:700;">提交</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>