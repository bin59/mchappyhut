<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '事件时间轴';
require_once __DIR__ . '/../../header.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalStmt = $conn->query("SELECT COUNT(*) AS total FROM timeline_events");
$total = $totalStmt->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

$stmt = $conn->prepare("SELECT te.*, u.username, u.avatar, s.name AS server_name FROM timeline_events te JOIN users u ON te.user_id = u.id LEFT JOIN servers s ON te.server_id = s.id ORDER BY te.event_time DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$events = $stmt->get_result();
?>

<div style="max-width:1800px; margin:0 auto; padding:100px 20px 40px; animation: fadeIn 0.6s ease;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:16px;">
        <h1 style="font-size:2.8rem; font-weight:800;">📅 事件时间轴</h1>
        <!-- 缩放控制 -->
        <div style="display:flex; align-items:center; gap:12px; background:var(--surface-glass); backdrop-filter:blur(12px); padding:8px 16px; border-radius:20px;">
            <button id="zoomOutBtn" style="background:var(--surface-alt); border:none; color:var(--text); width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:1.2rem;">-</button>
            <span id="zoomLevel" style="font-size:0.9rem; font-weight:600;">100%</span>
            <button id="zoomInBtn" style="background:var(--surface-alt); border:none; color:var(--text); width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:1.2rem;">+</button>
        </div>
    </div>

    <!-- 时间轴容器（可缩放） -->
    <div id="timelineContainer" style="transform-origin: top center; transition: transform 0.2s ease;">
        <?php if ($events->num_rows === 0): ?>
            <div style="text-align:center; padding:80px; color:var(--text-secondary);">暂无事件记录</div>
        <?php else: ?>
            <div style="position:relative; padding-left:60px;">
                <!-- 中心竖线 -->
                <div style="position:absolute; left:30px; top:0; bottom:0; width:3px; background:var(--mc-green); opacity:0.4;"></div>
                
                <?php while ($event = $events->fetch_assoc()): ?>
                    <div class="timeline-item" style="position:relative; margin-bottom:40px; padding-left:40px;">
                        <!-- 时间点圆点 -->
                        <div style="position:absolute; left:-11px; top:8px; width:20px; height:20px; border-radius:50%; background:var(--mc-green); border:4px solid var(--bg); box-shadow:0 0 0 3px var(--mc-green); z-index:2;"></div>
                        
                        <!-- 时间标签 (置顶左侧上方) -->
                        <div style="margin-bottom:12px;">
                            <span style="font-size:0.9rem; color:var(--mc-green); font-weight:700; background:var(--surface-glass); backdrop-filter:blur(8px); padding:4px 14px; border-radius:20px; display:inline-block;">
                                <?php echo date('Y年m月d日 H:i', strtotime($event['event_time'])); ?>
                            </span>
                        </div>
                        
                        <!-- 事件卡片 -->
                        <a href="detail.php?id=<?php echo $event['id']; ?>" style="text-decoration:none; color:inherit;">
                            <div class="event-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:16px; padding:24px; transition: all 0.25s ease; box-shadow:var(--shadow-sm);">
                                <div style="display:flex; gap:20px; flex-wrap:wrap;">
                                    <?php if ($event['cover']): ?>
                                        <img src="<?php echo htmlspecialchars($event['cover']); ?>" style="width:120px; height:80px; object-fit:cover; border-radius:10px; flex-shrink:0;">
                                    <?php endif; ?>
                                    <div style="flex:1; min-width:0;">
                                        <h3 style="font-size:1.3rem; font-weight:700; margin:0 0 6px;"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <?php if ($event['subtitle']): ?>
                                            <p style="color:var(--text-secondary); font-size:0.9rem; margin:0 0 8px;"><?php echo htmlspecialchars($event['subtitle']); ?></p>
                                        <?php endif; ?>
                                        <p style="color:var(--text-secondary); font-size:0.9rem; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?php echo strip_tags($event['content']); ?></p>
                                        <div style="display:flex; align-items:center; gap:12px; margin-top:10px; font-size:0.85rem; color:var(--text-tertiary);">
                                            <img src="<?php echo $event['avatar']; ?>" style="width:22px; height:22px; border-radius:50%;">
                                            <span><?php echo htmlspecialchars($event['username']); ?></span>
                                            <?php if ($event['server_name']): ?>
                                                <span>·</span>
                                                <span>🖥️ <?php echo htmlspecialchars($event['server_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:40px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" style="padding:8px 16px; border-radius:20px; text-decoration:none; background:<?php echo $i===$page?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $i===$page?'#fff':'var(--text)'; ?>;"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
let scale = 1;
const container = document.getElementById('timelineContainer');
const zoomLevelEl = document.getElementById('zoomLevel');

document.getElementById('zoomInBtn').addEventListener('click', () => {
    if (scale < 1.5) {
        scale += 0.1;
        container.style.transform = `scale(${scale})`;
        zoomLevelEl.textContent = Math.round(scale * 100) + '%';
    }
});

document.getElementById('zoomOutBtn').addEventListener('click', () => {
    if (scale > 0.5) {
        scale -= 0.1;
        container.style.transform = `scale(${scale})`;
        zoomLevelEl.textContent = Math.round(scale * 100) + '%';
    }
});
</script>

<style>
    .event-card:hover { transform: translateX(6px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); border-color: var(--mc-green); }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    @media (max-width: 768px) {
        .timeline-item { padding-left: 30px; }
        .event-card { padding: 16px; }
        .event-card img { width: 100%; height: auto; max-height: 150px; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>