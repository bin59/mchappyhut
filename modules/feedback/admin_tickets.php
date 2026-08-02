<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$pageTitle = '工单管理后台';
require_once __DIR__ . '/../../header.php';

$statusFilter = $_GET['status'] ?? 'all';
$statusCondition = '';
if ($statusFilter === 'unresolved') $statusCondition = " AND status != 'resolved'";
elseif ($statusFilter === 'resolved') $statusCondition = " AND status = 'resolved'";

$tickets = $conn->query("SELECT t.*, u.username, u.avatar FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE 1=1 $statusCondition ORDER BY updated_at DESC");
?>

<div style="max-width:1600px; margin:0 auto; padding:100px 30px 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1 style="font-size:2.4rem; font-weight:800;">工单管理</h1>
        <div style="display:flex; gap:12px;">
            <a href="tickets.php" class="btn-auth" style="background:var(--surface-alt);">用户中心</a>
            <a href="ticket_create.php" class="btn-auth" style="background:var(--mc-green); color:#fff;">提交工单</a>
        </div>
    </div>

    <!-- 状态筛选卡片 -->
    <div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap;">
        <a href="?status=all" class="btn-auth" style="padding:10px 24px; border-radius:20px; background:<?php echo $statusFilter==='all'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $statusFilter==='all'?'#fff':'var(--text)'; ?>;">全部 (<?php echo $conn->query("SELECT COUNT(*) AS cnt FROM tickets")->fetch_assoc()['cnt']; ?>)</a>
        <a href="?status=unresolved" class="btn-auth" style="padding:10px 24px; border-radius:20px; background:<?php echo $statusFilter==='unresolved'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $statusFilter==='unresolved'?'#fff':'var(--text)'; ?>;">未办结 (<?php echo $conn->query("SELECT COUNT(*) AS cnt FROM tickets WHERE status != 'resolved'")->fetch_assoc()['cnt']; ?>)</a>
        <a href="?status=resolved" class="btn-auth" style="padding:10px 24px; border-radius:20px; background:<?php echo $statusFilter==='resolved'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $statusFilter==='resolved'?'#fff':'var(--text)'; ?>;">已办结 (<?php echo $conn->query("SELECT COUNT(*) AS cnt FROM tickets WHERE status = 'resolved'")->fetch_assoc()['cnt']; ?>)</a>
    </div>

    <!-- 数据卡片列表 -->
    <div style="display:flex; flex-direction:column; gap:16px;">
        <?php if ($tickets->num_rows === 0): ?>
            <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:60px; text-align:center;">暂无工单</div>
        <?php else: ?>
            <?php while ($ticket = $tickets->fetch_assoc()):
                $statusLabels = ['draft'=>'草稿','sent'=>'已发送','received'=>'已接收','processing'=>'处理中','on_hold'=>'留置','resolved'=>'已办结'];
                $statusColors = ['draft'=>'#95a5a6','sent'=>'#3498db','received'=>'#2ecc71','processing'=>'#f39c12','on_hold'=>'#e74c3c','resolved'=>'#27ae60'];
            ?>
                <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:20px 24px; border:1px solid var(--border-light); display:flex; align-items:center; gap:20px; transition: all 0.2s;"
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--shadow-md)';"
                     onmouseout="this.style.transform=''; this.style.boxShadow='';">
                    <div style="display:flex; align-items:center; gap:12px; min-width:0; flex:1;">
                        <img src="<?php echo $ticket['avatar']; ?>" style="width:44px; height:44px; border-radius:50%;">
                        <div style="min-width:0;">
                            <div style="font-weight:700; font-size:1.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($ticket['title']); ?></div>
                            <div style="color:var(--text-secondary); font-size:0.9rem;"><?php echo htmlspecialchars($ticket['username']); ?> · <?php echo $ticket['ticket_no']; ?></div>
                        </div>
                    </div>
                    <span style="background:<?php echo $statusColors[$ticket['status']]; ?>; color:#fff; padding:4px 16px; border-radius:20px; font-size:0.85rem; white-space:nowrap;"><?php echo $statusLabels[$ticket['status']]; ?></span>
                    <div style="color:var(--text-secondary); font-size:0.9rem; white-space:nowrap;"><?php echo date('m-d H:i', strtotime($ticket['created_at'])); ?></div>
                    <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="btn-auth" style="background:var(--mc-green); color:#fff; padding:8px 20px;">查看</a>
                    <a href="ticket_delete.php?id=<?php echo $ticket['id']; ?>" style="color:#e74c3c; text-decoration:none;" onclick="return confirm('确定删除工单？')">删除</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>