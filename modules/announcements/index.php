<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '公告中心';
require_once __DIR__ . '/../../header.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 8;
$offset = ($page - 1) * $perPage;

$totalStmt = $conn->query("SELECT COUNT(*) AS total FROM announcements");
$total = $totalStmt->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

$stmt = $conn->prepare("SELECT a.*, u.username, u.avatar, u.id AS author_id FROM announcements a JOIN users u ON a.user_id = u.id ORDER BY a.is_pinned DESC, a.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$announcements = $stmt->get_result();
?>

<!-- 横幅 -->
<div class="announce-banner" style="position:relative; width:100%; min-height:380px; display:flex; align-items:center; padding:0 40px; margin-bottom:32px; background: linear-gradient(to bottom, rgba(10,14,8,0.35), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/assets/images/ag1.png') center/cover no-repeat; border-radius: 0 0 20px 20px; animation: fadeInDown 0.8s ease;">
    <div style="position:relative; z-index:1; max-width:1400px; margin:0 auto; width:100%;">
        <h1 style="font-size:clamp(2.5rem, 5vw, 3.5rem); font-weight:800; color:#fff; margin-bottom:12px; animation: fadeInUp 0.6s ease 0.2s both;">官方公告中心</h1>
        <p style="color:rgba(255,255,255,0.85); font-size:1.15rem; animation: fadeInUp 0.6s ease 0.3s both;">掌握服务器第一手资讯与更新动态</p>
    </div>
</div>

<div style="max-width:1300px; margin:0 auto; padding:0 20px 40px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 style="font-size:1.8rem;">全部公告</h2>
        <?php if (isAdmin()): ?>
            <a href="edit.php" class="btn-auth" style="text-decoration:none;"><i class="fas fa-plus"></i> 发布公告</a>
        <?php endif; ?>
    </div>

    <div class="announce-list" style="display:grid; grid-template-columns: repeat(2, 1fr); gap:20px;">
        <?php if ($announcements->num_rows === 0): ?>
            <div style="grid-column:1/-1; background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:60px; text-align:center; color:var(--text-secondary);">暂无公告</div>
        <?php else: ?>
            <?php $index = 0; while ($row = $announcements->fetch_assoc()): ?>
                <a href="detail.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit; animation: fadeInUp 0.5s ease <?php echo 0.1 * ($index % 8); ?>s both; display: flex; min-width: 0;">
                    <div class="announce-card" style="
                        background:var(--surface-glass); backdrop-filter:blur(14px);
                        border:1px solid var(--border-light); border-radius:16px;
                        padding:24px; width:100%; min-width: 0;
                        display:flex; align-items:flex-start; gap:16px;
                        transition: transform 0.2s, box-shadow 0.2s;
                        height: 160px;
                        overflow: hidden;
                    ">
                        <!-- 封面图 -->
                        <div class="card-img" style="flex-shrink:0; width:80px; height:80px; border-radius:12px; overflow:hidden; background:var(--surface-alt);">
                            <?php if ($row['cover']): ?>
                                <img src="<?php echo htmlspecialchars($row['cover']); ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--text-tertiary);"><i class="fas fa-bullhorn" style="font-size:1.8rem;"></i></div>
                            <?php endif; ?>
                        </div>

                        <!-- 信息区 -->
                        <div class="card-body" style="flex:1; min-width: 0; display:flex; flex-direction:column; justify-content:space-between; height:100%; overflow: hidden;">
                            <div style="min-width: 0;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap; min-width: 0;">
                                    <h3 style="font-size:1.25rem; font-weight:700; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width: 0;"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <?php if ($row['tag']): ?>
                                        <span style="background:var(--mc-green); color:#fff; font-size:0.7rem; padding:2px 10px; border-radius:12px; white-space:nowrap;"><?php echo htmlspecialchars($row['tag']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($row['is_pinned']): ?>
                                        <span style="background:var(--mc-gold-soft); color:#1C1F18; font-size:0.7rem; padding:2px 10px; border-radius:12px;">置顶</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($row['subtitle']): ?>
                                    <p style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width: 0;"><?php echo htmlspecialchars($row['subtitle']); ?></p>
                                <?php endif; ?>
                            </div>
                            <!-- 作者与日期 -->
                            <div class="card-meta" style="display:flex; align-items:center; gap:8px; font-size:0.75rem; color:var(--text-tertiary); margin-top:auto;">
                                <img src="<?php echo $row['avatar']; ?>" style="width:20px; height:20px; border-radius:50%; object-fit:cover;">
                                <span style="white-space:nowrap;"><?php echo htmlspecialchars($row['username']); ?></span>
                                <span>·</span>
                                <span style="white-space:nowrap;"><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php $index++; endwhile; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:40px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" style="padding:8px 18px; border-radius:20px; text-decoration:none; background: <?php echo $i === $page ? 'var(--mc-green)' : 'var(--surface-alt)'; ?>; color: <?php echo $i === $page ? '#fff' : 'var(--text)'; ?>;"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
    /* 卡片悬浮效果 */
    .announce-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }

    /* 手机端适配 */
    @media (max-width: 768px) {
        .announce-list { grid-template-columns: 1fr !important; }
        .announce-card {
            height: auto !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 12px !important;
        }
        .card-img {
            width: 100% !important;
            height: 150px !important;
        }
        .card-body {
            width: 100%;
        }
        .card-body h3 {
            font-size: 1.1rem !important;
            white-space: normal !important;  /* 手机端可换行 */
        }
        .card-meta {
            margin-top: 10px;
            width: 100%;
            justify-content: space-between;
        }
    }

    /* 动画 */
    @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeInDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
</style>

<?php require_once __DIR__ . '/../../footer.php'; ?>