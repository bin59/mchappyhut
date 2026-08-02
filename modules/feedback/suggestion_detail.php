<?php
require_once __DIR__ . '/../../config.php';
// 不再强制登录，允许任何人查看
$id = intval($_GET['id'] ?? 0);
$suggestion = $conn->query("SELECT s.*, u.username, u.avatar, u.id AS author_id FROM suggestions s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = $id")->fetch_assoc();
if (!$suggestion) die('建议不存在');

// 评论提交（需要登录）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isLoggedIn()) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $userId = currentUser()['id'];
        $isAdmin = isAdmin() ? 1 : 0;
        $stmt = $conn->prepare("INSERT INTO suggestion_comments (suggestion_id, user_id, is_admin, content) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis", $id, $userId, $isAdmin, $comment);
        $stmt->execute();
        redirect(BASE_URL . "/modules/feedback/suggestion_detail.php?id=$id");
    }
}

// 查询评论，并关联用户的 role 字段以显示角色标签
$comments = $conn->query("SELECT c.*, u.username, u.avatar, u.role FROM suggestion_comments c JOIN users u ON c.user_id = u.id WHERE c.suggestion_id = $id ORDER BY c.created_at ASC");

$pageTitle = htmlspecialchars($suggestion['title']) . ' - 建议';
require_once __DIR__ . '/../../header.php';

$roleLabels = [
    'super_admin' => '超级管理员',
    'admin' => '管理员',
    'group_leader' => '团体负责人',
    'senior_adventurer' => '高级冒险家',
    'adventurer' => '冒险家',
    'restricted' => '受限用户'
];
?>

<div style="max-width:1000px; margin:0 auto; padding:100px 20px 60px;">
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:30px; margin-bottom:24px; border:1px solid var(--border-light);">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
            <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $suggestion['author_id']; ?>">
                <img src="<?php echo $suggestion['avatar'] ?: 'assets/images/default-avatar.png'; ?>" style="width:44px; height:44px; border-radius:50%;">
            </a>
            <div>
                <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $suggestion['author_id']; ?>" style="text-decoration:none; color:var(--text);"><span style="font-weight:700;"><?php echo htmlspecialchars($suggestion['username']); ?></span></a>
                <div style="color:var(--text-secondary); font-size:0.9rem;"><?php echo date('Y-m-d H:i', strtotime($suggestion['created_at'])); ?></div>
            </div>
            <span style="margin-left:auto; background:var(--mc-green); color:#fff; padding:4px 14px; border-radius:20px; font-size:0.85rem;">
                <?php $catLabels = ['game'=>'游戏','forum'=>'论坛','website'=>'网站','other'=>'其他']; echo $catLabels[$suggestion['category']] ?? '其他'; ?>
            </span>
        </div>
        <h1 style="font-size:2rem; font-weight:800; margin-bottom:8px;"><?php echo htmlspecialchars($suggestion['title']); ?></h1>
        <?php if ($suggestion['subtitle']): ?>
            <p style="color:var(--text-secondary); font-size:1.1rem; margin-bottom:16px;"><?php echo htmlspecialchars($suggestion['subtitle']); ?></p>
        <?php endif; ?>
        <div style="line-height:1.8;"><?php echo nl2br(htmlspecialchars($suggestion['content'])); ?></div>
    </div>

    <!-- 评论区 -->
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:24px; border:1px solid var(--border-light);">
        <h3 style="margin-bottom:16px;">讨论 (<?php echo $comments->num_rows; ?>)</h3>
        <?php while ($c = $comments->fetch_assoc()): ?>
            <div style="border-bottom:1px solid var(--border-light); padding:16px 0;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                    <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $c['user_id']; ?>">
                        <img src="<?php echo $c['avatar']; ?>" style="width:28px; height:28px; border-radius:50%;">
                    </a>
                    <span style="font-weight:600;"><?php echo htmlspecialchars($c['username']); ?></span>
                    <?php if ($c['is_admin']): ?>
                        <span style="background:var(--mc-green); color:#fff; padding:2px 8px; border-radius:8px; font-size:0.75rem;">管理员</span>
                    <?php else: ?>
                        <span style="background:var(--surface-alt); color:var(--text-secondary); padding:2px 8px; border-radius:8px; font-size:0.75rem;"><?php echo $roleLabels[$c['role']] ?? '冒险家'; ?></span>
                    <?php endif; ?>
                    <span style="margin-left:auto; font-size:0.85rem; color:var(--text-secondary);"><?php echo date('m-d H:i', strtotime($c['created_at'])); ?></span>
                </div>
                <div style="line-height:1.6;"><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
                <?php if (isLoggedIn() && (isAdmin() || $c['user_id'] == currentUser()['id'])): ?>
                    <div style="margin-top:6px;">
                        <a href="suggestion_comment_delete.php?id=<?php echo $c['id']; ?>&suggestion_id=<?php echo $id; ?>" style="color:#e74c3c; font-size:0.8rem;">删除</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <?php if (isLoggedIn()): ?>
            <form method="POST" style="margin-top:20px;">
                <textarea name="comment" rows="3" required placeholder="写下你的看法..." style="width:100%; padding:12px; border-radius:10px; background:var(--bg); color:var(--text);"></textarea>
                <button type="submit" class="btn-auth" style="margin-top:10px;">发表评论</button>
            </form>
        <?php else: ?>
            <p style="text-align:center; margin-top:20px; color:var(--text-secondary);"><a href="<?php echo BASE_URL; ?>/modules/user/login.php">登录</a> 后即可参与讨论</p>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>