<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$contributor = ['name' => '', 'avatar' => '', 'cover' => '', 'skin_url' => '', 'subtitle' => '', 'description' => '', 'sort_order' => 0];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM contributors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $contributor = $stmt->get_result()->fetch_assoc() ?: $contributor;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $subtitle = $_POST['subtitle'] ?? '';
    $description = $_POST['description'] ?? '';
    $sort_order = intval($_POST['sort_order'] ?? 0);

    // 头像处理
    $avatar = $contributor['avatar'] ?? '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['avatar']);
        if ($upload['success']) $avatar = $upload['url'];
    } elseif (!empty($_POST['avatar_url'])) {
        $avatar = $_POST['avatar_url'];
    }

    // 封面处理
    $cover = $contributor['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
    } elseif (!empty($_POST['cover_url'])) {
        $cover = $_POST['cover_url'];
    }

    // 皮肤处理：仅支持上传，不信任外部 URL
    $skin_url = $contributor['skin_url'] ?? '';
    if (isset($_FILES['skin_file']) && $_FILES['skin_file']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['skin_file']);
        if ($upload['success']) $skin_url = $upload['url'];
    } // 不再接受外部 URL

    if (empty($name)) {
        $error = '姓名不能为空';
    } else {
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE contributors SET name=?, avatar=?, cover=?, skin_url=?, subtitle=?, description=?, sort_order=? WHERE id=?");
            $stmt->bind_param("ssssssii", $name, $avatar, $cover, $skin_url, $subtitle, $description, $sort_order, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO contributors (name, avatar, cover, skin_url, subtitle, description, sort_order) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssi", $name, $avatar, $cover, $skin_url, $subtitle, $description, $sort_order);
        }
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/about/');
        } else {
            $error = '保存失败';
        }
    }
}

$pageTitle = $editMode ? '编辑贡献者' : '添加贡献者';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:700px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px;"><?php echo $editMode ? '编辑贡献者' : '添加贡献者'; ?></h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:16px;"><label>姓名 *</label><input type="text" name="name" value="<?php echo htmlspecialchars($contributor['name']); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"></div>
        <div style="margin-bottom:16px;"><label>副标题</label><input type="text" name="subtitle" value="<?php echo htmlspecialchars($contributor['subtitle'] ?? ''); ?>" style="width:100%; padding:12px;"></div>
        <div style="margin-bottom:16px;"><label>头像（上传）</label><input type="file" name="avatar" accept="image/*"> 或 URL <input type="text" name="avatar_url" value="<?php echo htmlspecialchars($contributor['avatar'] ?? ''); ?>" placeholder="https://..."><?php if (!empty($contributor['avatar'])): ?><img src="<?php echo $contributor['avatar']; ?>" style="width:60px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>背景封面（上传）</label><input type="file" name="cover" accept="image/*"> 或 URL <input type="text" name="cover_url" value="<?php echo htmlspecialchars($contributor['cover'] ?? ''); ?>" placeholder="https://..."><?php if (!empty($contributor['cover'])): ?><img src="<?php echo $contributor['cover']; ?>" style="max-width:200px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>皮肤文件（仅支持上传图片）</label><input type="file" name="skin_file" accept="image/*"><?php if (!empty($contributor['skin_url'])): ?><img src="<?php echo $contributor['skin_url']; ?>" style="max-width:200px; margin-top:8px;"><?php endif; ?></div>
        <div style="margin-bottom:16px;"><label>详细介绍</label><textarea name="description" rows="5" style="width:100%; padding:12px;"><?php echo htmlspecialchars($contributor['description'] ?? ''); ?></textarea></div>
        <div style="margin-bottom:24px;"><label>排序序号</label><input type="number" name="sort_order" value="<?php echo $contributor['sort_order']; ?>" style="width:100%; padding:12px;"></div>
        <button type="submit" class="btn-auth" style="width:200px; justify-content:center; padding:12px;">保存贡献者</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>