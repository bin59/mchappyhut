<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '玩家日志';
require_once __DIR__ . '/../../header.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// 标签筛选
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$serverId = isset($_GET['server']) ? intval($_GET['server']) : 0;
$search = isset($_GET['s']) ? trim($_GET['s']) : '';

// 获取所有已有标签（用于筛选栏）
$tagResult = $conn->query("SELECT DISTINCT tag FROM player_logs WHERE tag IS NOT NULL AND tag != '' ORDER BY tag");
$allTags = [];
while ($t = $tagResult->fetch_assoc()) $allTags[] = $t['tag'];

// 获取服务器列表
$serverResult = $conn->query("SELECT id, name FROM servers ORDER BY name");

// 构建查询条件
$whereArr = [];
$params = [];
$types = '';

if ($tag) {
    $whereArr[] = 'pl.tag = ?';
    $params[] = $tag;
    $types .= 's';
}
if ($serverId > 0) {
    $whereArr[] = 'pl.server_id = ?';
    $params[] = $serverId;
    $types .= 'i';
}
if ($search) {
    $whereArr[] = '(pl.title LIKE ? OR pl.content LIKE ?)';
    $kw = '%' . $search . '%';
    $params[] = $kw;
    $params[] = $kw;
    $types .= 'ss';
}
$where = $whereArr ? 'WHERE ' . implode(' AND ', $whereArr) : '';

// 统计总数
$countSql = "SELECT COUNT(*) AS total FROM player_logs pl $where";
if ($whereArr) {
    $countStmt = $conn->prepare($countSql);
    if ($types) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($countSql)->fetch_assoc()['total'];
}
$totalPages = ceil($total / $perPage);

// 查询日志（置顶优先，按创建时间倒序）
$dataSql = "SELECT pl.*, u.username, u.avatar, s.name AS server_name 
    FROM player_logs pl 
    JOIN users u ON pl.user_id = u.id 
    LEFT JOIN servers s ON pl.server_id = s.id 
    $where 
    ORDER BY pl.is_pinned DESC, pl.created_at DESC 
    LIMIT ? OFFSET ?";

// 重新准备参数（加上分页参数）
$allParams = $params;
$allParams[] = $perPage;
$allParams[] = $offset;
$allTypes = $types . 'ii';

$stmt = $conn->prepare($dataSql);
if ($allTypes) {
    $stmt->bind_param($allTypes, ...$allParams);
}
$stmt->execute();
$logs = $stmt->get_result();

// 查询参数用于分页链接
$queryParams = [];
if ($tag) $queryParams[] = 'tag=' . urlencode($tag);
if ($serverId) $queryParams[] = 'server=' . $serverId;
if ($search) $queryParams[] = 's=' . urlencode($search);
$queryStr = $queryParams ? '&' . implode('&', $queryParams) : '';
?>

