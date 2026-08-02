<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$form = ['title'=>'','description'=>'','require_login'=>1,'is_external'=>0,'external_link'=>'','is_voting'=>0,'cover'=>'','user_id'=>0];
$fields = [];

if ($editMode) {
    $form = $conn->query("SELECT * FROM forms WHERE id = $id")->fetch_assoc() ?: $form;
    $fields = $conn->query("SELECT * FROM form_fields WHERE form_id = $id ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $require_login = isset($_POST['require_login']) ? 1 : 0;
    $is_external = isset($_POST['is_external']) ? 1 : 0;
    $external_link = $_POST['external_link'] ?? '';
    $is_voting = isset($_POST['is_voting']) ? 1 : 0;

    // 封面处理
    $cover = $form['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    } elseif (!empty($_POST['cover_url'])) {
        $cover = $_POST['cover_url'];
    }

    if ($editMode) {
        $stmt = $conn->prepare("UPDATE forms SET title=?, description=?, require_login=?, is_external=?, external_link=?, cover=?, is_voting=? WHERE id=?");
        $stmt->bind_param("ssiissii", $title, $description, $require_login, $is_external, $external_link, $cover, $is_voting, $id);
        $stmt->execute();
    } else {
        $userId = currentUser()['id'];
        $stmt = $conn->prepare("INSERT INTO forms (title, description, require_login, is_external, external_link, cover, is_voting, user_id, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssiissiii", $title, $description, $require_login, $is_external, $external_link, $cover, $is_voting, $userId, $userId);
        $stmt->execute();
        $id = $conn->insert_id;
    }

    // 重建字段
    $conn->query("DELETE FROM form_fields WHERE form_id = $id");
    if (!$is_external && isset($_POST['field_label'])) {
        foreach ($_POST['field_label'] as $i => $label) {
            if (empty(trim($label))) continue;
            $type = $_POST['field_type'][$i];
            $options = in_array($type, ['select','radio','checkbox']) ? json_encode(array_filter(explode("\n", trim($_POST['field_options'][$i] ?? '')))) : null;
            $required = isset($_POST['field_required']) && in_array($i, array_keys($_POST['field_required'] ?? [])) ? 1 : 0;
            $stmt = $conn->prepare("INSERT INTO form_fields (form_id, label, type, options, required, sort_order) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("isssii", $id, $label, $type, $options, $required, $i);
            $stmt->execute();
        }
    }
    redirect(BASE_URL . '/modules/feedback/forms.php');
}

$pageTitle = $editMode ? '编辑表单' : '创建表单';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 60px;">
    <h1 style="font-size:2.4rem; font-weight:800; margin-bottom:8px;"><?php echo $editMode?'编辑表单':'创建新表单'; ?></h1>
    <p style="color:var(--text-secondary); margin-bottom:30px;">仿金山表单设计，支持投票、封面和站外链接</p>

    <form method="POST" enctype="multipart/form-data" id="formBuilder">
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:28px; margin-bottom:20px; border:1px solid var(--border-light);">
            <h3 style="margin-bottom:20px;">基本信息</h3>
            <div style="margin-bottom:16px;"><label style="font-weight:600;">标题 *</label><input name="title" value="<?php echo htmlspecialchars($form['title']); ?>" required style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);"></div>
            <div style="margin-bottom:16px;"><label style="font-weight:600;">描述</label><textarea name="description" rows="2" style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);"><?php echo htmlspecialchars($form['description']); ?></textarea></div>
            <div style="margin-bottom:16px;">
                <label style="font-weight:600;">封面图片</label>
                <input type="file" name="cover" accept="image/*" style="width:100%; margin-bottom:8px;">
                <input type="text" name="cover_url" placeholder="或输入URL" value="<?php echo htmlspecialchars($form['cover']??''); ?>" style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);">
                <?php if(!empty($form['cover'])): ?><img src="<?php echo $form['cover']; ?>" style="max-width:200px; margin-top:8px; border-radius:8px;"><?php endif; ?>
            </div>
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="require_login" <?php echo $form['require_login']?'checked':''; ?>> 需登录填写</label>
                <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="is_voting" <?php echo ($form['is_voting']??0)?'checked':''; ?>> 投票模式（强制登录）</label>
                <label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" name="is_external" id="isExternal" <?php echo $form['is_external']?'checked':''; ?>> 站外链接</label>
            </div>
            <div id="externalLinkBox" style="margin-top:16px; <?php echo $form['is_external']?'':'display:none;'; ?>">
                <input name="external_link" value="<?php echo htmlspecialchars($form['external_link']); ?>" placeholder="https://..." style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);">
            </div>
        </div>

        <div id="fieldsContainer" <?php echo $form['is_external']?'style="display:none;"':''; ?>>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3>题目列表</h3>
                <button type="button" id="addFieldBtn" style="padding:8px 20px; background:var(--mc-green); color:#fff; border:none; border-radius:10px; cursor:pointer;">+ 添加题目</button>
            </div>
            <div id="fieldsList" style="display:flex; flex-direction:column; gap:16px;">
                <?php foreach ($fields as $f): ?>
                <div class="field-item" style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:20px; border:1px solid var(--border-light);">
                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                        <input name="field_label[]" value="<?php echo htmlspecialchars($f['label']); ?>" placeholder="题目" style="flex:2; padding:10px; border-radius:8px;">
                        <select name="field_type[]" style="padding:10px; border-radius:8px;">
                            <option value="text" <?php echo $f['type']=='text'?'selected':''; ?>>文本</option>
                            <option value="textarea" <?php echo $f['type']=='textarea'?'selected':''; ?>>多行</option>
                            <option value="select" <?php echo $f['type']=='select'?'selected':''; ?>>下拉</option>
                            <option value="radio" <?php echo $f['type']=='radio'?'selected':''; ?>>单选</option>
                            <option value="checkbox" <?php echo $f['type']=='checkbox'?'selected':''; ?>>多选</option>
                        </select>
                        <label style="display:flex; align-items:center; gap:4px; white-space:nowrap;"><input type="checkbox" name="field_required[<?php echo $f['sort_order']; ?>]" <?php echo $f['required']?'checked':''; ?>> 必填</label>
                        <button type="button" class="remove-field" style="background:#e74c3c; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer;">删除</button>
                    </div>
                    <textarea name="field_options[]" placeholder="选项（每行一个）" rows="3" style="width:100%; margin-top:12px; padding:10px; border-radius:8px;"><?php echo implode("\n", json_decode($f['options'],true) ?: []); ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" style="margin-top:24px; width:100%; padding:16px; background:var(--mc-green); color:#fff; border:none; border-radius:14px; font-size:1.1rem; font-weight:700; cursor:pointer;">保存表单</button>
    </form>
