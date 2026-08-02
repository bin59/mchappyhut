<?php
require_once __DIR__ . '/../../config.php';

// 访问权限：有id则查看指定用户，无id则需登录后查看自己
$viewUserId = isset($_GET['id']) ? intval($_GET['id']) : null;
if ($viewUserId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $viewUserId);
    $stmt->execute();
    $profileUser = $stmt->get_result()->fetch_assoc();
    if (!$profileUser) {
        redirect(BASE_URL . '/modules/user/login.php');
    }
} else {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/modules/user/login.php');
    }
    $profileUser = currentUser();
    $viewUserId = $profileUser['id'];
}

// 获取该用户发布的帖子
$postStmt = $conn->prepare("SELECT * FROM community_posts WHERE user_id = ? ORDER BY created_at DESC");
$postStmt->bind_param("i", $viewUserId);
$postStmt->execute();
$posts = $postStmt->get_result();

$pageTitle = htmlspecialchars($profileUser['username']) . ' 的个人主页';
require_once __DIR__ . '/../../header.php';
?>

<div class="profile-page" style="padding-top: var(--nav-height);">

    <!-- 背景封面：全宽高大图，底部渐变遮罩 -->
    <div class="profile-cover" style="position:relative; width:100%; height:clamp(260px, 38vw, 420px); overflow:hidden; background: var(--surface-alt);">
        <?php if ($profileUser['cover']): ?>
            <img src="<?php echo htmlspecialchars($profileUser['cover']); ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
        <?php else: ?>
            <div style="height:100%; background: linear-gradient(135deg, #2d5a3d 0%, #4F8A30 50%, #D4942B 100%);"></div>
        <?php endif; ?>
        <!-- 底部渐变，让文字更清晰 -->
        <div style="position:absolute; bottom:0; left:0; right:0; height:50%; background:linear-gradient(to top, rgba(0,0,0,0.3), transparent); pointer-events:none;"></div>
        <!-- 编辑封面按钮 (仅本人) -->
        <?php if (isLoggedIn() && $profileUser['id'] == currentUser()['id']): ?>
            <a href="edit_profile.php" style="position:absolute; top:24px; right:24px; background:rgba(0,0,0,0.55); backdrop-filter:blur(10px); color:#fff; width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:1.2rem; border:1px solid rgba(255,255,255,0.2);"><i class="fas fa-camera"></i></a>
        <?php endif; ?>
    </div>

    <!-- 个人信息区域：大号头像叠加 -->
    <div style="max-width:1200px; margin:0 auto; padding:0 30px; position:relative; z-index:1;">
        <div class="profile-header" style="display:flex; align-items:flex-end; gap:32px; margin-top:-80px; flex-wrap:wrap;">
            <!-- 头像 -->
            <div style="position:relative; flex-shrink:0;">
                <div style="border-radius:50%; padding:5px; background:var(--surface); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                    <img src="<?php echo htmlspecialchars($profileUser['avatar']); ?>" style="width:150px; height:150px; border-radius:50%; object-fit:cover; display:block;">
                </div>
                <?php if (isLoggedIn() && $profileUser['id'] == currentUser()['id']): ?>
                    <a href="edit_profile.php" style="position:absolute; bottom:14px; right:14px; background:var(--mc-green); color:#fff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:0.85rem; border:3px solid var(--surface); box-shadow:0 2px 8px rgba(0,0,0,0.2);"><i class="fas fa-camera"></i></a>
                <?php endif; ?>
            </div>

            <!-- 用户名、角色、简介 -->
            <div style="flex:1; min-width:0; padding-bottom:15px;">
                <h1 style="font-size:2.5rem; font-weight:800; margin:0 0 8px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; color:var(--text); letter-spacing:-0.02em;">
                    <?php echo htmlspecialchars($profileUser['username']); ?>
                    <span style="background: <?php echo $profileUser['role'] === 'super_admin' ? '#e74c3c' : ($profileUser['role'] === 'admin' ? '#D4942B' : ($profileUser['role'] === 'group_leader' ? '#3498db' : '#4F8A30')); ?>; color:#fff; padding:4px 16px; border-radius:20px; font-size:0.75rem; font-weight:700; letter-spacing:0.5px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                        <?php
                        $roles = [
                            'super_admin' => '超级管理员',
                            'admin' => '管理员',
                            'group_leader' => '团体负责人',
                            'senior_adventurer' => '高级冒险家',
                            'adventurer' => '冒险家',
                            'restricted' => '受限用户'
                        ];
                        echo $roles[$profileUser['role']] ?? '冒险家';
                        ?>
                    </span>
                </h1>
                <p style="font-size:1.05rem; color:var(--text-secondary); margin:0 0 14px; line-height:1.5;"><?php echo htmlspecialchars($profileUser['bio'] ?: '这个人很懒，什么都没写'); ?></p>
                <div style="display:flex; align-items:center; gap:24px; font-size:0.9rem; color:var(--text-tertiary); flex-wrap:wrap;">
                    <span style="display:flex; align-items:center; gap:6px;"><i class="far fa-calendar-alt"></i> <?php echo date('Y-m-d', strtotime($profileUser['created_at'])); ?></span>
                    <span style="display:flex; align-items:center; gap:6px;"><i class="far fa-file-alt"></i> <?php echo $posts->num_rows; ?> 篇帖子</span>
                    <span style="background:var(--surface-alt); padding:2px 10px; border-radius:12px; font-size:0.8rem;">🆔 <?php echo $profileUser['id']; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 帖子列表区域 -->
    <div style="max-width:1200px; margin:0 auto; padding:50px 30px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:30px;">
            <h2 style="font-size:1.8rem; font-weight:700; margin:0; display:flex; align-items:center; gap:10px;">
                📌 最新帖子
                <span style="font-size:0.9rem; font-weight:400; color:var(--text-secondary);">(<?php echo $posts->num_rows; ?>)</span>
            </h2>
        </div>

        <?php if ($posts->num_rows === 0): ?>
            <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:60px 20px; text-align:center; color:var(--text-secondary); border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                <i class="far fa-newspaper" style="font-size:2rem; margin-bottom:12px; display:block; opacity:0.5;"></i>
                暂无帖子
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:24px;">
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/community/detail.php?id=<?php echo $post['id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="post-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:18px; padding:24px; transition: all 0.25s ease; height:100%; display:flex; flex-direction:column; box-shadow:var(--shadow-sm);">
                            <h3 style="font-size:1.25rem; font-weight:700; margin:0 0 10px; line-height:1.3; word-break:break-word;"><?php echo htmlspecialchars($post['title']); ?></h3>
                            <?php if (!empty($post['subtitle'])): ?>
                                <p style="color:var(--text-secondary); font-size:0.9rem; margin:0 0 12px;"><?php echo htmlspecialchars($post['subtitle']); ?></p>
                            <?php endif; ?>
                            <p style="color:var(--text-secondary); font-size:0.9rem; flex:1; margin:0 0 16px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; line-height:1.5;"><?php echo strip_tags($post['content']); ?></p>
                            <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem; color:var(--text-tertiary); border-top:1px solid var(--border-light); padding-top:14px; margin-top:auto;">
                                <span style="display:flex; align-items:center; gap:4px;"><i class="far fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></span>
                                <span style="display:flex; align-items:center; gap:4px;"><i class="far fa-comment"></i> <?php
                                    $cntStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM community_comments WHERE post_id = ?");
                                    $cntStmt->bind_param("i", $post['id']);
                                    $cntStmt->execute();
                                    echo $cntStmt->get_result()->fetch_assoc()['cnt'];
                                ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .post-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.12); border-color: var(--mc-green); }

    /* ========= 手机端优化 ========= */
    @media (max-width: 768px) {
        .profile-cover { height: 200px !important; }
        .profile-header { margin-top: -60px !important; flex-direction: column; align-items: center; text-align: center; gap: 16px; }
        .profile-header img[style*="width:150px"] { width: 100px !important; height: 100px !important; }
        .profile-header h1 { font-size: 1.8rem !important; justify-content: center; }
        .profile-header [style*="flex:1"] { padding-bottom: 0; text-align: center; }
        .profile-header [style*="font-size:0.9rem; color:var(--text-tertiary)"] { justify-content: center; }
        .post-card { padding: 18px; }
        .post-card h3 { font-size: 1.05rem; }
        h2 { font-size: 1.5rem !important; }
        [style*="max-width:1200px"] { padding-left: 16px !important; padding-right: 16px !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>