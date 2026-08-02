<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$rule = ['title' => '', 'subtitle' => '', 'tag' => '', 'cover' => '', 'content' => '', 'sort_order' => 0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM rules WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $rule = $stmt->get_result()->fetch_assoc() ?: $rule;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $cover = $rule['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    }
    $content = $_POST['content'];
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if (empty($title) || empty($content)) {
        $error = '标题和内容不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE rules SET title=?, subtitle=?, tag=?, cover=?, content=?, sort_order=? WHERE id=?");
            $stmt->bind_param("sssssii", $title, $subtitle, $tag, $cover, $content, $sort_order, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO rules (title, subtitle, tag, cover, content, sort_order) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("sssssi", $title, $subtitle, $tag, $cover, $content, $sort_order);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/rules/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑规则' : '添加规则';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑规则' : '添加新规则'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" id="ruleForm">
        <div style="margin-bottom:16px;"><label>标题 *</label><input type="text" name="title" value="<?php echo htmlspecialchars($rule['title']); ?>" required style="width:100%; padding:12px;"></div>
        <div style="margin-bottom:16px;"><label>副标题</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($rule['subtitle'] ?? ''); ?>" style="width:100%; padding:12px;"></div>
        <div style="margin-bottom:16px;"><label>标签</label><input type="text" name="tag" value="<?php echo htmlspecialchars($rule['tag'] ?? ''); ?>" style="width:100%; padding:12px;"></div>
        <div style="margin-bottom:16px;"><label>封面图片</label><input type="file" name="cover" accept="image/*"><?php if ($rule['cover']): ?><img src="<?php echo $rule['cover']; ?>" style="max-width:200px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>排序</label><input type="number" name="sort_order" value="<?php echo $rule['sort_order']; ?>"></div>
        <div style="margin-bottom:16px;"><label>正文内容</label><div id="editor" style="height:400px;"></div><textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($rule['content'] ?? ''); ?></textarea></div>
        <button type="submit" class="btn-auth">保存规则</button>
    </form>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    var quill = new Quill('#editor', { theme: 'snow', modules: { toolbar: [ [{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['blockquote', 'code-block'], ['link', 'image'], [{ 'align': [] }], ['clean'] ] }, placeholder: '输入规则内容...' });
    var contentTextarea = document.getElementById('content');
    quill.root.innerHTML = contentTextarea.value;
    document.getElementById('ruleForm').addEventListener('submit', function() { contentTextarea.value = quill.root.innerHTML; });

    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input'); input.setAttribute('type', 'file'); input.setAttribute('accept', 'image/*'); input.click();
        input.onchange = async function() {
            var file = input.files[0]; if (!file) return;
            var formData = new FormData(); formData.append('image', file);
            try {
                var response = await fetch('<?php echo BASE_URL; ?>/upload.php', { method: 'POST', body: formData });
                var result = await response.json();
                if (result.success && result.url) { var range = quill.getSelection(true); quill.insertEmbed(range.index, 'image', result.url); quill.setSelection(range.index + 1); }
                else { alert('上传失败：' + (result.message || '服务器错误')); }
            } catch (e) { alert('网络错误，请检查连接'); }
        };
    });
</script>
<?php require_once __DIR__ . '/../../footer.php'; ?>