<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

// 获取当前内容
$about = $conn->query("SELECT id, content FROM about LIMIT 1")->fetch_assoc();
$content = $about['content'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newContent = $_POST['content'];
    if ($about) {
        $stmt = $conn->prepare("UPDATE about SET content=? WHERE id=?");
        $stmt->bind_param("si", $newContent, $about['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO about (content) VALUES (?)");
        $stmt->bind_param("s", $newContent);
    }
    if ($stmt->execute()) {
        redirect(BASE_URL . '/modules/about/');
    } else {
        $error = '保存失败';
    }
}

$pageTitle = '编辑关于我们';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;">编辑关于我们</h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <div style="margin-bottom:16px;">
            <label>正文内容</label>
            <div id="editor" style="height:500px; border:1px solid var(--border); border-radius:8px; background:var(--bg);"></div>
            <textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($content); ?></textarea>
        </div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center;">保存</button>
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
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                [{ 'align': [] }],
                ['clean']
            ]
        },
        placeholder: '输入关于我们的介绍...'
    });
    var contentTextarea = document.getElementById('content');
    quill.root.innerHTML = contentTextarea.value;
    document.querySelector('form').onsubmit = function() {
        contentTextarea.value = quill.root.innerHTML;
    };
    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        input.onchange = function() {
            var file = input.files[0];
            var formData = new FormData();
            formData.append('image', file);
            fetch('<?php echo BASE_URL; ?>/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    var range = quill.getSelection();
                    quill.insertEmbed(range.index, 'image', result.url);
                } else {
                    alert('上传失败: ' + result.message);
                }
            });
        };
    });
</script>

<?php require_once __DIR__ . '/../../footer.php'; ?>