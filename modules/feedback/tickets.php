<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$pageTitle = '工单中心';
require_once __DIR__ . '/../../header.php';

$currentUser = currentUser();
$userId = $currentUser['id'];
$role = $currentUser['role'];
$isAdmin = ($role === 'super_admin' || $role === 'admin');

$statusFilter = $_GET['status'] ?? 'all';
$statusCondition = '';
if ($statusFilter === 'unresolved') {
    $statusCondition = " AND t.status != 'resolved'";
} elseif ($statusFilter === 'resolved') {
    $statusCondition = " AND t.status = 'resolved'";
}

// 分页
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// 用户只看自己的工单，管理员看全部
if (!$isAdmin) {
    $where = " WHERE t.user_id = $userId" . $statusCondition;
} else {
    $where = " WHERE 1=1" . $statusCondition;
}

$totalStmt = $conn->query("SELECT COUNT(*) AS total FROM tickets t $where");
$total = $totalStmt->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

$tickets = $conn->query("SELECT t.*, u.username, u.avatar FROM tickets t LEFT JOIN users u ON t.user_id = u.id $where ORDER BY t.updated_at DESC LIMIT $perPage OFFSET $offset");
?>

<div style="max-width:1600px; margin:0 auto; padding:100px 30px 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:30px;">
        <div>
            <h1 style="font-size:2.6rem; font-weight:800; margin:0;">工单中心</h1>
            <p style="color:var(--text-secondary); margin-top:4px;"><?php echo $isAdmin ? '查看所有工单' : '管理您的支持请求'; ?></p>
        </div>
        <div style="display:flex; gap:12px;">
            <a href="ticket_create.php" class="btn-auth" style="text-decoration:none; padding:12px 28px; background:var(--mc-green); color:#fff;">提交工单</a>
            <?php if ($isAdmin): ?>
                <a href="admin_tickets.php" class="btn-auth" style="text-decoration:none; padding:12px 28px; background:#D4942B; color:#000;">管理后台</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 状态筛选 -->
    <div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap;">
        <a href="?status=all" class="btn-auth" style="padding:8px 20px; text-decoration:none; background:<?php echo $statusFilter==='all'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $statusFilter==='all'?'#fff':'var(--text)'; ?>;">全部</a>
        <a href="?status=unresolved" class="btn-auth" style="padding:8px 20px; text-decoration:none; background:<?php echo $statusFilter==='unresolved'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $statusFilter==='unresolved'?'#fff':'var(--text)'; ?>;">未办结</a>
        <a href="?status=resolved" class="btn-auth" style="padding:8px 20px; text-decoration:none; background:<?php echo $statusFilter==='resolved'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $statusFilter==='resolved'?'#fff':'var(--text)'; ?>;">已办结</a>
    </div>

    <!-- 桌面端表格 -->
    <div class="desktop-table" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; border:1px solid var(--border-light); overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:var(--surface-alt);">
                    <th style="padding:18px; text-align:left;">提交者</th>
                    <th style="padding:18px; text-align:left;">工单号</th>
                    <th style="padding:18px; text-align:left;">标题</th>
                    <th style="padding:18px; text-align:left;">状态</th>
                    <th style="padding:18px; text-align:left;">时间</th>
                    <th style="padding:18px; text-align:left;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tickets->num_rows === 0): ?>
                    <tr><td colspan="6" style="padding:60px; text-align:center; color:var(--text-secondary);">暂无工单</td></tr>
                <?php else: ?>
                    <?php while ($ticket = $tickets->fetch_assoc()):
                        $statusLabels = ['draft'=>'草稿','sent'=>'已发送','received'=>'已接收','processing'=>'处理中','on_hold'=>'留置','resolved'=>'已办结'];
                        $statusColors = ['draft'=>'#95a5a6','sent'=>'#3498db','received'=>'#2ecc71','processing'=>'#f39c12','on_hold'=>'#e74c3c','resolved'=>'#27ae60'];
                    ?>
                        <tr style="border-bottom:1px solid var(--border-light);">
                            <td style="padding:18px;">
                                <img src="<?php echo $ticket['avatar']; ?>" style="width:28px; height:28px; border-radius:50%; vertical-align:middle; margin-right:8px;">
                                <?php echo htmlspecialchars($ticket['username'] ?? '未知'); ?>
                            </td>
                            <td style="padding:18px;"><?php echo htmlspecialchars($ticket['ticket_no']); ?></td>
                            <td style="padding:18px;"><?php echo htmlspecialchars($ticket['title']); ?></td>
                            <td style="padding:18px;"><span style="background:<?php echo $statusColors[$ticket['status']]; ?>; color:#fff; padding:4px 14px; border-radius:20px;"><?php echo $statusLabels[$ticket['status']]; ?></span></td>
                            <td style="padding:18px; color:var(--text-secondary);"><?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></td>
                            <td style="padding:18px;">
                                <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="btn-auth" style="padding:8px 18px; background:var(--mc-green); color:#fff; text-decoration:none;">查看</a>
                                <?php if (!$isAdmin && $ticket['status'] === 'draft' && $ticket['user_id'] == $userId): ?>
                                    <a href="ticket_create.php?id=<?php echo $ticket['id']; ?>" style="margin-left:6px; color:var(--mc-green); text-decoration:none;">编辑</a>
                                    <a href="ticket_delete.php?id=<?php echo $ticket['id']; ?>" style="margin-left:6px; color:#e74c3c; text-decoration:none;" onclick="return confirm('确定删除？')">删除</a>
                                <?php elseif (!$isAdmin && $ticket['status'] === 'sent' && $ticket['user_id'] == $userId): ?>
                                    <a href="ticket_recall.php?id=<?php echo $ticket['id']; ?>" style="margin-left:6px; color:#f39c12; text-decoration:none;" onclick="return confirm('撤回工单？')">撤回</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 手机端卡片列表 -->
    <div class="mobile-cards" style="display:none;">
        <?php if ($tickets->num_rows === 0): ?>
            <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:60px; text-align:center; color:var(--text-secondary);">暂无工单</div>
        <?php else: ?>
            <?php while ($ticket = $tickets->fetch_assoc()):
                $statusLabels = ['draft'=>'草稿','sent'=>'已发送','received'=>'已接收','processing'=>'处理中','on_hold'=>'留置','resolved'=>'已办结'];
                $statusColors = ['draft'=>'#95a5a6','sent'=>'#3498db','received'=>'#2ecc71','processing'=>'#f39c12','on_hold'=>'#e74c3c','resolved'=>'#27ae60'];
            ?>
                <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid var(--border-light);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="<?php echo $ticket['avatar']; ?>" style="width:36px; height:36px; border-radius:50%;">
                            <div>
                                <div style="font-weight:600; font-size:0.95rem;"><?php echo htmlspecialchars($ticket['username'] ?? '未知'); ?></div>
                                <div style="font-size:0.8rem; color:var(--text-secondary);"><?php echo $ticket['ticket_no']; ?></div>
                            </div>
                        </div>
                        <span style="background:<?php echo $statusColors[$ticket['status']]; ?>; color:#fff; padding:3px 12px; border-radius:20px; font-size:0.8rem;"><?php echo $statusLabels[$ticket['status']]; ?></span>
                    </div>
                    <h3 style="font-size:1.1rem; font-weight:700; margin:0 0 8px;"><?php echo htmlspecialchars($ticket['title']); ?></h3>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem;">
                        <span style="color:var(--text-secondary);"><?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></span>
                        <div style="display:flex; gap:8px;">
                            <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="btn-auth" style="padding:6px 14px; background:var(--mc-green); color:#fff; text-decoration:none; font-size:0.85rem;">查看</a>
                            <?php if (!$isAdmin && $ticket['status'] === 'draft' && $ticket['user_id'] == $userId): ?>
                                <a href="ticket_create.php?id=<?php echo $ticket['id']; ?>" style="color:var(--mc-green);">编辑</a>
                                <a href="ticket_delete.php?id=<?php echo $ticket['id']; ?>" style="color:#e74c3c;">删除</a>
                            <?php elseif (!$isAdmin && $ticket['status'] === 'sent' && $ticket['user_id'] == $userId): ?>
                                <a href="ticket_recall.php?id=<?php echo $ticket['id']; ?>" style="color:#f39c12;">撤回</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div style="display:flex; justify-content:center; gap:8px; margin-top:30px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?status=<?php echo $statusFilter; ?>&page=<?php echo $i; ?>" style="padding:8px 16px; border-radius:20px; text-decoration:none; background:<?php echo $i===$page?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $i===$page?'#fff':'var(--text)'; ?>;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    @media (max-width: 768px) {
        .desktop-table { display: none !important; }
        .mobile-cards { display: block !important; }
        h1 { font-size: 2rem !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>