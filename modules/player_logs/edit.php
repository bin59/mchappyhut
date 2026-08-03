<?php
require_once __DIR__ . '/../../config.php';
requireLogin();

// 需要 senior_adventurer 及以上才能发布日志
if (!canPostInCommunity()) {
    redirect(BASE_URL . '/modules/player_logs/');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$log = ['title' => '', 'content' => '', 'cover' => '', 'tag' => '', 'server_id' => '', 'game_time' => date('Y-m-d\TH:i'), 'is_pinned' => 0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM player_logs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) redirect(BASE_URL . '/modules/player_logs/');
    
    // 仅作者本人或管理员可编辑
    if ($row['user_id'] != currentUser()['id'] && !isAdmin()) {
        redirect(BASE_URL . '/modules/player_logs/detail.php?id=' . $id);
    }
    $log = $row;
    // 格式化 gametime 为 datetime-local 格式
    if ($log['game_time']) {
        $log['game_time'] = date('Y-m-d\TH:i', strtotime($log['game_time']));
    } else {
        $log['game_time'] = '';
    }
}

$servers = $conn->query("SELECT id, name FROM servers ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $tag = trim($_POST['tag'] ?? '');
    $server_id = $_POST['server_id'] ? intval($_POST['server_id']) : NULL;
    $game_time = $_POST['game_time'] ?: NULL;
    $is_pinned = isset($_POST['is_pinned']) && isAdmin() ? 1 : ($editMode ? $log['is_pinned'] : 0);

    // 封面图片处理
    $cover = $log['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    }
    // 封面URL输入
    if (!empty($_POST['cover_url'])) {
        $cover = trim($_POST['cover_url']);
    }

    if (empty($title) || empty($content)) {
        $error = '标题和内容不能为空';
    } else {
        $user_id = currentUser()['id'];
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE player_logs SET title=?, content=?, cover=?, tag=?, server_id=?, game_time=?, is_pinned=? WHERE id=?");
            $stmt->bind_param("ssssisii", $title, $content, $cover, $tag, $server_id, $game_time, $is_pinned, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO player_logs (title, content, cover, tag, server_id, game_time, user_id, is_pinned) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssisii", $title, $content, $cover, $tag, $server_id, $game_time, $user_id, $is_pinned);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/player_logs/');
        } else {
            $error = '保存失败：' . $conn->error;
        }
    }
}

$pageTitle = $editMode ? '编辑日志' : '写日志';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:8px;"><?php echo $editMode ? '📝 编辑日志' : '✏️ 写日志'; ?></h2>
    <p style="color:var(--text-secondary); margin-bottom:24px;">分享你的 Minecraft 冒险故事、建筑成果或生存经历</p>

    <?php if (isset($error)): ?>
        <div style="color:#e74c3c; background:rgba(231,76,60,0.1); padding:12px 16px; border-radius:8px; margin-bottom:20px; border:1px solid rgba(231,76,60,0.2);">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:16px; padding:28px;">
        <!-- 标题 -->
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">日志标题 *</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($log['title']); ?>" required 
                placeholder="给日志起个吸引人的标题..."
                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:1rem;">
        </div>

        <!-- 标签 + 服务器（同行） -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
            <div>
                <label style="font-weight:600; display:block; margin-bottom:6px;">分类标签</label>
                <input type="text" name="tag" value="<?php echo htmlspecialchars($log['tag'] ?? ''); ?>" 
                    placeholder="如：生存日记、建筑记录、红石研究..."
                    style="width:100%; padding:12px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
            </div>
            <div>
                <label style="font-weight:600; display:block; margin-bottom:6px;">关联服务器</label>
                <select name="server_id" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
                    <option value="">不关联</option>
                    <?php 
                    $servers->data_seek(0);
                    while ($srv = $servers->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $srv['id']; ?>" <?php echo ($log['server_id'] == $srv['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($srv['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <!-- 游戏时间 -->
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">游戏内事件时间（可选）</label>
            <input type="datetime-local" name="game_time" value="<?php echo htmlspecialchars($log['game_time']); ?>"
                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem; max-width:300px;">
            <span style="font-size:0.8rem; color:var(--text-tertiary);">记录事件在游戏中实际发生的时间</span>
        </div>

        <!-- 封面图片 -->
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">封面图片</label>
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-start;">
                <input type="file" name="cover" accept="image/*" 
                    style="padding:10px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text);">
                <span style="color:var(--text-tertiary); align-self:center;">或</span>
                <input type="text" name="cover_url" placeholder="图片URL地址" value="<?php echo $editMode ? '' : ''; ?>"
                    style="flex:1; min-width:200px; padding:12px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text);">
            </div>
            <?php if ($editMode && $log['cover']): ?>
                <img src="<?php echo htmlspecialchars($log['cover']); ?>" style="max-width:200px; margin-top:10px; border-radius:8px;">
            <?php endif; ?>
        </div>

        <!-- 富文本编辑器 -->
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">日志内容 *</label>
            <div id="editor" style="height:450px; border:1px solid var(--border); border-radius:10px; background:var(--bg); font-size:1rem;"></div>
            <textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($log['content'] ?? ''); ?></textarea>
        </div>

        <!-- 置顶开关（仅管理员） -->
        <?php if (isAdmin()): ?>
        <div style="margin-bottom:20px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_pinned" value="1" <?php echo $log['is_pinned'] ? 'checked' : ''; ?>
                    style="width:18px; height:18px; accent-color:var(--mc-green);">
                <span style="font-weight:600;">📌 置顶此日志</span>
            </label>
        </div>
        <?php endif; ?>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn-auth" style="padding:12px 32px; background:var(--mc-green); color:#fff; justify-content:center; font-size:1rem;">
                <i class="fas fa-save"></i>&nbsp; <?php echo $editMode ? '保存修改' : '发布日志'; ?>
            </button>
            <a href="<?php echo $editMode ? 'detail.php?id=' . $id : 'index.php'; ?>" class="btn-auth" style="text-decoration:none; justify-content:center; padding:12px 24px;">
                取消
            </a>
        </div>
    </form>
</div>

<!-- Quill 富文本编辑器 -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    var quill = new Quill('#editor', { 
        theme: 'snow', 
        modules: { 
            toolbar: [ 
                [{ 'header': [1, 2, 3, false] }], 
                ['bold', 'italic', 'underline', 'strike'], 
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'code-block'], 
                ['link', 'image'], 
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean'] 
            ] 
        }, 
        placeholder: '记录你在方块世界的精彩故事...'
    });
    
    var contentTextarea = document.getElementById('content');
    quill.root.innerHTML = contentTextarea.value;
    document.querySelector('form').onsubmit = function() { 
        contentTextarea.value = quill.root.innerHTML; 
    };
    
    // 自定义图片上传
    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input'); 
        input.setAttribute('type', 'file'); 
        input.setAttribute('accept', 'image/*'); 
        input.click();
        input.onchange = function() {
            var file = input.files[0]; 
            var formData = new FormData(); 
            formData.append('image', file);
            fetch('<?php echo BASE_URL; ?>/upload.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(result => { 
                if (result.success) { 
                    var range = quill.getSelection(); 
                    quill.insertEmbed(range.index, 'image', result.url); 
                } else {
                    alert('图片上传失败');
                }
            })
            .catch(() => alert('上传请求失败'));
        };
    });
</script>

<style>
    .ql-editor { font-size: 1rem; line-height: 1.8; }
    .ql-toolbar { border-radius: 10px 10px 0 0; border-color: var(--border) !important; background: var(--surface-alt); }
    .ql-container { border-radius: 0 0 10px 10px; border-color: var(--border) !important; }
    .ql-editor.ql-blank::before { color: var(--text-tertiary); font-style: normal; }
    @media (max-width: 768px) {
        [style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; }
        #editor { height: 350px !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>
