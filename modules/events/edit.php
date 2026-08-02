<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$event = ['title'=>'','subtitle'=>'','cover'=>'','organizer_name'=>'','organizer_avatar'=>'','start_time'=>'','end_time'=>'','content'=>'','is_pinned'=>0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc() ?: $event;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'] ?? '';
    $cover = $event['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    }
    $organizer_name = $_POST['organizer_name'] ?? '';
    $organizer_avatar = $event['organizer_avatar'] ?? '';
    if (isset($_FILES['organizer_avatar']) && $_FILES['organizer_avatar']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['organizer_avatar']);
        if ($upload['success']) $organizer_avatar = $upload['url'];
    } elseif (!empty($_POST['organizer_avatar_url'])) {
        $organizer_avatar = $_POST['organizer_avatar_url'];
    }
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $content = $_POST['content'];
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

    if (empty($title) || empty($start_time) || empty($end_time)) {
        $error = '请填写标题和时间';
    } else {
        $user_id = currentUser()['id'];
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE events SET title=?,subtitle=?,cover=?,organizer_name=?,organizer_avatar=?,start_time=?,end_time=?,content=?,is_pinned=? WHERE id=?");
            $stmt->bind_param("ssssssssii", $title, $subtitle, $cover, $organizer_name, $organizer_avatar, $start_time, $end_time, $content, $is_pinned, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO events (title,subtitle,cover,organizer_name,organizer_avatar,start_time,end_time,content,user_id,is_pinned) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssii", $title, $subtitle, $cover, $organizer_name, $organizer_avatar, $start_time, $end_time, $content, $user_id, $is_pinned);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/events/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑活动' : '发布活动';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 40px;">
    <h2><?php echo $editMode?'编辑活动':'发布新活动'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:16px;"><label>标题 *</label><input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required style="width:100%; padding:10px;"></div>
        <div style="margin-bottom:16px;"><label>副标题</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($event['subtitle']??''); ?>" style="width:100%; padding:10px;"></div>
        <div style="margin-bottom:16px;"><label>封面图片</label><input type="file" name="cover" accept="image/*"><?php if ($event['cover']): ?><img src="<?php echo $event['cover']; ?>" style="max-width:200px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>经办人名称</label><input type="text" name="organizer_name" value="<?php echo htmlspecialchars($event['organizer_name']??''); ?>" style="width:100%; padding:10px;"></div>
        <div style="margin-bottom:16px;"><label>经办人头像</label><input type="file" name="organizer_avatar" accept="image/*"> 或 URL <input type="text" name="organizer_avatar_url" value="<?php echo htmlspecialchars($event['organizer_avatar']??''); ?>" placeholder="https://..."><?php if ($event['organizer_avatar']): ?><img src="<?php echo $event['organizer_avatar']; ?>" style="width:60px; margin-top:8px;"><?php endif; ?></div>
        <div style="display:flex; gap:16px; margin-bottom:16px;">
            <div style="flex:1;"><label>开始时间 *</label><input type="datetime-local" name="start_time" value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_time'] ?: 'now')); ?>" required></div>
            <div style="flex:1;"><label>结束时间 *</label><input type="datetime-local" name="end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_time'] ?: 'now +1 hour')); ?>" required></div>
        </div>
        <div style="margin-bottom:16px;"><label>活动详情</label><div id="editor" style="height:400px;"></div><textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($event['content']??''); ?></textarea></div>
        <div style="margin-bottom:16px;"><label><input type="checkbox" name="is_pinned" <?php echo ($event['is_pinned']??0)?'checked':''; ?>> 置顶</label></div>
        <button type="submit" class="btn-auth">保存活动</button>
    </form>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    var quill = new Quill('#editor', { theme: 'snow', modules: { toolbar: [ [{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['blockquote', 'code-block'], ['link', 'image'], [{ 'align': [] }], ['clean'] ] }, placeholder: '输入活动详情...' });
    var contentTextarea = document.getElementById('content');
    quill.root.innerHTML = contentTextarea.value;
    document.querySelector('form').onsubmit = function() { contentTextarea.value = quill.root.innerHTML; };

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