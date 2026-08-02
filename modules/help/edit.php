<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$article = ['title' => '', 'content' => '', 'sort_order' => 0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM help_articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc() ?: $article;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if (empty($title)) {
        $error = '标题不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE help_articles SET title=?, content=?, sort_order=? WHERE id=?");
            $stmt->bind_param("ssii", $title, $content, $sort_order, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO help_articles (title, content, sort_order) VALUES (?,?,?)");
            $stmt->bind_param("ssi", $title, $content, $sort_order);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/help/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑帮助文档' : '添加帮助文档';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑帮助文档' : '添加帮助文档'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" id="helpForm">
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">标题 *</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">内容</label>
            <div id="editor" style="height:500px; border:1px solid var(--border); border-radius:8px; background:var(--bg);"></div>
            <textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($article['content']); ?></textarea>
        </div>
        <div style="margin-bottom:24px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">排序（数字越小越靠前）</label>
            <input type="number" name="sort_order" value="<?php echo htmlspecialchars($article['sort_order']); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center; padding:12px;">保存文档</button>
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
        placeholder: '输入帮助内容...'
    });

    var contentTextarea = document.getElementById('content');
    quill.root.innerHTML = contentTextarea.value;

    document.getElementById('helpForm').addEventListener('submit', function() {
        contentTextarea.value = quill.root.innerHTML;
    });

    // 图片上传
    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        input.onchange = async function() {
            var file = input.files[0];
            if (!file) return;
            var fd = new FormData();
            fd.append('image', file);
            try {
                var response = await fetch('<?php echo BASE_URL; ?>/upload.php', { method:'POST', body:fd });
                var result = await response.json();
                if (result.success && result.url) {
                    var range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1);
                } else {
                    alert('上传失败：' + (result.message || '服务器错误'));
                }
            } catch(e) {
                alert('网络错误，请检查连接');
            }
        };
    });
</script>
<?php require_once __DIR__ . '/../../footer.php'; ?>