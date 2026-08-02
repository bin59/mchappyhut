<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '活动中心';
require_once __DIR__ . '/../../header.php';

// 状态筛选
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// 构建查询条件
$where = "WHERE 1=1";
$params = [];
$types = "";
if ($statusFilter === 'ongoing') {
    $where .= " AND start_time <= NOW() AND end_time >= NOW()";
} elseif ($statusFilter === 'upcoming') {
    $where .= " AND start_time > NOW()";
} elseif ($statusFilter === 'ended') {
    $where .= " AND end_time < NOW()";
}

// 获取总数
$countSql = "SELECT COUNT(*) AS total FROM events $where";
$countStmt = $conn->prepare($countSql);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

// 获取活动列表
$listSql = "SELECT * FROM events $where ORDER BY is_pinned DESC, start_time ASC LIMIT ? OFFSET ?";
$listStmt = $conn->prepare($listSql);
$listStmt->bind_param("ii", $perPage, $offset);
$listStmt->execute();
$events = $listStmt->get_result();
?>

<!-- 顶部横幅 (ag5.png) -->
<div class="banner-animate" style="position:relative; width:100%; min-height:300px; display:flex; align-items:center; justify-content:flex-end; padding:0 40px; background: linear-gradient(to bottom, rgba(10,14,8,0.35), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/assets/images/ag5.png') center/cover no-repeat; border-bottom:4px solid #c9a84c;">
    <div style="text-align:right; color:#fff; z-index:1;">
        <h1 style="font-size:clamp(2.5rem,5vw,3.8rem); font-weight:800; margin-bottom:8px; opacity:0; animation: fadeInDown 0.8s ease forwards 0.1s;">活动中心</h1>
        <p style="font-size:1.2rem; opacity:0; animation: fadeInUp 0.8s ease forwards 0.3s;">精彩活动，不容错过</p>
    </div>
</div>

