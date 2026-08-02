<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) redirect(BASE_URL . '/modules/events/');
$pageTitle = htmlspecialchars($event['title']) . ' - 活动';
require_once __DIR__ . '/../../header.php';

$now = new DateTime();
$start = new DateTime($event['start_time']);
$end = new DateTime($event['end_time']);
if ($now < $start) $status = 'upcoming';
elseif ($now > $end) $status = 'ended';
else $status = 'ongoing';
?>

<div class="event-detail" style="animation: fadeIn 0.6s ease;">

    <!-- 顶部封面横幅（如果有cover） -->
    <?php if (!empty($event['cover'])): ?>
        <div style="position:relative; width:100%; height:45vh; max-height:500px; background: url('<?php echo htmlspecialchars($event['cover']); ?>') center/cover no-repeat; display:flex; align-items:flex-end; border-bottom:4px solid var(--mc-green);">
            <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.5), transparent 60%);"></div>
            <div style="position:relative; z-index:1; padding:0 40px 30px; width:100%;">
                <h1 style="font-size:clamp(2rem, 4vw, 2.8rem); font-weight:800; color:#fff; text-shadow:0 2px 10px rgba(0,0,0,0.5); margin:0; word-break:break-word;"><?php echo htmlspecialchars($event['title']); ?></h1>
            </div>
        </div>
    <?php endif; ?>

    <!-- 主体内容区 -->
    <div style="max-width:1600px; margin:0 auto; padding:30px 20px 60px; display:flex; gap:30px; align-items:flex-start;">
        
        <!-- 左侧：活动信息卡片 -->
        <div class="event-sidebar" style="flex-shrink:0; width:300px; position:sticky; top:100px;">
            <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:20px; overflow:hidden; box-shadow:var(--shadow-lg); border:1px solid var(--border-light);">
                <!-- 举办者信息 -->
                <div style="padding:24px 20px 16px; text-align:center;">
                    <?php if (!empty($event['organizer_avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($event['organizer_avatar']); ?>" style="width:70px; height:70px; border-radius:50%; object-fit:cover; border:3px solid var(--surface); box-shadow:var(--shadow-md); margin-bottom:10px;">
                    <?php else: ?>
                        <div style="width:70px; height:70px; border-radius:50%; background:var(--surface-alt); display:inline-flex; align-items:center; justify-content:center; color:var(--text-tertiary); margin-bottom:10px;"><i class="fas fa-calendar-alt"></i></div>
                    <?php endif; ?>
                    <h3 style="font-size:1.1rem; font-weight:700; margin:0 0 4px;"><?php echo htmlspecialchars($event['organizer_name'] ?: '官方'); ?></h3>
                    <span class="event-status <?php echo $status; ?>" style="display:inline-block; padding:3px 14px; border-radius:20px; font-size:0.8rem; font-weight:600; color:#fff; background:<?php echo $status==='ongoing'?'#4F8A30':($status==='upcoming'?'#D4942B':'#5E6259'); ?>;">
                        <?php echo $status==='ongoing'?'进行中':($status==='upcoming'?'未开始':'已结束'); ?>
                    </span>
                </div>
                
                <div style="padding:0 20px 16px;">
                    <div style="font-size:0.9rem; color:var(--text-secondary); display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                        <i class="far fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($event['start_time'])); ?>
                    </div>
                    <div style="font-size:0.9rem; color:var(--text-secondary); display:flex; align-items:center; gap:8px;">
                        <i class="far fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($event['end_time'])); ?>
                    </div>
                </div>

                <?php if (isAdmin()): ?>
                    <div style="padding:16px 20px; border-top:1px solid var(--border-light); display:flex; gap:8px; justify-content:center;">
                        <a href="edit.php?id=<?php echo $event['id']; ?>" class="btn-auth" style="padding:6px 14px; font-size:0.8rem; text-decoration:none;">编辑</a>
                        <a href="delete.php?id=<?php echo $event['id']; ?>" class="btn-auth" style="background:#e74c3c; padding:6px 14px; font-size:0.8rem; text-decoration:none;" onclick="return confirm('确定删除？');">删除</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 右侧：活动正文 -->
        <div style="flex:1; min-width:0;">
            <?php if (!$event['cover']): ?>
                <h1 style="font-size:2.2rem; font-weight:800; margin:0 0 16px; word-break:break-word;"><?php echo htmlspecialchars($event['title']); ?></h1>
            <?php endif; ?>
            <?php if ($event['subtitle']): ?>
                <p style="color:var(--text-secondary); font-size:1.1rem; margin-bottom:20px;"><?php echo htmlspecialchars($event['subtitle']); ?></p>
            <?php endif; ?>

            <!-- 正文内容卡片（限制图片宽度） -->
            <div class="event-content" style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:30px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); line-height:1.9; word-break:break-word;">
                <?php echo $event['content']; ?>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .event-content img { max-width: 100% !important; height: auto !important; border-radius: 8px; margin: 12px 0; }
    @media (max-width: 768px) {
        .event-sidebar { width: 100% !important; position: static !important; margin-bottom: 20px; }
        [style*="display:flex; gap:30px"] { flex-direction: column; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>