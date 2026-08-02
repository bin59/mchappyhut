<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$form = ['title' => '', 'description' => '', 'require_login' => 1, 'is_external' => 0, 'external_link' => ''];
$fields = [];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $form = $stmt->get_result()->fetch_assoc() ?: $form;

    $fieldStmt = $conn->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY sort_order ASC");
    $fieldStmt->bind_param("i", $id);
    $fieldStmt->execute();
    $fields = $fieldStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $require_login = isset($_POST['require_login']) ? 1 : 0;
    $is_external = isset($_POST['is_external']) ? 1 : 0;
    $external_link = $_POST['external_link'] ?? '';

    if (empty($title)) {
        $error = '标题不能为空';
    } else {
        // 保存表单基本信息
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE forms SET title=?, description=?, require_login=?, is_external=?, external_link=? WHERE id=?");
            $stmt->bind_param("ssiisi", $title, $description, $require_login, $is_external, $external_link, $id);
            $stmt->execute();
        } else {
            $user_id = currentUser()['id'];
            $stmt = $conn->prepare("INSERT INTO forms (title, description, require_login, is_external, external_link, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiisi", $title, $description, $require_login, $is_external, $external_link, $user_id);
            $stmt->execute();
            $id = $conn->insert_id;
            $editMode = true;
        }

        // 删除原有字段并重新添加
        $conn->query("DELETE FROM form_fields WHERE form_id = $id");

        if (!$is_external && isset($_POST['field_label'])) {
            $labels = $_POST['field_label'];
            $types = $_POST['field_type'];
            $options = $_POST['field_options'] ?? [];
            $requireds = $_POST['field_required'] ?? [];
            $sortOrder = 0;
            foreach ($labels as $index => $label) {
                if (empty($label)) continue;
                $type = $types[$index];
                $optionStr = in_array($type, ['select', 'radio', 'checkbox']) ? json_encode(array_filter(explode("\n", $options[$index] ?? ''))) : null;
                $required = in_array($index, array_keys($requireds)) ? 1 : 0;
                $stmt = $conn->prepare("INSERT INTO form_fields (form_id, label, type, options, required, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssii", $id, $label, $type, $optionStr, $required, $sortOrder);
                $stmt->execute();
                $sortOrder++;
            }
        }

        redirect(BASE_URL . '/modules/feedback/');
    }
}

