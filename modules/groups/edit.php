<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$group = ['name' => '', 'cover' => '', 'subtitle' => '', 'type' => '', 'leader_name' => '', 'leader_avatar' => '', 'description' => ''];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM `groups` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $group = $stmt->get_result()->fetch_assoc() ?: $group;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $subtitle = $_POST['subtitle'] ?? '';
    $type = $_POST['type'] ?? '';
    $leader_name = $_POST['leader_name'] ?? '';
    $description = $_POST['description'] ?? '';

    // 封面处理
    $cover = $group['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) {
            $cover = $upload['url'];
        }
    }

    // 负责人头像处理
    $leader_avatar = $group['leader_avatar'] ?? '';
    if (isset($_FILES['leader_avatar']) && $_FILES['leader_avatar']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['leader_avatar']);
        if ($upload['success']) {
            $leader_avatar = $upload['url'];
        }
    } elseif (!empty($_POST['leader_avatar_url'])) {
        $leader_avatar = $_POST['leader_avatar_url'];
    }

    if (empty($name)) {
        $error = '团体名称不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE `groups` SET name=?, cover=?, subtitle=?, type=?, leader_name=?, leader_avatar=?, description=? WHERE id=?");
            $stmt->bind_param("sssssssi", $name, $cover, $subtitle, $type, $leader_name, $leader_avatar, $description, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO `groups` (name, cover, subtitle, type, leader_name, leader_avatar, description) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $name, $cover, $subtitle, $type, $leader_name, $leader_avatar, $description);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/groups/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑团体' : '创建团体';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:700px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑团体' : '创建新团体'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:16px;"><label>团体名称 *</label><input type="text" name="name" value="<?php echo htmlspecialchars($group['name']); ?>" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>副标题</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($group['subtitle'] ?? ''); ?>" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>封面图片（上传）</label><input type="file" name="cover" accept="image/*" style="width:100%;"><?php if ($group['cover']): ?><img src="<?php echo $group['cover']; ?>" style="max-width:200px; margin-top:8px; border-radius:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>团体类型</label><input type="text" name="type" value="<?php echo htmlspecialchars($group['type'] ?? ''); ?>" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);" placeholder="如：建筑、红石、生存"></div>
        <div style="margin-bottom:16px;"><label>负责人名称</label><input type="text" name="leader_name" value="<?php echo htmlspecialchars($group['leader_name'] ?? ''); ?>" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:16px;"><label>负责人头像（上传）</label><input type="file" name="leader_avatar" accept="image/*" style="width:100%;"><?php if ($group['leader_avatar']): ?><img src="<?php echo $group['leader_avatar']; ?>" style="width:48px; height:48px; border-radius:50%; object-fit:cover; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>或头像URL</label><input type="text" name="leader_avatar_url" value="<?php echo htmlspecialchars($group['leader_avatar'] ?? ''); ?>" placeholder="https://..." style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"></div>
        <div style="margin-bottom:24px;"><label>团体详情</label><textarea name="description" rows="6" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><?php echo htmlspecialchars($group['description'] ?? ''); ?></textarea></div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center;">保存团体</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>