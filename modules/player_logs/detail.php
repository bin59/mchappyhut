<?php
require_once __DIR__ . '/../../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT pl.*, u.username, u.avatar, u.id AS author_id, s.name AS server_name 
    FROM player_logs pl 
    JOIN users u ON pl.user_id = u.id 
    LEFT JOIN servers s ON pl.server_id = s.id 
    WHERE pl.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$log = $stmt->get_result()->fetch_assoc();
if (!$log) redirect(BASE_URL . '/modules/player_logs/');

$pageTitle = htmlspecialchars($log['title']) . ' - 玩家日志';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <!-- 面包屑 -->
    <div style="margin-bottom:20px; font-size:0.9rem; color:var(--text-secondary);">
        <a href="index.php" style="color:var(--mc-green); text-decoration:none;">玩家日志</a>
        <span style="margin:0 8px;">/</span>
        <span><?php echo htmlspecialchars($log['title']); ?></span>
    </div>

    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:16px; overflow:hidden;">
        <!-- 封面大图 -->
        <?php if ($log['cover']): ?>
            <img src="<?php echo htmlspecialchars($log['cover']); ?>" 
                style="width:100%; max-height:360px; object-fit:cover;" 
                onerror="this.style.display='none'">
        <?php endif; ?>

        <div style="padding:32px;">
            <!-- 标题和标签 -->
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                <?php if ($log['tag']): ?>
                    <span style="font-size:0.8rem; padding:3px 12px; border-radius:14px; background:rgba(79,138,48,0.12); color:var(--mc-green);"><?php echo htmlspecialchars($log['tag']); ?></span>
                <?php endif; ?>
                <?php if ($log['is_pinned']): ?>
                    <span style="font-size:0.8rem; padding:3px 12px; border-radius:14px; background:rgba(231,76,60,0.1); color:#e74c3c;">📌 置顶</span>
                <?php endif; ?>
            </div>

            <h1 style="font-size:2.2rem; font-weight:800; margin:0 0 8px; line-height:1.3;"><?php echo htmlspecialchars($log['title']); ?></h1>

            <!-- 元信息 -->
            <div style="display:flex; flex-wrap:wrap; gap:20px; margin-bottom:28px; padding:16px 0; border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <img src="<?php echo $log['avatar']; ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                    <div>
                        <div style="font-size:0.8rem; color:var(--text-tertiary);">作者</div>
                        <div style="font-weight:600;"><?php echo htmlspecialchars($log['username']); ?></div>
                    </div>
                </div>
                
                <?php if ($log['game_time']): ?>
                <div>
                    <div style="font-size:0.8rem; color:var(--text-tertiary);">游戏时间</div>
                    <div style="font-weight:600;">📅 <?php echo date('Y年m月d日 H:i', strtotime($log['game_time'])); ?></div>
                </div>
                <?php endif; ?>
                
                <div>
                    <div style="font-size:0.8rem; color:var(--text-tertiary);">发布时间</div>
                    <div style="font-weight:600;"><?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?></div>
                </div>
                
                <?php if ($log['server_name']): ?>
                <div>
                    <div style="font-size:0.8rem; color:var(--text-tertiary);">关联服务器</div>
                    <div style="font-weight:600;">🖥️ <?php echo htmlspecialchars($log['server_name']); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 正文内容 -->
            <div style="line-height:1.9; word-break:break-word; font-size:1.05rem;">
                <?php echo $log['content']; ?>
            </div>

            <!-- 操作按钮 -->
            <?php if (isLoggedIn() && (currentUser()['id'] == $log['author_id'] || isAdmin())): ?>
                <div style="margin-top:32px; padding-top:20px; border-top:1px solid var(--border-light); display:flex; gap:12px;">
                    <a href="edit.php?id=<?php echo $log['id']; ?>" class="btn-auth" style="text-decoration:none;">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <a href="delete.php?id=<?php echo $log['id']; ?>" class="btn-auth" 
                        style="background:#e74c3c; color:#fff; text-decoration:none;"
                        onclick="return confirm('确定要删除这条日志吗？此操作不可撤销。');">
                        <i class="fas fa-trash"></i> 删除
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 返回按钮 -->
    <div style="margin-top:24px;">
        <a href="index.php<?php echo isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'detail.php') === false ? '' : ''; ?>" class="btn-auth" style="text-decoration:none; justify-content:center; width:fit-content; padding:8px 24px;">
            ← 返回列表
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>
