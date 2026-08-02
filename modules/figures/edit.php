<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$figure = ['name' => '', 'avatar' => '', 'cover' => '', 'subtitle' => '', 'description' => '', 'sort_order' => 0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM figures WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $figure = $stmt->get_result()->fetch_assoc() ?: $figure;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $subtitle = $_POST['subtitle'] ?? '';
    $description = $_POST['description'] ?? '';
    $sort_order = intval($_POST['sort_order'] ?? 0);

    // 头像处理
    $avatar = $figure['avatar'] ?? '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['avatar']);
        if ($upload['success']) $avatar = $upload['url'];
    } elseif (!empty($_POST['avatar_url'])) {
        $avatar = $_POST['avatar_url'];
    }

    // 封面处理
    $cover = $figure['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    } elseif (!empty($_POST['cover_url'])) {
        $cover = $_POST['cover_url'];
    }

    if (empty($name)) {
        $error = '姓名不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE figures SET name=?, avatar=?, cover=?, subtitle=?, description=?, sort_order=? WHERE id=?");
            $stmt->bind_param("sssssii", $name, $avatar, $cover, $subtitle, $description, $sort_order, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO figures (name, avatar, cover, subtitle, description, sort_order) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("sssssi", $name, $avatar, $cover, $subtitle, $description, $sort_order);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/figures/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑人物' : '添加人物';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑人物' : '添加新人物'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" id="figureForm">
        <div style="margin-bottom:16px;">
            <label>姓名 *</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($figure['name']); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <div style="margin-bottom:16px;">
            <label>副标题（头衔/简介）</label>
            <input type="text" name="subtitle" value="<?php echo htmlspecialchars($figure['subtitle'] ?? ''); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <div style="margin-bottom:16px;">
            <label>头像（上传）</label>
            <input type="file" name="avatar" accept="image/*" style="width:100%; margin-bottom:8px;">
            <label>或头像URL</label>
            <input type="text" name="avatar_url" value="<?php echo htmlspecialchars($figure['avatar'] ?? ''); ?>" placeholder="https://..." style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
            <?php if (!empty($figure['avatar'])): ?><img src="<?php echo $figure['avatar']; ?>" style="width:80px; height:80px; border-radius:50%; object-fit:cover; margin-top:8px;"><?php endif; ?>
        </div>
        <div style="margin-bottom:16px;">
            <label>封面图片（详情页横幅）</label>
            <input type="file" name="cover" accept="image/*" style="width:100%; margin-bottom:8px;">
            <label>或封面URL</label>
            <input type="text" name="cover_url" value="<?php echo htmlspecialchars($figure['cover'] ?? ''); ?>" placeholder="https://..." style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
            <?php if (!empty($figure['cover'])): ?><img src="<?php echo $figure['cover']; ?>" style="max-width:200px; border-radius:8px; margin-top:8px;"><?php endif; ?>
        </div>
        <div style="margin-bottom:16px;">
            <label>详细介绍（支持图文）</label>
            <div id="editor" style="height:400px; border:1px solid var(--border); border-radius:8px; background:var(--bg);"></div>
            <textarea name="description" id="description" style="display:none;"><?php echo htmlspecialchars($figure['description'] ?? ''); ?></textarea>
        </div>
        <div style="margin-bottom:24px;">
            <label>排序序号（数字越小越靠前）</label>
            <input type="number" name="sort_order" value="<?php echo htmlspecialchars($figure['sort_order']); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center; padding:12px;">保存人物</button>
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
        placeholder: '输入人物介绍...'
    });

    var contentTextarea = document.getElementById('description');
    quill.root.innerHTML = contentTextarea.value;

    document.getElementById('figureForm').addEventListener('submit', function() {
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