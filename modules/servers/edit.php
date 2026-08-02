<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$server = [
    'name' => '', 'avatar' => '', 'cover' => '', 'subtitle' => '', 
    'address' => '', 'port' => '25565', 'status' => 'online', 
    'category' => '', 'version' => '', 'has_mod' => 0, 
    'client_note' => '', 'max_players' => '', 'description' => '', 'join_link' => ''
];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM servers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $server = $stmt->get_result()->fetch_assoc() ?: $server;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $subtitle = $_POST['subtitle'] ?? '';
    $address = $_POST['address'];
    $port = $_POST['port'] ?? '25565';
    $status = $_POST['status'];
    $category = $_POST['category'] ?? '';
    $version = $_POST['version'] ?? '';
    // 修复：确保接收整数 0 或 1
    $has_mod = isset($_POST['has_mod']) ? intval($_POST['has_mod']) : 0;
    $client_note = $_POST['client_note'] ?? '';
    $max_players = $_POST['max_players'] ? intval($_POST['max_players']) : NULL;
    $description = $_POST['description'];
    $join_link = $_POST['join_link'] ?? '';

    // 头像上传
    $avatar = $server['avatar'] ?? '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['avatar']);
        if ($upload['success']) $avatar = $upload['url'];
    } elseif (!empty($_POST['avatar_url'])) {
        $avatar = $_POST['avatar_url'];
    }

    // 封面上传
    $cover = $server['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    } elseif (!empty($_POST['cover_url'])) {
        $cover = $_POST['cover_url'];
    }

    if (empty($name) || empty($address)) {
        $error = '名称和地址不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE servers SET name=?, avatar=?, cover=?, subtitle=?, address=?, port=?, status=?, category=?, version=?, has_mod=?, client_note=?, max_players=?, description=?, join_link=? WHERE id=?");
            $stmt->bind_param("ssssssssssisssi", $name, $avatar, $cover, $subtitle, $address, $port, $status, $category, $version, $has_mod, $client_note, $max_players, $description, $join_link, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO servers (name, avatar, cover, subtitle, address, port, status, category, version, has_mod, client_note, max_players, description, join_link) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssssisss", $name, $avatar, $cover, $subtitle, $address, $port, $status, $category, $version, $has_mod, $client_note, $max_players, $description, $join_link);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/servers/');
        } else {
            $error = '保存失败：' . $stmt->error;
        }
    }
}