$pageTitle = $editMode ? '编辑表单' : '创建表单';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑表单' : '创建新表单'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" id="formBuilder">
        <div style="margin-bottom:16px;"><label>表单标题 *</label><input type="text" name="title" value="<?php echo htmlspecialchars($form['title']); ?>" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>描述</label><textarea name="description" rows="2" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><?php echo htmlspecialchars($form['description'] ?? ''); ?></textarea></div>
        <div style="margin-bottom:16px; display:flex; gap:24px;">
            <label><input type="checkbox" name="require_login" <?php echo $form['require_login'] ? 'checked' : ''; ?>> 需要登录才能填写</label>
            <label><input type="checkbox" name="is_external" id="isExternal" <?php echo $form['is_external'] ? 'checked' : ''; ?>> 站外链接</label>
        </div>
        <div style="margin-bottom:16px;" id="externalLinkBox" <?php echo !$form['is_external'] ? 'style="display:none;"' : ''; ?>>
            <label>外部链接</label><input type="url" name="external_link" value="<?php echo htmlspecialchars($form['external_link'] ?? ''); ?>" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);" placeholder="https://...">
        </div>

        <div id="fieldsContainer" <?php echo $form['is_external'] ? 'style="display:none;"' : ''; ?>>
            <h3 style="margin-bottom:16px;">表单字段 <button type="button" id="addFieldBtn" class="btn-auth" style="font-size:0.8rem; padding:4px 12px;">+ 添加字段</button></h3>
            <div id="fieldsList">
                <?php if (!empty($fields)): ?>
                    <?php foreach ($fields as $field): ?>
                        <div class="field-item" style="background:var(--surface-alt); padding:12px; border-radius:8px; margin-bottom:8px;">
                            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                <input type="text" name="field_label[]" value="<?php echo htmlspecialchars($field['label']); ?>" placeholder="字段名" required style="flex:2; padding:8px; border:1px solid var(--border); border-radius:4px; background:var(--bg); color:var(--text);">
                                <select name="field_type[]" style="flex:1; padding:8px; border:1px solid var(--border); border-radius:4px; background:var(--bg); color:var(--text);">
                                    <option value="text" <?php echo $field['type'] === 'text' ? 'selected' : ''; ?>>文本</option>
                                    <option value="textarea" <?php echo $field['type'] === 'textarea' ? 'selected' : ''; ?>>多行文本</option>
                                    <option value="select" <?php echo $field['type'] === 'select' ? 'selected' : ''; ?>>下拉</option>
                                    <option value="radio" <?php echo $field['type'] === 'radio' ? 'selected' : ''; ?>>单选</option>
                                    <option value="checkbox" <?php echo $field['type'] === 'checkbox' ? 'selected' : ''; ?>>多选</option>
                                </select>
                                <label style="white-space:nowrap;"><input type="checkbox" name="field_required[<?php echo $field['sort_order']; ?>]" <?php echo $field['required'] ? 'checked' : ''; ?>> 必填</label>
                                <button type="button" class="remove-field btn-auth" style="background:#e74c3c; font-size:0.7rem; padding:6px 10px;">X</button>
                            </div>
                            <textarea name="field_options[]" placeholder="选项（每行一个，仅下拉/单选/多选需要）" style="width:100%; margin-top:8px; padding:8px; border:1px solid var(--border); border-radius:4px; background:var(--bg); color:var(--text);" rows="2"><?php
                                $opts = json_decode($field['options'], true);
                                echo $opts ? htmlspecialchars(implode("\n", $opts)) : '';
                            ?></textarea>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn-auth" style="width:200px; justify-content:center; margin-top:24px;">保存表单</button>
    </form>
</div>

<script>
document.getElementById('isExternal').addEventListener('change', function() {
    document.getElementById('externalLinkBox').style.display = this.checked ? 'block' : 'none';
    document.getElementById('fieldsContainer').style.display = this.checked ? 'none' : 'block';
});

document.getElementById('addFieldBtn').addEventListener('click', function() {
    const container = document.getElementById('fieldsList');
    const index = container.children.length;
    const html = `
        <div class="field-item" style="background:var(--surface-alt); padding:12px; border-radius:8px; margin-bottom:8px;">
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <input type="text" name="field_label[]" placeholder="字段名" required style="flex:2; padding:8px; border:1px solid var(--border); border-radius:4px; background:var(--bg); color:var(--text);">
                <select name="field_type[]" style="flex:1; padding:8px; border:1px solid var(--border); border-radius:4px; background:var(--bg); color:var(--text);">
                    <option value="text">文本</option>
                    <option value="textarea">多行文本</option>
                    <option value="select">下拉</option>
                    <option value="radio">单选</option>
                    <option value="checkbox">多选</option>
                </select>
                <label style="white-space:nowrap;"><input type="checkbox" name="field_required[${index}]"> 必填</label>
                <button type="button" class="remove-field btn-auth" style="background:#e74c3c; font-size:0.7rem; padding:6px 10px;">X</button>
            </div>
            <textarea name="field_options[]" placeholder="选项（每行一个，仅下拉/单选/多选需要）" style="width:100%; margin-top:8px; padding:8px; border:1px solid var(--border); border-radius:4px; background:var(--bg); color:var(--text);" rows="2"></textarea>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
});

document.getElementById('fieldsList').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-field')) {
        e.target.closest('.field-item').remove();
    }
});
</script>

<?php require_once __DIR__ . '/../../footer.php'; ?>