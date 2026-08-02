<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT te.*, u.username, u.avatar, s.name AS server_name FROM timeline_events te JOIN users u ON te.user_id = u.id LEFT JOIN servers s ON te.server_id = s.id WHERE te.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) redirect(BASE_URL . '/modules/timeline/');
$pageTitle = htmlspecialchars($event['title']) . ' - 事件';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:16px; padding:32px;">
        <?php if ($event['cover']): ?>
            <img src="<?php echo htmlspecialchars($event['cover']); ?>" style="width:100%; max-height:300px; object-fit:cover; border-radius:12px; margin-bottom:24px;">
        <?php endif; ?>

        <h1 style="font-size:2.2rem; margin-bottom:8px;"><?php echo htmlspecialchars($event['title']); ?></h1>
        <?php if ($event['subtitle']): ?>
            <p style="color:var(--text-secondary); font-size:1.1rem; margin-bottom:16px;"><?php echo htmlspecialchars($event['subtitle']); ?></p>
        <?php endif; ?>

        <div style="display:flex; flex-wrap:wrap; gap:16px; margin-bottom:24px; padding:16px 0; border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light);">
            <div>
                <div style="font-size:0.8rem; color:var(--text-tertiary);">记录者</div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <img src="<?php echo $event['avatar']; ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                    <span style="font-weight:600;"><?php echo htmlspecialchars($event['username']); ?></span>
                </div>
            </div>
            <div>
                <div style="font-size:0.8rem; color:var(--text-tertiary);">发生时间</div>
                <div style="font-weight:600;"><?php echo date('Y年m月d日 H:i', strtotime($event['event_time'])); ?></div>
            </div>
            <?php if ($event['server_name']): ?>
                <div>
                    <div style="font-size:0.8rem; color:var(--text-tertiary);">关联服务器</div>
                    <div style="font-weight:600;">🖥️ <?php echo htmlspecialchars($event['server_name']); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <div style="line-height:1.8; word-break:break-word;">
            <?php echo $event['content']; ?>
        </div>

        <?php if (isAdmin()): ?>
            <div style="margin-top:32px; display:flex; gap:12px;">
                <a href="edit.php?id=<?php echo $event['id']; ?>" class="btn-auth"><i class="fas fa-edit"></i> 编辑</a>
                <a href="delete.php?id=<?php echo $event['id']; ?>" class="btn-auth" style="background:#e74c3c;" onclick="return confirm('确定删除？');"><i class="fas fa-trash"></i> 删除</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>