$pageTitle = $editMode ? '编辑服务器' : '添加服务器';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 40px;">
    <h2><?php echo $editMode ? '编辑服务器' : '添加新服务器'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" id="serverForm">
        <div style="margin-bottom:16px;"><label>名称 *</label><input type="text" name="name" value="<?php echo htmlspecialchars($server['name']); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"></div>
        <div style="margin-bottom:16px;"><label>副标题/说明</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($server['subtitle']??''); ?>" style="width:100%; padding:12px;"></div>
        <div style="margin-bottom:16px;"><label>头像</label><input type="file" name="avatar" accept="image/*"><input type="text" name="avatar_url" value="<?php echo htmlspecialchars($server['avatar']??''); ?>" placeholder="或输入URL"><?php if (!empty($server['avatar'])): ?><img src="<?php echo $server['avatar']; ?>" style="width:60px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>封面</label><input type="file" name="cover" accept="image/*"><input type="text" name="cover_url" value="<?php echo htmlspecialchars($server['cover']??''); ?>" placeholder="或输入URL"><?php if (!empty($server['cover'])): ?><img src="<?php echo $server['cover']; ?>" style="max-width:200px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>地址 *</label><input type="text" name="address" value="<?php echo htmlspecialchars($server['address']); ?>" required></div>
        <div style="margin-bottom:16px;"><label>端口</label><input type="text" name="port" value="<?php echo htmlspecialchars($server['port']); ?>"></div>
        <div style="margin-bottom:16px;"><label>状态</label><select name="status"><option value="online" <?php echo $server['status']==='online'?'selected':''; ?>>在线</option><option value="offline" <?php echo $server['status']==='offline'?'selected':''; ?>>离线</option><option value="maintenance" <?php echo $server['status']==='maintenance'?'selected':''; ?>>维护中</option></select></div>
        <div style="margin-bottom:16px;"><label>游戏类别</label>
            <select name="category">
                <option value="">请选择</option>
                <option value="cn_mobile" <?php echo ($server['category']??'')==='cn_mobile'?'selected':''; ?>>中国版移动端</option>
                <option value="cn_java" <?php echo ($server['category']??'')==='cn_java'?'selected':''; ?>>中国版Java端</option>
                <option value="intl_mobile" <?php echo ($server['category']??'')==='intl_mobile'?'selected':''; ?>>国际版移动端</option>
                <option value="intl_java" <?php echo ($server['category']??'')==='intl_java'?'selected':''; ?>>国际版Java端</option>
                <option value="intl_cross" <?php echo ($server['category']??'')==='intl_cross'?'selected':''; ?>>国际版互通端</option>
            </select>
        </div>
        <div style="margin-bottom:16px;"><label>游戏版本</label><input type="text" name="version" value="<?php echo htmlspecialchars($server['version']??''); ?>" placeholder="如1.20.1"></div>
        <div style="margin-bottom:16px;"><label>是否添加模组</label>
            <select name="has_mod">
                <option value="0" <?php echo ($server['has_mod']??0)==0?'selected':''; ?>>否</option>
                <option value="1" <?php echo ($server['has_mod']??0)==1?'selected':''; ?>>是</option>
            </select>
        </div>
        <div style="margin-bottom:16px;"><label>客户端说明</label><textarea name="client_note" rows="3" style="width:100%; padding:12px;"><?php echo htmlspecialchars($server['client_note']??''); ?></textarea></div>
        <div style="margin-bottom:16px;"><label>人数上限</label><input type="number" name="max_players" value="<?php echo htmlspecialchars($server['max_players']??''); ?>"></div>
        <div style="margin-bottom:16px;"><label>详细描述（富文本）</label><div id="editor" style="height:400px; border:1px solid var(--border); border-radius:8px; background:var(--bg);"></div><textarea name="description" id="description" style="display:none;"><?php echo htmlspecialchars($server['description'] ?? ''); ?></textarea></div>
        <div style="margin-bottom:24px;"><label>加入链接</label><input type="text" name="join_link" value="<?php echo htmlspecialchars($server['join_link']??''); ?>" placeholder="https://..."></div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center; padding:12px;">保存服务器</button>
    </form>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: { toolbar: [ [{ 'header': [1,2,3,false] }], ['bold','italic','underline'], [{'list':'ordered'},{'list':'bullet'}], ['blockquote','code-block'], ['link','image'], [{'align':[]}], ['clean'] ] },
        placeholder: '输入服务器详细介绍...'
    });
    var descTextarea = document.getElementById('description');
    quill.root.innerHTML = descTextarea.value;
    document.getElementById('serverForm').addEventListener('submit', function() {
        descTextarea.value = quill.root.innerHTML;
    });
    quill.getModule('toolbar').addHandler('image', function() {
        var input = document.createElement('input'); input.setAttribute('type','file'); input.setAttribute('accept','image/*'); input.click();
        input.onchange = async function() {
            var file = input.files[0]; if (!file) return;
            var formData = new FormData(); formData.append('image', file);
            try {
                var response = await fetch('/upload.php', { method:'POST', body:formData });
                var result = await response.json();
                if (result.success && result.url) { var range = quill.getSelection(true); quill.insertEmbed(range.index, 'image', result.url); quill.setSelection(range.index+1); }
                else { alert('上传失败：'+(result.message || '服务器错误')); }
            } catch (e) { alert('网络错误，请检查连接'); }
        };
    });
</script>
<style>
    @media (max-width: 768px) {
        h2 { font-size: 1.5rem; }
        input, select, textarea { padding: 8px !important; font-size: 0.85rem; }
        .btn-auth { width: 100% !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>