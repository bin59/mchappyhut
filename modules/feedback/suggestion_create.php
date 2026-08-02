<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$pageTitle = '提交建议';
require_once __DIR__ . '/../../header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $category = $_POST['category'] ?? 'other';
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        $error = '标题和内容不能为空';
    } else {
        $userId = currentUser()['id'];
        $stmt = $conn->prepare("INSERT INTO suggestions (user_id, title, subtitle, category, content) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $userId, $title, $subtitle, $category, $content);
        if ($stmt->execute()) {
            redirect(BASE_URL . '/modules/feedback/suggestions.php');
        } else {
            $error = '提交失败';
        }
    }
}
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 60px;">
    <h1 style="font-size:2.2rem; font-weight:800; margin-bottom:24px;">提出建议</h1>
    <?php if (isset($error)): ?><div style="color:#e74c3c; margin-bottom:16px;"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:20px; padding:30px; border:1px solid var(--border-light);">
            <div style="margin-bottom:16px;">
                <label style="font-weight:600;">建议标题 *</label>
                <input type="text" name="title" required style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);">
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-weight:600;">副标题</label>
                <input type="text" name="subtitle" style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);">
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-weight:600;">事项选择</label>
                <select name="category" style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);">
                    <option value="game">游戏</option>
                    <option value="forum">论坛</option>
                    <option value="website">网站</option>
                    <option value="other">其他</option>
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-weight:600;">详细描述 *</label>
                <textarea name="content" rows="6" required style="width:100%; padding:14px; border-radius:12px; background:var(--bg); color:var(--text);"></textarea>
            </div>
            <button type="submit" class="btn-auth" style="width:100%; padding:14px; background:var(--mc-green); color:#fff; border-radius:12px; font-size:1rem;">提交建议</button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>