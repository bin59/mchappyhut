<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT p.*, u.username, u.avatar, u.id AS author_id, u.role, c.name AS cat_name 
                        FROM community_posts p 
                        JOIN users u ON p.user_id = u.id 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) redirect(BASE_URL . '/modules/community/');

// 处理评论提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content']) && canPostInCommunity()) {
    $comment = trim($_POST['comment_content']);
    if (!empty($comment)) {
        $user_id = currentUser()['id'];
        $stmt = $conn->prepare("INSERT INTO community_comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id, $user_id, $comment);
        $stmt->execute();
        redirect(BASE_URL . "/modules/community/detail.php?id=$id");
    }
}

// 获取评论
$commentsStmt = $conn->prepare("SELECT c.*, u.username, u.avatar FROM community_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
$commentsStmt->bind_param("i", $id);
$commentsStmt->execute();
$comments = $commentsStmt->get_result();

$pageTitle = htmlspecialchars($post['title']) . ' - 社区';
require_once __DIR__ . '/../../header.php';
?>

<div class="post-detail" style="animation: fadeIn 0.6s ease;">

    <!-- 顶部封面横幅（如果有封面） -->
    <?php if ($post['cover']): ?>
        <div style="position:relative; width:100%; height:45vh; max-height:500px; background: url('<?php echo htmlspecialchars($post['cover']); ?>') center/cover no-repeat; display:flex; align-items:flex-end; border-bottom:4px solid var(--mc-green);">
            <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.5), transparent 60%);"></div>
            <div style="position:relative; z-index:1; padding:0 40px 30px; width:100%;">
                <h1 style="font-size:clamp(2rem, 4vw, 2.8rem); font-weight:800; color:#fff; text-shadow:0 2px 10px rgba(0,0,0,0.5); margin:0; word-break:break-word;"><?php echo htmlspecialchars($post['title']); ?></h1>
            </div>
        </div>
    <?php endif; ?>

    <!-- 主体内容区：左侧作者卡片 + 右侧正文 -->
    <div style="max-width:1600px; margin:0 auto; padding:30px 20px 40px; display:flex; gap:30px; align-items:flex-start;">
        <!-- 左侧作者卡片（sticky） -->
        <div class="post-sidebar" style="flex-shrink:0; width:300px; position:sticky; top:100px;">
            <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:20px; overflow:hidden; box-shadow:var(--shadow-lg); border:1px solid var(--border-light);">
                <!-- 作者头像和名称 -->
                <div style="padding:30px 20px 20px; text-align:center;">
                    <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $post['author_id']; ?>">
                        <img src="<?php echo htmlspecialchars($post['avatar']); ?>" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--surface); box-shadow:var(--shadow-md);">
                    </a>
                    <h3 style="font-size:1.3rem; font-weight:700; margin:12px 0 4px;">
                        <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $post['author_id']; ?>" style="text-decoration:none; color:var(--text);"><?php echo htmlspecialchars($post['username']); ?></a>
                    </h3>
                    <span style="background:<?php echo $post['role'] === 'super_admin' ? '#e74c3c' : ($post['role'] === 'admin' ? '#D4942B' : ($post['role'] === 'group_leader' ? '#3498db' : '#4F8A30')); ?>; color:#fff; padding:2px 12px; border-radius:16px; font-size:0.75rem; font-weight:600; display:inline-block; margin-bottom:12px;">
                        <?php
                        $roles = ['super_admin' => '超级管理员', 'admin' => '管理员', 'group_leader' => '团体负责人', 'senior_adventurer' => '高级冒险家', 'adventurer' => '冒险家', 'restricted' => '受限用户'];
                        echo $roles[$post['role']] ?? '冒险家';
                        ?>
                    </span>
                    <div style="font-size:0.85rem; color:var(--text-secondary);">
                        <div>发布于</div>
                        <div><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></div>
                    </div>
                    <?php if ($post['is_pinned']): ?>
                        <div style="margin-top:12px;"><span style="background:var(--mc-gold-soft); color:#1C1F18; padding:3px 12px; border-radius:12px; font-size:0.75rem;">📌 置顶</span></div>
                    <?php endif; ?>
                    <?php if ($post['tag'] || $post['cat_name']): ?>
                        <div style="margin-top:10px; display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <?php if ($post['tag']): ?><span style="background:rgba(79,138,48,0.15); backdrop-filter:blur(4px); color:var(--mc-green); font-size:0.7rem; padding:2px 10px; border-radius:12px;"><?php echo htmlspecialchars($post['tag']); ?></span><?php endif; ?>
                            <?php if ($post['cat_name']): ?><span style="background:rgba(212,148,43,0.15); backdrop-filter:blur(4px); color:#b8860b; font-size:0.7rem; padding:2px 10px; border-radius:12px;"><?php echo htmlspecialchars($post['cat_name']); ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- 编辑/删除按钮（仅作者或管理员） -->
                <?php if (isAdmin() || (isLoggedIn() && $_SESSION['user_id'] == $post['user_id'])): ?>
                    <div style="padding:16px 20px; border-top:1px solid var(--border-light); display:flex; gap:8px; justify-content:center;">
                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn-auth" style="padding:8px 16px; font-size:0.85rem; text-decoration:none;">编辑</a>
                        <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn-auth" style="background:#e74c3c; padding:8px 16px; font-size:0.85rem; text-decoration:none;" onclick="return confirm('确定删除？');">删除</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 右侧正文区 -->
        <div style="flex:1; min-width:0;">
            <?php if (!$post['cover']): ?>
                <!-- 如果没有封面横幅，标题在此显示 -->
                <h1 style="font-size:2.2rem; font-weight:800; margin:0 0 8px; word-break:break-word;"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div style="display:flex; gap:8px; margin-bottom:20px;">
                    <?php if ($post['tag']): ?><span style="background:rgba(79,138,48,0.15); backdrop-filter:blur(4px); color:var(--mc-green); font-size:0.8rem; padding:3px 12px; border-radius:12px;"><?php echo htmlspecialchars($post['tag']); ?></span><?php endif; ?>
                    <?php if ($post['cat_name']): ?><span style="background:rgba(212,148,43,0.15); backdrop-filter:blur(4px); color:#b8860b; font-size:0.8rem; padding:3px 12px; border-radius:12px;"><?php echo htmlspecialchars($post['cat_name']); ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($post['subtitle']): ?>
                <p style="color:var(--text-secondary); font-size:1.1rem; margin-bottom:20px;"><?php echo htmlspecialchars($post['subtitle']); ?></p>
            <?php endif; ?>

            <!-- 正文内容卡片 -->
            <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:30px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); line-height:1.9; word-break:break-word; margin-bottom:30px;">
                <?php echo $post['content']; ?>
            </div>

            <!-- 评论区 -->
            <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:30px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                <h3 style="font-size:1.4rem; font-weight:700; margin:0 0 20px;">💬 评论 (<?php echo $comments->num_rows; ?>)</h3>
                <?php if (canPostInCommunity()): ?>
                    <form method="POST" style="margin-bottom:24px;">
                        <textarea name="comment_content" rows="3" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);" placeholder="写下你的评论..."></textarea>
                        <button type="submit" class="btn-auth" style="margin-top:12px;">发表评论</button>
                    </form>
                <?php elseif (isLoggedIn()): ?>
                    <p style="color:var(--text-secondary); margin-bottom:24px;">你的等级还不能发表评论。</p>
                <?php else: ?>
                    <p style="color:var(--text-secondary); margin-bottom:24px;">请 <a href="<?php echo BASE_URL; ?>/modules/user/login.php">登录</a> 后评论。</p>
                <?php endif; ?>

                <?php if ($comments->num_rows === 0): ?>
                    <p style="text-align:center; color:var(--text-secondary);">暂无评论，快来抢占沙发</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:20px;">
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div style="border-bottom:1px solid var(--border-light); padding-bottom:20px;">
                                <div style="display:flex; align-items:flex-start; gap:12px;">
                                    <img src="<?php echo $comment['avatar']; ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                                    <div style="flex:1;">
                                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                            <span style="font-weight:600;"><?php echo htmlspecialchars($comment['username']); ?></span>
                                            <span style="font-size:0.8rem; color:var(--text-tertiary);"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></span>
                                        </div>
                                        <p style="line-height:1.6; margin:0;"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                        <?php if (isAdmin() || (isLoggedIn() && $_SESSION['user_id'] == $comment['user_id'])): ?>
                                            <a href="comment_delete.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $id; ?>" style="font-size:0.8rem; color:#e74c3c; text-decoration:none;" onclick="return confirm('删除评论？');">删除</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .post-detail img { max-width:100%; height:auto; border-radius:8px; margin:12px 0; }
    @media (max-width: 768px) {
        .post-sidebar { width: 100% !important; position: static !important; margin-bottom: 20px; }
        [style*="display:flex; gap:30px"] { flex-direction: column; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>