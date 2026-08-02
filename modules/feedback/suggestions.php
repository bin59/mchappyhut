<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '建议中心';
require_once __DIR__ . '/../../header.php';

$categoryFilter = $_GET['category'] ?? 'all';
$where = '';
if ($categoryFilter !== 'all' && in_array($categoryFilter, ['game','forum','website','other'])) {
    $where = " WHERE category = '$categoryFilter'";
}

// 分页
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

$totalStmt = $conn->query("SELECT COUNT(*) AS total FROM suggestions $where");
$total = $totalStmt->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

$suggestions = $conn->query("SELECT s.*, u.username, u.avatar, u.id AS author_id FROM suggestions s LEFT JOIN users u ON s.user_id = u.id $where ORDER BY s.created_at DESC LIMIT $perPage OFFSET $offset");
?>

<div style="max-width:1600px; margin:0 auto; padding:100px 30px 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:30px;">
        <div>
            <h1 style="font-size:2.6rem; font-weight:800;">建议中心</h1>
            <p style="color:var(--text-secondary);">提出您的想法，帮助我们改进</p>
        </div>
        <?php if (isLoggedIn()): ?>
            <a href="suggestion_create.php" class="btn-auth" style="padding:12px 28px; background:var(--mc-green); color:#fff;">提交建议</a>
        <?php endif; ?>
    </div>

    <!-- 分类筛选 -->
    <div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap;">
        <a href="?category=all" class="btn-auth" style="padding:8px 20px; background:<?php echo $categoryFilter==='all'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $categoryFilter==='all'?'#fff':'var(--text)'; ?>;">全部</a>
        <a href="?category=game" class="btn-auth" style="padding:8px 20px; background:<?php echo $categoryFilter==='game'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $categoryFilter==='game'?'#fff':'var(--text)'; ?>;">游戏</a>
        <a href="?category=forum" class="btn-auth" style="padding:8px 20px; background:<?php echo $categoryFilter==='forum'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $categoryFilter==='forum'?'#fff':'var(--text)'; ?>;">论坛</a>
        <a href="?category=website" class="btn-auth" style="padding:8px 20px; background:<?php echo $categoryFilter==='website'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $categoryFilter==='website'?'#fff':'var(--text)'; ?>;">网站</a>
        <a href="?category=other" class="btn-auth" style="padding:8px 20px; background:<?php echo $categoryFilter==='other'?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $categoryFilter==='other'?'#fff':'var(--text)'; ?>;">其他</a>
    </div>

    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:24px;">
        <?php if ($suggestions->num_rows === 0): ?>
            <div style="grid-column:1/-1; background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:80px; text-align:center; color:var(--text-secondary);">暂无建议</div>
        <?php else: ?>
            <?php while ($s = $suggestions->fetch_assoc()): ?>
                <a href="suggestion_detail.php?id=<?php echo $s['id']; ?>" style="text-decoration:none; color:inherit;">
                    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:24px; border:1px solid var(--border-light); transition: all 0.25s; height:100%; display:flex; flex-direction:column;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                            <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $s['author_id']; ?>" onclick="event.stopPropagation();">
                                <img src="<?php echo $s['avatar'] ?: 'assets/images/default-avatar.png'; ?>" style="width:44px; height:44px; border-radius:50%;">
                            </a>
                            <div>
                                <div style="font-weight:700;"><?php echo htmlspecialchars($s['username']); ?></div>
                                <div style="color:var(--text-secondary); font-size:0.8rem;"><?php echo date('Y-m-d', strtotime($s['created_at'])); ?></div>
                            </div>
                        </div>
                        <h3 style="font-size:1.3rem; font-weight:700; margin:0 0 6px;"><?php echo htmlspecialchars($s['title']); ?></h3>
                        <?php if ($s['subtitle']): ?>
                            <p style="color:var(--text-secondary); font-size:0.9rem; margin:0 0 8px;"><?php echo htmlspecialchars($s['subtitle']); ?></p>
                        <?php endif; ?>
                        <p style="color:var(--text-secondary); font-size:0.9rem; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?php echo strip_tags($s['content']); ?></p>
                        <span style="align-self:flex-end; background:var(--surface-alt); padding:2px 10px; border-radius:12px; font-size:0.8rem; margin-top:8px;">
                            <?php $catLabels = ['game'=>'游戏','forum'=>'论坛','website'=>'网站','other'=>'其他']; echo $catLabels[$s['category']] ?? '其他'; ?>
                        </span>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:30px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?category=<?php echo $categoryFilter; ?>&page=<?php echo $i; ?>" style="padding:8px 16px; border-radius:20px; text-decoration:none; background:<?php echo $i===$page?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $i===$page?'#fff':'var(--text)'; ?>;"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: repeat(3, 1fr)"] { grid-template-columns: 1fr !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>