<!-- 内容区域 -->
<div style="max-width:1800px; margin:0 auto; padding:40px 20px 60px; display:flex; gap:30px; align-items:flex-start;">

    <!-- 左侧状态筛选栏 (平板端保留，手机端隐藏) -->
    <div class="events-sidebar" style="flex-shrink:0; width:200px; position:sticky; top:100px; z-index:10; opacity:0; animation: slideInLeft 0.6s ease forwards 0.2s;">
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:20px; border:1px solid var(--border-light);">
            <h3 style="margin:0 0 16px;">📅 状态</h3>
            <ul style="list-style:none; padding:0; margin:0;">
                <li style="margin-bottom:6px;">
                    <a href="?status=all" style="display:block; padding:8px 12px; border-radius:8px; text-decoration:none; color:<?php echo $statusFilter==='all' ? '#fff' : 'var(--text)'; ?>; background:<?php echo $statusFilter==='all' ? 'var(--mc-green)' : 'transparent'; ?>;">全部</a>
                </li>
                <li style="margin-bottom:6px;">
                    <a href="?status=ongoing" style="display:block; padding:8px 12px; border-radius:8px; text-decoration:none; color:<?php echo $statusFilter==='ongoing' ? '#fff' : 'var(--text)'; ?>; background:<?php echo $statusFilter==='ongoing' ? '#4F8A30' : 'transparent'; ?>;">进行中</a>
                </li>
                <li style="margin-bottom:6px;">
                    <a href="?status=upcoming" style="display:block; padding:8px 12px; border-radius:8px; text-decoration:none; color:<?php echo $statusFilter==='upcoming' ? '#fff' : 'var(--text)'; ?>; background:<?php echo $statusFilter==='upcoming' ? '#D4942B' : 'transparent'; ?>;">未开始</a>
                </li>
                <li style="margin-bottom:6px;">
                    <a href="?status=ended" style="display:block; padding:8px 12px; border-radius:8px; text-decoration:none; color:<?php echo $statusFilter==='ended' ? '#fff' : 'var(--text)'; ?>; background:<?php echo $statusFilter==='ended' ? '#5E6259' : 'transparent'; ?>;">已结束</a>
                </li>
            </ul>
            <?php if (isAdmin()): ?>
                <div style="margin-top:16px; border-top:1px solid var(--border-light); padding-top:12px;">
                    <a href="edit.php" class="btn-auth" style="width:100%; justify-content:center; font-size:0.85rem; padding:8px;">发布活动</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 右侧活动卡片列表 -->
    <div style="flex:1; min-width:0;">
        <?php if ($events->num_rows === 0): ?>
            <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:80px 20px; text-align:center; color:var(--text-secondary); border:1px solid var(--border-light); animation: fadeIn 0.6s ease;">暂无活动</div>
        <?php else: ?>
            <div class="events-grid" style="display:grid; gap:24px;">
                <?php $index = 0; while ($event = $events->fetch_assoc()): 
                    $now = new DateTime();
                    $start = new DateTime($event['start_time']);
                    $end = new DateTime($event['end_time']);
                    if ($now < $start) $status = 'upcoming';
                    elseif ($now > $end) $status = 'ended';
                    else $status = 'ongoing';
                ?>
                    <a href="detail.php?id=<?php echo $event['id']; ?>" style="text-decoration:none; color:inherit; opacity:0; animation: fadeInUp 0.6s ease forwards <?php echo 0.1 * ($index % 12) + 0.3; ?>s;">
                        <div class="event-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; overflow:hidden; border:1px solid var(--border-light); transition: all 0.25s ease; height:100%; display:flex; flex-direction:column; box-shadow:var(--shadow-sm);">
                            <?php if (!empty($event['cover'])): ?>
                                <img src="<?php echo htmlspecialchars($event['cover']); ?>" style="width:100%; height:180px; object-fit:cover;">
                            <?php else: ?>
                                <div style="height:180px; background:linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:2rem; opacity:0.6;"><i class="fas fa-calendar-alt"></i></div>
                            <?php endif; ?>
                            <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                    <h3 style="font-size:1.3rem; font-weight:700; margin:0;"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <?php if ($event['is_pinned']): ?>
                                        <span style="background:var(--mc-gold-soft); color:#1C1F18; font-size:0.7rem; padding:2px 10px; border-radius:12px; flex-shrink:0;">置顶</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($event['subtitle'])): ?>
                                    <p style="color:var(--text-secondary); font-size:0.9rem; margin:0 0 12px;"><?php echo htmlspecialchars($event['subtitle']); ?></p>
                                <?php endif; ?>
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap;">
                                    <span class="event-status <?php echo $status; ?>" style="display:inline-block; padding:3px 12px; border-radius:20px; font-size:0.8rem; font-weight:600; background:<?php echo $status==='ongoing'?'#4F8A30':($status==='upcoming'?'#D4942B':'#5E6259'); ?>; color:#fff;">
                                        <?php echo $status==='ongoing'?'进行中':($status==='upcoming'?'未开始':'已结束'); ?>
                                    </span>
                                    <span style="font-size:0.85rem; color:var(--text-secondary);">
                                        <i class="far fa-clock"></i> <?php echo date('m/d', strtotime($event['start_time'])); ?> - <?php echo date('m/d', strtotime($event['end_time'])); ?>
                                    </span>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:auto; padding-top:12px; border-top:1px solid var(--border-light);">
                                    <?php if (!empty($event['organizer_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($event['organizer_avatar']); ?>" style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                                    <?php endif; ?>
                                    <span style="font-size:0.85rem; color:var(--text-secondary);"><?php echo htmlspecialchars($event['organizer_name'] ?: '官方'); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php $index++; endwhile; ?>
            </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <div style="display:flex; justify-content:center; gap:8px; margin-top:30px; animation: fadeIn 0.5s ease 0.6s;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?status=<?php echo $statusFilter; ?>&page=<?php echo $i; ?>" style="padding:8px 16px; border-radius:20px; text-decoration:none; background:<?php echo $i===$page?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $i===$page?'#fff':'var(--text)'; ?>;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .event-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.12); border-color: var(--mc-green); }
    /* 桌面端一行3列 */
    .events-grid { grid-template-columns: repeat(3, 1fr); }
    /* 平板端一行2列 */
    @media (max-width: 1200px) and (min-width: 768px) {
        .events-grid { grid-template-columns: repeat(2, 1fr); }
        .events-sidebar { width: 160px !important; }
    }
    /* 手机端一行1列，侧栏隐藏 */
    @media (max-width: 767px) {
        .events-grid { grid-template-columns: 1fr; }
        .events-sidebar { display: none; }
        div[style*="display:flex; gap:30px"] { flex-direction: column; }
    }

    /* 动画关键帧 */
    @keyframes fadeInDown {
        from { opacity:0; transform:translateY(-20px); }
        to { opacity:1; transform:translateY(0); }
    }
    @keyframes fadeInUp {
        from { opacity:0; transform:translateY(20px); }
        to { opacity:1; transform:translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity:0; }
        to { opacity:1; }
    }
    @keyframes slideInLeft {
        from { opacity:0; transform:translateX(-30px); }
        to { opacity:1; transform:translateX(0); }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>