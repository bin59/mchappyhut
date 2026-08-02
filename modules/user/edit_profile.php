<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $bio = trim($_POST['bio'] ?? '');

    $avatar = $user['avatar'] ?? '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['avatar']);
        if ($upload['success']) $avatar = $upload['url'];
        else $error = '头像上传失败: ' . $upload['message'];
    } elseif (!empty($_POST['avatar_url'])) $avatar = $_POST['avatar_url'];

    $cover = $user['cover'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['cover']);
        if ($upload['success']) $cover = $upload['url'];
        else $error = '背景上传失败: ' . $upload['message'];
    } elseif (!empty($_POST['cover_url'])) $cover = $_POST['cover_url'];

    if (empty($username)) $error = '用户名不能为空';
    else {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $user['id']);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) $error = '用户名已被使用';
        else {
            $stmt = $conn->prepare("UPDATE users SET username=?, bio=?, avatar=?, cover=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $bio, $avatar, $cover, $user['id']);
            if ($stmt->execute()) redirect(BASE_URL . '/modules/user/profile.php');
            else $error = '保存失败';
        }
    }
}
$pageTitle = '编辑资料';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:600px; margin:0 auto; padding:100px 20px 40px;">
    <h2 style="margin-bottom:24px; font-weight:700;">✏️ 编辑个人资料</h2>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:32px;">
        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">用户名</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">个人简介</label>
            <textarea name="bio" rows="3" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
        </div>
        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">头像（上传或填写URL）</label>
            <input type="file" name="avatar" accept="image/*" style="width:100%; margin-bottom:8px;">
            <input type="text" name="avatar_url" value="<?php echo htmlspecialchars($user['avatar'] ?? ''); ?>" placeholder="或输入头像URL" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
            <?php if ($user['avatar']): ?><img src="<?php echo $user['avatar']; ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover; margin-top:8px;"><?php endif; ?>
        </div>
        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:6px;">背景图（上传或填写URL）</label>
            <input type="file" name="cover" accept="image/*" style="width:100%; margin-bottom:8px;">
            <input type="text" name="cover_url" value="<?php echo htmlspecialchars($user['cover'] ?? ''); ?>" placeholder="或输入背景图URL" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
            <?php if ($user['cover']): ?><img src="<?php echo $user['cover']; ?>" style="width:100%; height:80px; object-fit:cover; border-radius:8px; margin-top:8px;"><?php endif; ?>
        </div>
        <button type="submit" class="btn-auth" style="width:100%; justify-content:center; padding:12px;">保存修改</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>