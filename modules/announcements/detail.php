<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT a.*, u.username, u.avatar, u.id AS author_id FROM announcements a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$announcement = $stmt->get_result()->fetch_assoc();
if (!$announcement) {
    redirect(BASE_URL . '/modules/announcements/');
}
$pageTitle = htmlspecialchars($announcement['title']) . ' - 公告';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:860px; margin:0 auto; padding-top:var(--nav-height); padding-bottom:40px; animation: fadeIn 0.6s ease;">
    <!-- 全屏封面区域 -->
    <?php if ($announcement['cover']): ?>
        <div class="cover-hero" style="position:relative; border-radius:0 0 20px 20px; overflow:hidden; max-height:450px; margin-bottom:32px;">
            <img src="<?php echo htmlspecialchars($announcement['cover']); ?>" style="width:100%; height:100%; object-fit:cover; display:block; filter: brightness(0.9);">
            <!-- 叠加渐变与标题 -->
            <div style="position:absolute; bottom:0; left:0; right:0; padding:32px 28px 24px; background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; flex-wrap:wrap;">
                    <h1 style="font-size:clamp(1.6rem, 4vw, 2.6rem); font-weight:800; color:#fff; text-shadow: 0 1px 8px rgba(0,0,0,0.5); margin:0; line-height:1.2;"><?php echo htmlspecialchars($announcement['title']); ?></h1>
                    <?php if ($announcement['tag']): ?>
                        <span style="background:rgba(255,255,255,0.2); backdrop-filter:blur(8px); color:#fff; font-size:0.7rem; padding:3px 12px; border-radius:14px; border:1px solid rgba(255,255,255,0.3);"><?php echo htmlspecialchars($announcement['tag']); ?></span>
                    <?php endif; ?>
                    <?php if ($announcement['is_featured']): ?>
                        <span style="background:rgba(232,184,75,0.8); color:#1C1F18; font-size:0.7rem; padding:3px 12px; border-radius:14px;">特别公告</span>
                    <?php endif; ?>
                </div>
                <?php if ($announcement['subtitle']): ?>
                    <p style="color:rgba(255,255,255,0.9); font-size:0.95rem; margin-bottom:0; text-shadow:0 1px 4px rgba(0,0,0,0.4);"><?php echo htmlspecialchars($announcement['subtitle']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- 无封面时的标题区 -->
        <div style="padding:32px 0 16px; margin-bottom:24px; border-bottom:1px solid var(--border-light);">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px;">
                <h1 style="font-size:clamp(1.8rem, 4.5vw, 2.6rem); font-weight:800; margin:0; word-break:break-word;"><?php echo htmlspecialchars($announcement['title']); ?></h1>
                <?php if ($announcement['tag']): ?><span style="background:var(--mc-green); color:#fff; font-size:0.75rem; padding:3px 14px; border-radius:16px;"><?php echo htmlspecialchars($announcement['tag']); ?></span><?php endif; ?>
                <?php if ($announcement['is_featured']): ?><span style="background:var(--mc-gold-soft); color:#1C1F18; font-size:0.75rem; padding:3px 14px; border-radius:16px;">特别公告</span><?php endif; ?>
            </div>
            <?php if ($announcement['subtitle']): ?>
                <p style="color:var(--text-secondary); font-size:1.05rem;"><?php echo htmlspecialchars($announcement['subtitle']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 作者栏与元信息 -->
    <div style="padding:0 20px; display:flex; align-items:center; gap:12px; margin-bottom:28px; flex-wrap:wrap;">
        <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $announcement['author_id']; ?>" style="text-decoration:none; display:flex; align-items:center; gap:10px;">
            <img src="<?php echo $announcement['avatar']; ?>" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid var(--border-light);">
            <span style="font-weight:600; color:var(--text); font-size:0.9rem;"><?php echo htmlspecialchars($announcement['username']); ?></span>
        </a>
        <div style="flex:1;"></div>
        <div style="font-size:0.8rem; color:var(--text-tertiary); display:flex; align-items:center; gap:6px;">
            <i class="far fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($announcement['created_at'])); ?>
        </div>
        <?php if ($announcement['server_id']): ?>
            <?php
            $srvStmt = $conn->prepare("SELECT name FROM servers WHERE id = ?");
            $srvStmt->bind_param("i", $announcement['server_id']);
            $srvStmt->execute();
            $server = $srvStmt->get_result()->fetch_assoc();
            ?>
            <span class="server-badge"><?php echo $server ? htmlspecialchars($server['name']) : '指定服务器'; ?></span>
        <?php else: ?>
            <span class="server-badge">全服</span>
        <?php endif; ?>
    </div>

    <!-- 正文（毛玻璃卡片） -->
    <div style="padding:0 20px;">
        <div class="content-card" style="background:var(--surface-glass); backdrop-filter:blur(12px); border:1px solid var(--border-light); border-radius:16px; padding:28px; line-height:1.9; font-size:clamp(0.9rem, 2vw, 1rem); word-break:break-word; color:var(--text);">
            <?php echo $announcement['content']; ?>
        </div>
    </div>

    <?php if (isAdmin()): ?>
        <div style="padding:0 20px; margin-top:32px; display:flex; gap:12px; flex-wrap:wrap;">
            <a href="edit.php?id=<?php echo $announcement['id']; ?>" class="btn-auth"><i class="fas fa-edit"></i> 编辑</a>
            <a href="delete.php?id=<?php echo $announcement['id']; ?>" class="btn-auth" style="background:#e74c3c;" onclick="return confirm('确定删除？');"><i class="fas fa-trash"></i> 删除</a>
        </div>
    <?php endif; ?>
</div>

<style>
    @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .server-badge { display:inline-block; background:linear-gradient(135deg,#4F8A30,#6DB840); border:1px solid rgba(255,255,255,0.3); border-radius:20px; padding:3px 14px; font-size:0.75rem; font-weight:600; color:#fff; }
    .content-card img { max-width:100%; border-radius:12px; margin:12px 0; }
    /* 手机端进一步缩小字体 */
    @media (max-width: 768px) {
        .cover-hero { max-height:300px !important; border-radius:0 !important; }
        .cover-hero h1 { font-size:1.4rem !important; }
        .cover-hero p { font-size:0.85rem !important; }
        .content-card { padding:20px !important; font-size:0.88rem !important; }
    }
</style>

<?php require_once __DIR__ . '/../../footer.php'; ?>