<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
if (!canPostInCommunity()) die("权限不足");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$post = ['title' => '', 'subtitle' => '', 'tag' => '', 'cover' => '', 'category_id' => 0, 'content' => '', 'is_pinned' => 0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM community_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    if (!$post || (!isAdmin() && $post['user_id'] != currentUser()['id'])) die("权限不足");
}

$categories = $conn->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $content = $_POST['content'] ?? '';
    $is_pinned = isset($_POST['is_pinned']) && isAdmin() ? 1 : 0;

    $cover = $post['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    } elseif (!empty($_POST['cover_url'])) {
        $cover = $_POST['cover_url'];
    }

    if (empty($title) || trim(strip_tags($content)) === '') {
        $error = '标题和内容不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE community_posts SET title=?, subtitle=?, tag=?, cover=?, category_id=?, content=?, is_pinned=? WHERE id=?");
            // 修正：category_id 为整数，内容为字符串
            $stmt->bind_param("ssssisii", $title, $subtitle, $tag, $cover, $category_id, $content, $is_pinned, $id);
        } else {
            $user_id = currentUser()['id'];
            $stmt = $conn->prepare("INSERT INTO community_posts (title, subtitle, tag, cover, category_id, content, user_id, is_pinned) VALUES (?,?,?,?,?,?,?,?)");
            // 修正：参数顺序和类型对应
            $stmt->bind_param("ssssisii", $title, $subtitle, $tag, $cover, $category_id, $content, $user_id, $is_pinned);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/community/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑帖子' : '发布帖子';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑帖子' : '发布新帖子'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" id="postForm">
        <div style="margin-bottom:16px;"><label>标题 *</label><input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>副标题</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($post['subtitle'] ?? ''); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>标签</label><input type="text" name="tag" value="<?php echo htmlspecialchars($post['tag'] ?? ''); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>封面图片</label><input type="file" name="cover" accept="image/*"><input type="text" name="cover_url" value="<?php echo htmlspecialchars($post['cover'] ?? ''); ?>" placeholder="或输入URL" style="width:100%; padding:12px; margin-top:8px;"><?php if (!empty($post['cover'])): ?><img src="<?php echo $post['cover']; ?>" style="max-width:200px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>分类</label><select name="category_id" style="width:100%; padding:12px;"><option value="0">无分类</option><?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>" <?php echo ($post['category_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option><?php endforeach; ?></select></div>
        <div style="margin-bottom:16px;"><label>正文内容 *</label><textarea name="content" id="contentEditor" style="width:100%; height:400px; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea></div>
        <div style="margin-bottom:16px; display:flex; gap:8px; flex-wrap:wrap;">
            <button type="button" onclick="insertTag('b')" class="btn-auth" style="padding:6px 14px;"><b>B</b></button>
            <button type="button" onclick="insertTag('i')" class="btn-auth" style="padding:6px 14px;"><i>I</i></button>
            <button type="button" onclick="insertTag('u')" class="btn-auth" style="padding:6px 14px;"><u>U</u></button>
            <button type="button" onclick="insertCode()" class="btn-auth" style="padding:6px 14px;"><i class="fas fa-code"></i></button>
            <button type="button" onclick="insertLink()" class="btn-auth" style="padding:6px 14px;"><i class="fas fa-link"></i></button>
            <button type="button" id="uploadImgBtn" class="btn-auth" style="padding:6px 14px;"><i class="fas fa-image"></i></button>
            <input type="file" id="hiddenImageInput" accept="image/*" style="display:none;">
        </div>
        <?php if (isAdmin()): ?><div style="margin-bottom:16px;"><label><input type="checkbox" name="is_pinned" <?php echo ($post['is_pinned'] ?? 0) ? 'checked' : ''; ?>> 置顶</label></div><?php endif; ?>
        <button type="submit" class="btn-auth" style="width:200px;">保存帖子</button>
    </form>
</div>

<script>
function insertTag(tag) {
    var editor = document.getElementById('contentEditor');
    var start = editor.selectionStart, end = editor.selectionEnd;
    var text = editor.value.substring(start, end);
    var replacement = '<' + tag + '>' + text + '</' + tag + '>';
    editor.value = editor.value.substring(0, start) + replacement + editor.value.substring(end);
    editor.focus();
}
function insertCode() {
    var editor = document.getElementById('contentEditor');
    var start = editor.selectionStart, end = editor.selectionEnd;
    var text = editor.value.substring(start, end) || '// 代码';
    var replacement = '\n<pre><code>' + text + '</code></pre>\n';
    editor.value = editor.value.substring(0, start) + replacement + editor.value.substring(end);
    editor.focus();
}
function insertLink() {
    var editor = document.getElementById('contentEditor');
    var url = prompt('链接地址', 'https://');
    if (!url) return;
    var start = editor.selectionStart, end = editor.selectionEnd;
    var text = editor.value.substring(start, end) || url;
    var replacement = '<a href="' + url + '" target="_blank">' + text + '</a>';
    editor.value = editor.value.substring(0, start) + replacement + editor.value.substring(end);
    editor.focus();
}
document.getElementById('uploadImgBtn').addEventListener('click', function() {
    document.getElementById('hiddenImageInput').click();
});
document.getElementById('hiddenImageInput').addEventListener('change', async function() {
    var file = this.files[0];
    if (!file) return;
    var fd = new FormData();
    fd.append('image', file);
    try {
        var res = await fetch('<?php echo BASE_URL; ?>/upload.php', { method:'POST', body:fd });
        var data = await res.json();
        if (data.success && data.url) {
            var editor = document.getElementById('contentEditor');
            var imgTag = '<img src="' + data.url + '" alt="图片" style="max-width:100%;">';
            var start = editor.selectionStart, end = editor.selectionEnd;
            editor.value = editor.value.substring(0, start) + imgTag + editor.value.substring(end);
            editor.setSelectionRange(start + imgTag.length, start + imgTag.length);
            editor.focus();
        } else {
            alert('上传失败：' + (data.message || ''));
        }
    } catch(e) { alert('网络错误'); }
    this.value = '';
});
</script>
<?php require_once __DIR__ . '/../../footer.php'; ?>