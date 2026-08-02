<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$announcement = ['title' => '', 'subtitle' => '', 'tag' => '', 'cover' => '', 'server_id' => '', 'content' => '', 'is_pinned' => 0, 'is_featured' => 0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $announcement = $stmt->get_result()->fetch_assoc();
    if (!$announcement) redirect(BASE_URL . '/modules/announcements/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $cover = $announcement['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    }
    $server_id = $_POST['server_id'] ? intval($_POST['server_id']) : NULL;
    $content = $_POST['content'];
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($title) || empty($content)) {
        $error = '标题和内容不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE announcements SET title=?, subtitle=?, tag=?, cover=?, server_id=?, content=?, is_pinned=?, is_featured=? WHERE id=?");
            $stmt->bind_param("ssssisiii", $title, $subtitle, $tag, $cover, $server_id, $content, $is_pinned, $is_featured, $id);
        } else {
            $user_id = currentUser()['id'];
            $stmt = $conn->prepare("INSERT INTO announcements (title, subtitle, tag, cover, server_id, content, user_id, is_pinned, is_featured) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssisiii", $title, $subtitle, $tag, $cover, $server_id, $content, $user_id, $is_pinned, $is_featured);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/announcements/');
        } else {
            $error = '保存失败';
        }
    }
}

$servers = $conn->query("SELECT id, name FROM servers ORDER BY name");
$pageTitle = $editMode ? '编辑公告' : '发布公告';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px; animation: fadeIn 0.5s ease;">
    <h2 style="margin-bottom:24px; font-weight:700;"><?php echo $editMode ? '编辑公告' : '发布新公告'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" id="announceForm">
        <div style="margin-bottom:16px;"><label>标题 *</label><input type="text" name="title" value="<?php echo htmlspecialchars($announcement['title']); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>副标题</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($announcement['subtitle'] ?? ''); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>标签</label><input type="text" name="tag" value="<?php echo htmlspecialchars($announcement['tag'] ?? ''); ?>" placeholder="如：更新、活动" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>封面图片</label><input type="file" name="cover" accept="image/*" style="width:100%;"><?php if ($announcement['cover']): ?><img src="<?php echo $announcement['cover']; ?>" style="max-width:200px; margin-top:8px; border-radius:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>关联服务器</label><select name="server_id" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><option value="">全部服务器</option><?php while ($srv = $servers->fetch_assoc()): ?><option value="<?php echo $srv['id']; ?>" <?php echo ($announcement['server_id'] == $srv['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($srv['name']); ?></option><?php endwhile; ?></select></div>
        <div style="margin-bottom:16px;"><label>正文内容</label><div id="editor" style="height:400px; border:1px solid var(--border); border-radius:8px; background:var(--bg);"></div><textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($announcement['content'] ?? ''); ?></textarea></div>
        <div style="margin-bottom:16px; display:flex; gap:24px;">
            <label><input type="checkbox" name="is_pinned" <?php echo $announcement['is_pinned'] ? 'checked' : ''; ?>> 置顶公告</label>
            <label><input type="checkbox" name="is_featured" <?php echo $announcement['is_featured'] ? 'checked' : ''; ?>> 特别公告</label>
        </div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center; padding:12px;">保存公告</button>
    </form>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                [{ 'align': [] }],
                ['clean']
            ]
        },
        placeholder: '输入公告内容...'
    });
    var contentTextarea = document.getElementById('content');
    quill.root.innerHTML = contentTextarea.value;
    document.getElementById('announceForm').addEventListener('submit', function() {
        contentTextarea.value = quill.root.innerHTML;
    });

    // 图片上传修复
    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        input.onchange = async function() {
            var file = input.files[0];
            if (!file) return;
            var formData = new FormData();
            formData.append('image', file);
            try {
                var response = await fetch('<?php echo BASE_URL; ?>/upload.php', {
                    method: 'POST',
                    body: formData
                });
                var result = await response.json();
                if (result.success && result.url) {
                    var range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1);
                } else {
                    alert('上传失败：' + (result.message || '服务器错误'));
                }
            } catch (e) {
                alert('网络错误，请检查连接');
            }
        };
    });
</script>
<style>
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>