<div style="max-width:1800px; margin:0 auto; padding:100px 20px 40px; animation: fadeIn 0.6s ease;">
    <!-- 头部 -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="font-size:2.8rem; font-weight:800; margin:0;">📝 玩家日志</h1>
            <p style="color:var(--text-secondary); margin:4px 0 0;">记录你在方块世界的每一个精彩瞬间</p>
        </div>
        <?php if (isLoggedIn() && canPostInCommunity()): ?>
            <a href="edit.php" class="btn-auth" style="text-decoration:none; padding:10px 24px; background:var(--mc-green); color:#fff; justify-content:center;">
                <i class="fas fa-pen"></i>&nbsp; 写日志
            </a>
        <?php endif; ?>
    </div>

    <!-- 筛选栏 -->
    <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:28px; align-items:center;">
        <form method="GET" style="display:flex; gap:8px; flex:1; min-width:200px;">
            <input type="text" name="s" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索日志标题或内容..." 
                style="flex:1; padding:8px 16px; border:1px solid var(--border); border-radius:24px; background:var(--bg); color:var(--text); font-size:0.9rem; max-width:300px;">
            <?php if ($tag): ?><input type="hidden" name="tag" value="<?php echo htmlspecialchars($tag); ?>"><?php endif; ?>
            <?php if ($serverId): ?><input type="hidden" name="server" value="<?php echo $serverId; ?>"><?php endif; ?>
            <button type="submit" style="background:var(--mc-green); color:#fff; border:none; border-radius:24px; padding:8px 16px; cursor:pointer; font-size:0.9rem;">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <!-- 标签筛选 -->
        <div style="display:flex; flex-wrap:wrap; gap:6px;">
            <a href="?<?php echo $queryStr ? str_replace('tag=' . urlencode($tag), '', $queryStr . '&') . 's=' . urlencode($search) : ($search ? 's=' . urlencode($search) : ''); ?>" 
                style="padding:6px 14px; border-radius:20px; text-decoration:none; font-size:0.85rem; 
                background:<?php echo !$tag ? 'var(--mc-green)' : 'var(--surface-alt)'; ?>; 
                color:<?php echo !$tag ? '#fff' : 'var(--text)'; ?>;">
                全部
            </a>
            <?php foreach ($allTags as $t): ?>
                <a href="?tag=<?php echo urlencode($t) . ($search ? '&s=' . urlencode($search) : '') . ($serverId ? '&server=' . $serverId : ''); ?>" 
                    style="padding:6px 14px; border-radius:20px; text-decoration:none; font-size:0.85rem; 
                    background:<?php echo ($tag === $t) ? 'var(--mc-green)' : 'var(--surface-alt)'; ?>; 
                    color:<?php echo ($tag === $t) ? '#fff' : 'var(--text)'; ?>;">
                    <?php echo htmlspecialchars($t); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 服务器筛选 -->
    <div style="margin-bottom:24px; display:flex; align-items:center; gap:8px;">
        <span style="font-size:0.85rem; color:var(--text-secondary);">服务器：</span>
        <a href="?<?php echo $tag ? 'tag=' . urlencode($tag) . '&' : ''; ?><?php echo $search ? 's=' . urlencode($search) : ''; ?>" 
            style="padding:4px 12px; border-radius:16px; text-decoration:none; font-size:0.8rem;
            background:<?php echo !$serverId ? 'var(--mc-green)' : 'var(--surface-alt)'; ?>;
            color:<?php echo !$serverId ? '#fff' : 'var(--text)'; ?>;">
            全部
        </a>
        <?php 
        $serverResult->data_seek(0);
        while ($srv = $serverResult->fetch_assoc()): 
        ?>
            <a href="?server=<?php echo $srv['id'] . ($tag ? '&tag=' . urlencode($tag) : '') . ($search ? '&s=' . urlencode($search) : ''); ?>" 
                style="padding:4px 12px; border-radius:16px; text-decoration:none; font-size:0.8rem;
                background:<?php echo ($serverId == $srv['id']) ? 'var(--mc-green)' : 'var(--surface-alt)'; ?>;
                color:<?php echo ($serverId == $srv['id']) ? '#fff' : 'var(--text)'; ?>;">
                <?php echo htmlspecialchars($srv['name']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- 日志卡片网格 -->
    <?php if ($logs->num_rows === 0): ?>
        <div style="text-align:center; padding:80px; color:var(--text-secondary);">
            <i class="fas fa-book-open" style="font-size:3rem; opacity:0.3; display:block; margin-bottom:16px;"></i>
            <p style="font-size:1.1rem;">暂无玩家日志</p>
            <?php if (isLoggedIn() && canPostInCommunity()): ?>
                <a href="edit.php" class="btn-auth" style="margin-top:16px; justify-content:center; display:inline-flex;">写下第一篇日志</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap:20px;">
            <?php while ($log = $logs->fetch_assoc()): ?>
                <a href="detail.php?id=<?php echo $log['id']; ?>" style="text-decoration:none; color:inherit;">
                    <div class="log-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:16px; overflow:hidden; transition: all 0.25s ease; box-shadow:var(--shadow-sm); height:100%; display:flex; flex-direction:column;">
                        <!-- 封面图 -->
                        <?php if ($log['cover']): ?>
                            <div style="height:180px; overflow:hidden; position:relative;">
                                <img src="<?php echo htmlspecialchars($log['cover']); ?>" 
                                    style="width:100%; height:100%; object-fit:cover; transition: transform 0.3s ease;" 
                                    onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22180%22><rect fill=%22%23333%22 width=%22400%22 height=%22180%22/><text x=%22200%22 y=%2290%22 text-anchor=%22middle%22 fill=%22%23666%22 font-size=%2216%22>暂无图片</text></svg>'">
                                <?php if ($log['is_pinned']): ?>
                                    <span style="position:absolute; top:10px; right:10px; background:var(--mc-green); color:#fff; font-size:0.75rem; padding:3px 10px; border-radius:12px;">置顶</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 内容区 -->
                        <div style="padding:18px 20px; flex:1; display:flex; flex-direction:column;">
                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px;">
                                <?php if ($log['tag']): ?>
                                    <span style="font-size:0.75rem; padding:2px 10px; border-radius:12px; background:rgba(79,138,48,0.12); color:var(--mc-green);"><?php echo htmlspecialchars($log['tag']); ?></span>
                                <?php endif; ?>
                                <?php if ($log['server_name']): ?>
                                    <span style="font-size:0.75rem; padding:2px 10px; border-radius:12px; background:rgba(52,152,219,0.1); color:#3498db;">🖥️ <?php echo htmlspecialchars($log['server_name']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <h3 style="font-size:1.2rem; font-weight:700; margin:0 0 6px; line-height:1.4;"><?php echo htmlspecialchars($log['title']); ?></h3>
                            
                            <p style="color:var(--text-secondary); font-size:0.9rem; margin:0 0 12px; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                <?php echo strip_tags($log['content']); ?>
                            </p>
                            
                            <!-- 底部信息 -->
                            <div style="display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid var(--border-light);">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <img src="<?php echo $log['avatar']; ?>" style="width:26px; height:26px; border-radius:50%; object-fit:cover;">
                                    <span style="font-size:0.85rem; color:var(--text-secondary);"><?php echo htmlspecialchars($log['username']); ?></span>
                                </div>
                                <span style="font-size:0.8rem; color:var(--text-tertiary);">
                                    <?php echo $log['game_time'] ? date('m-d H:i', strtotime($log['game_time'])) : date('m-d', strtotime($log['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:40px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i . $queryStr; ?>" 
                style="padding:8px 16px; border-radius:20px; text-decoration:none; 
                background:<?php echo $i === $page ? 'var(--mc-green)' : 'var(--surface-alt)'; ?>; 
                color:<?php echo $i === $page ? '#fff' : 'var(--text)'; ?>;">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
    .log-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); border-color: var(--mc-green); }
    .log-card:hover img { transform: scale(1.05); }
    @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    @media (max-width: 768px) {
        [style*="grid-template-columns: repeat(auto-fill, minmax(380px, 1fr))"] { grid-template-columns: 1fr !important; }
        .log-card img { height: 140px !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>