</div>

<script>
document.getElementById('isExternal').addEventListener('change', function(){
    document.getElementById('externalLinkBox').style.display = this.checked ? 'block' : 'none';
    document.getElementById('fieldsContainer').style.display = this.checked ? 'none' : 'block';
});
document.getElementById('addFieldBtn').addEventListener('click', ()=>{
    const div = document.createElement('div');
    div.className = 'field-item';
    div.innerHTML = `
        <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:20px; border:1px solid var(--border-light); margin-bottom:16px;">
            <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <input name="field_label[]" placeholder="题目" style="flex:2; padding:10px; border-radius:8px;">
                <select name="field_type[]" style="padding:10px; border-radius:8px;">
                    <option value="text">文本</option><option value="textarea">多行</option><option value="select">下拉</option><option value="radio">单选</option><option value="checkbox">多选</option>
                </select>
                <label style="display:flex; align-items:center; gap:4px;"><input type="checkbox" name="field_required[]"> 必填</label>
                <button type="button" class="remove-field" style="background:#e74c3c; color:#fff; border:none; padding:6px 12px; border-radius:6px;">删除</button>
            </div>
            <textarea name="field_options[]" placeholder="选项（每行一个）" rows="2" style="width:100%; margin-top:12px; padding:10px; border-radius:8px;"></textarea>
        </div>`;
    document.getElementById('fieldsList').appendChild(div);
});
document.getElementById('fieldsList').addEventListener('click', (e)=>{
    if(e.target.classList.contains('remove-field')) e.target.closest('.field-item').remove();
});
</script>
<?php require_once __DIR__ . '/../../footer.php'; ?>