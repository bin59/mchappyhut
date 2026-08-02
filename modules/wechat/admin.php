<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
if (!isAdmin()) {
    redirect(BASE_URL . '/modules/user/login.php');
}

$message = '';
$error = '';

// 读取当前二维码
$stmt = $conn->query("SELECT * FROM wechat_qr WHERE id = 1");
$wechat = $stmt->fetch_assoc();
$qr_image_url = $wechat['qr_image_url'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_url = $_POST['qr_image_url'] ?? '';

    // 处理上传
    if (isset($_FILES['qr_file']) && $_FILES['qr_file']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['qr_file']);
        if ($upload['success']) {
            $image_url = $upload['url'];
        } else {
            $error = '上传失败: ' . $upload['message'];
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE wechat_qr SET qr_image_url = ? WHERE id = 1");
        $stmt->bind_param("s", $image_url);
        $stmt->execute();
        $message = '二维码已更新';
        $qr_image_url = $image_url;
    }
}

// 删除二维码
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("UPDATE wechat_qr SET qr_image_url = NULL WHERE id = 1");
    $stmt->execute();
    $message = '二维码已删除';
    $qr_image_url = '';
}

$pageTitle = '微信二维码管理';
$isHomePage = false;
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:700px; margin:0 auto; padding: calc(var(--nav-height) + 40px) 20px 40px;">
    <h1 style="margin-bottom:30px;">微信二维码管理</h1>
    
    <?php if ($message): ?>
        <div style="background:#d4edda; color:#155724; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:20px; padding:30px; border:1px solid var(--border-light);">
        <h2>当前二维码</h2>
        <div style="margin:20px 0; text-align:center;">
            <?php if ($qr_image_url): ?>
                <img src="<?php echo htmlspecialchars($qr_image_url); ?>" style="width:200px; height:200px; object-fit:contain; border:1px solid #eee;">
            <?php else: ?>
                <p style="color:var(--text-secondary);">未设置二维码</p>
            <?php endif; ?>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom:16px;">
                <label style="font-weight:600;">上传二维码图片</label>
                <input type="file" name="qr_file" accept="image/*" style="width:100%; margin-top:8px;">
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-weight:600;">或填写图片URL</label>
                <input type="text" name="qr_image_url" value="<?php echo htmlspecialchars($qr_image_url); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
            </div>
            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn-auth" style="background:var(--mc-green); flex:1;">保存</button>
                <?php if ($qr_image_url): ?>
                    <a href="?delete=1" class="btn-auth" style="background:#e74c3c; flex:1; text-align:center; text-decoration:none;" onclick="return confirm('确定删除二维码？')">删除</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div style="margin-top:20px; text-align:center;">
        <a href="index.php" class="btn-auth" style="background:#07C160;">查看前台页面</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>