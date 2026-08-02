<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$event = ['title' => '', 'subtitle' => '', 'event_time' => date('Y-m-d H:i:s'), 'cover' => '', 'server_id' => '', 'content' => ''];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM timeline_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc() ?: $event;
}

$servers = $conn->query("SELECT id, name FROM servers ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'] ?? '';
    $event_time = $_POST['event_time'];
    $server_id = $_POST['server_id'] ? intval($_POST['server_id']) : NULL;
    $content = $_POST['content'];

    $cover = $event['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    }

    if (empty($title) || empty($event_time) || empty($content)) {
        $error = '标题、时间和内容不能为空';
    } else {
        $user_id = currentUser()['id'];
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE timeline_events SET title=?, subtitle=?, event_time=?, cover=?, server_id=?, content=? WHERE id=?");
            $stmt->bind_param("ssssisi", $title, $subtitle, $event_time, $cover, $server_id, $content, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO timeline_events (title, subtitle, event_time, cover, server_id, content, user_id) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssisi", $title, $subtitle, $event_time, $cover, $server_id, $content, $user_id);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/timeline/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑事件' : '添加事件';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑事件' : '添加新事件'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:16px;"><label>标题 *</label><input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>副标题</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($event['subtitle'] ?? ''); ?>" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>事件发生时间 *</label><input type="datetime-local" name="event_time" value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_time'])); ?>" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>封面图片</label><input type="file" name="cover" accept="image/*" style="width:100%;"><?php if ($event['cover']): ?><img src="<?php echo $event['cover']; ?>" style="max-width:200px; margin-top:8px; border-radius:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>关联服务器</label><select name="server_id" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><option value="">无</option><?php while ($srv = $servers->fetch_assoc()): ?><option value="<?php echo $srv['id']; ?>" <?php echo ($event['server_id'] == $srv['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($srv['name']); ?></option><?php endwhile; ?></select></div>
        <div style="margin-bottom:16px;"><label>事件详情 *</label><div id="editor" style="height:400px; border:1px solid var(--border); border-radius:8px; background:var(--bg);"></div><textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($event['content'] ?? ''); ?></textarea></div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center;">保存事件</button>
    </form>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    var quill = new Quill('#editor', { theme: 'snow', modules: { toolbar: [ [{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['blockquote', 'code-block'], ['link', 'image'], [{ 'align': [] }], ['clean'] ] }, placeholder: '输入事件详情...' });
    var contentTextarea = document.getElementById('content');
    quill.root.innerHTML = contentTextarea.value;
    document.querySelector('form').onsubmit = function() { contentTextarea.value = quill.root.innerHTML; };
    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input'); input.setAttribute('type', 'file'); input.setAttribute('accept', 'image/*'); input.click();
        input.onchange = function() {
            var file = input.files[0]; var formData = new FormData(); formData.append('image', file);
            fetch('<?php echo BASE_URL; ?>/upload.php', { method: 'POST', body: formData })
            .then(response => response.json()).then(result => { if (result.success) { var range = quill.getSelection(); quill.insertEmbed(range.index, 'image', result.url); } else alert('上传失败'); });
        };
    });
</script>

<?php require_once __DIR__ . '/../../footer.php'; ?>