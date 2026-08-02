<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM `groups` WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
if (!$group) {
    redirect(BASE_URL . '/modules/groups/');
}
$pageTitle = htmlspecialchars($group['name']) . ' - 玩家团体';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:900px; margin:0 auto; padding:100px 20px 40px;">
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:16px; padding:32px;">
        <?php if ($group['cover']): ?>
            <img src="<?php echo htmlspecialchars($group['cover']); ?>" style="width:100%; max-height:300px; object-fit:cover; border-radius:12px; margin-bottom:24px;">
        <?php endif; ?>

        <h1 style="font-size:2.4rem; font-weight:800; margin-bottom:8px;"><?php echo htmlspecialchars($group['name']); ?></h1>
        <?php if ($group['subtitle']): ?>
            <p style="color:var(--text-secondary); font-size:1.1rem; margin-bottom:16px;"><?php echo htmlspecialchars($group['subtitle']); ?></p>
        <?php endif; ?>

        <div style="display:flex; flex-wrap:wrap; gap:16px; margin-bottom:24px; padding:16px 0; border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light);">
            <?php if ($group['leader_name']): ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <?php if ($group['leader_avatar']): ?>
                        <img src="<?php echo htmlspecialchars($group['leader_avatar']); ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    <?php endif; ?>
                    <div>
                        <div style="font-size:0.8rem; color:var(--text-tertiary);">负责人</div>
                        <div style="font-weight:600;"><?php echo htmlspecialchars($group['leader_name']); ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($group['type']): ?>
                <div>
                    <div style="font-size:0.8rem; color:var(--text-tertiary);">团体类型</div>
                    <span style="background:var(--mc-green); color:#fff; padding:4px 12px; border-radius:12px; font-size:0.85rem;"><?php echo htmlspecialchars($group['type']); ?></span>
                </div>
            <?php endif; ?>
            <div>
                <div style="font-size:0.8rem; color:var(--text-tertiary);">创建时间</div>
                <div style="font-weight:600;"><?php echo date('Y-m-d', strtotime($group['created_at'])); ?></div>
            </div>
        </div>

        <?php if ($group['description']): ?>
            <div style="line-height:1.8; color:var(--text-secondary); word-break:break-word;">
                <?php echo nl2br(htmlspecialchars($group['description'])); ?>
            </div>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
            <div style="margin-top:32px; display:flex; gap:12px;">
                <a href="edit.php?id=<?php echo $group['id']; ?>" class="btn-auth"><i class="fas fa-edit"></i> 编辑</a>
                <a href="delete.php?id=<?php echo $group['id']; ?>" class="btn-auth" style="background:#e74c3c;" onclick="return confirm('确定删除此团体？');"><i class="fas fa-trash"></i> 删除</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>