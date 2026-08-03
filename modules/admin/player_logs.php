<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$pageTitle = '玩家日志管理 - 管理控制台';
require_once __DIR__ . '/../../header.php';

// 搜索和筛选
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$serverId = isset($_GET['server']) ? intval($_GET['server']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// 获取所有标签
$tagResult = $conn->query("SELECT DISTINCT tag FROM player_logs WHERE tag IS NOT NULL AND tag != '' ORDER BY tag");
$allTags = [];
while ($t = $tagResult->fetch_assoc()) $allTags[] = $t['tag'];

// 获取服务器列表
$serverResult = $conn->query("SELECT id, name FROM servers ORDER BY name");

// 构建查询条件
$whereArr = [];
$params = [];
$types = '';
if ($search) {
    $whereArr[] = '(pl.title LIKE ? OR pl.content LIKE ? OR u.username LIKE ? OR pl.tag LIKE ?)';
    $kw = '%' . $search . '%';
    $params = array_merge($params, [$kw, $kw, $kw, $kw]);
    $types .= 'ssss';
}
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
$where = $whereArr ? 'WHERE ' . implode(' AND ', $whereArr) : '';

// 统计
$countSql = "SELECT COUNT(*) AS total FROM player_logs pl JOIN users u ON pl.user_id = u.id $where";
if ($whereArr) {
    $countStmt = $conn->prepare($countSql);
    if ($types) $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($countSql)->fetch_assoc()['total'];
}
$totalPages = ceil($total / $perPage);

// 查询数据
$allParams = $params;
$allParams[] = $perPage;
$allParams[] = $offset;
$allTypes = $types . 'ii';

$sql = "SELECT pl.*, u.username, u.avatar, s.name AS server_name 
    FROM player_logs pl 
    JOIN users u ON pl.user_id = u.id 
    LEFT JOIN servers s ON pl.server_id = s.id 
    $where 
    ORDER BY pl.is_pinned DESC, pl.created_at DESC 
    LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($allTypes) $stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$logs = $stmt->get_result();

// 统计数据
$totalLogs = $conn->query("SELECT COUNT(*) AS cnt FROM player_logs")->fetch_assoc()['cnt'];
$pinnedLogs = $conn->query("SELECT COUNT(*) AS cnt FROM player_logs WHERE is_pinned = 1")->fetch_assoc()['cnt'];
$tagCount = $conn->query("SELECT COUNT(DISTINCT tag) AS cnt FROM player_logs WHERE tag IS NOT NULL AND tag != ''")->fetch_assoc()['cnt'];

$queryStr = '';
if ($search) $queryStr .= '&s=' . urlencode($search);
if ($tag) $queryStr .= '&tag=' . urlencode($tag);
if ($serverId) $queryStr .= '&server=' . $serverId;
?>

<div style="max-width:1600px; margin:0 auto; padding:100px 24px 40px; animation: fadeIn 0.5s ease;">

    <!-- 头部 -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="font-size:2rem; font-weight:800; margin:0;">📝 玩家日志管理</h1>
            <p style="color:var(--text-secondary); margin:4px 0 0;">共有 <?php echo $totalLogs; ?> 篇日志，<?php echo $tagCount; ?> 个分类标签</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="<?php echo BASE_URL; ?>/modules/player_logs/index.php" class="btn-auth" style="text-decoration:none; padding:10px 20px; background:var(--mc-green); color:#fff;">
                前台查看
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/player_logs/edit.php" class="btn-auth" style="text-decoration:none; padding:10px 20px;">
                添加日志
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/admin/dashboard.php" class="btn-auth" style="text-decoration:none; padding:10px 20px;">
                返回仪表盘
            </a>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:24px;">
        <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:14px; padding:16px 20px; border:1px solid var(--border-light);">
            <div style="font-size:0.85rem; color:var(--text-secondary);">总日志数</div>
            <div style="font-size:1.8rem; font-weight:800; color:var(--text);"><?php echo $totalLogs; ?></div>
        </div>
        <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:14px; padding:16px 20px; border:1px solid var(--border-light);">
            <div style="font-size:0.85rem; color:var(--text-secondary);">置顶日志</div>
            <div style="font-size:1.8rem; font-weight:800; color:#e74c3c;"><?php echo $pinnedLogs; ?></div>
        </div>
        <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:14px; padding:16px 20px; border:1px solid var(--border-light);">
            <div style="font-size:0.85rem; color:var(--text-secondary);">分类标签</div>
            <div style="font-size:1.8rem; font-weight:800; color:var(--mc-green);"><?php echo $tagCount; ?></div>
        </div>
        <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:14px; padding:16px 20px; border:1px solid var(--border-light);">
            <div style="font-size:0.85rem; color:var(--text-secondary);">当前页</div>
            <div style="font-size:1.8rem; font-weight:800; color:var(--text);"><?php echo $total > 0 ? $page . '/' . $totalPages : '--'; ?></div>
        </div>
    </div>

    <!-- 筛选栏 -->
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:14px; padding:16px 20px; margin-bottom:20px;">
        <form method="GET" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
            <input type="text" name="s" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索标题、内容、作者..." 
                style="flex:1; min-width:200px; padding:8px 14px; border:1px solid var(--border); border-radius:20px; background:var(--bg); color:var(--text); font-size:0.9rem;">
            
            <select name="tag" style="padding:8px 14px; border:1px solid var(--border); border-radius:20px; background:var(--bg); color:var(--text); font-size:0.9rem;">
                <option value="">全部标签</option>
                <?php foreach ($allTags as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $tag === $t ? 'selected' : ''; ?>><?php echo htmlspecialchars($t); ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="server" style="padding:8px 14px; border:1px solid var(--border); border-radius:20px; background:var(--bg); color:var(--text); font-size:0.9rem;">
                <option value="">全部服务器</option>
                <?php 
                $serverResult->data_seek(0);
                while ($srv = $serverResult->fetch_assoc()): 
                ?>
                    <option value="<?php echo $srv['id']; ?>" <?php echo $serverId == $srv['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($srv['name']); ?></option>
                <?php endwhile; ?>
            </select>
            
            <button type="submit" style="background:var(--mc-green); color:#fff; border:none; border-radius:20px; padding:8px 18px; cursor:pointer; font-size:0.9rem;">
                <i class="fas fa-search"></i> 搜索
            </button>
            <?php if ($search || $tag || $serverId): ?>
                <a href="player_logs.php" style="color:var(--text-secondary); text-decoration:none; font-size:0.9rem; padding:8px;">清除筛选</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- 数据表格 -->
    <?php if ($logs->num_rows === 0): ?>
        <div style="text-align:center; padding:60px; background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:14px; border:1px solid var(--border-light); color:var(--text-secondary);">
            <i class="fas fa-book-open" style="font-size:3rem; opacity:0.3; display:block; margin-bottom:12px;"></i>
            暂无日志数据
        </div>
    <?php else: ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:14px; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:900px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--border-light);">
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">ID</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">封面</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">标题</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">标签</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">作者</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">服务器</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">游戏时间</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">置顶</th>
                        <th style="padding:12px 14px; text-align:left; font-size:0.85rem; color:var(--text-secondary);">创建时间</th>
                        <th style="padding:12px 14px; text-align:center; font-size:0.85rem; color:var(--text-secondary);">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr style="border-bottom:1px solid var(--border-light); transition: background 0.15s;" onmouseover="this.style.background='var(--surface-alt)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 14px; font-size:0.9rem; color:var(--text-secondary);">#<?php echo $log['id']; ?></td>
                        <td style="padding:8px 14px;">
                            <?php if ($log['cover']): ?>
                                <img src="<?php echo htmlspecialchars($log['cover']); ?>" style="width:60px; height:40px; object-fit:cover; border-radius:6px;" onerror="this.style.display='none'">
                            <?php else: ?>
                                <span style="color:var(--text-tertiary); font-size:0.8rem;">--</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 14px;">
                            <a href="<?php echo BASE_URL; ?>/modules/player_logs/detail.php?id=<?php echo $log['id']; ?>" target="_blank" style="color:var(--text); text-decoration:none; font-weight:600; font-size:0.95rem;">
                                <?php echo htmlspecialchars(mb_strlen($log['title']) > 30 ? mb_substr($log['title'], 0, 30) . '...' : $log['title']); ?>
                            </a>
                        </td>
                        <td style="padding:12px 14px;">
                            <?php if ($log['tag']): ?>
                                <span style="font-size:0.8rem; padding:2px 10px; border-radius:12px; background:rgba(79,138,48,0.12); color:var(--mc-green); white-space:nowrap;">
                                    <?php echo htmlspecialchars($log['tag']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color:var(--text-tertiary); font-size:0.8rem;">--</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 14px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <img src="<?php echo $log['avatar']; ?>" style="width:22px; height:22px; border-radius:50%;">
                                <span style="font-size:0.9rem;"><?php echo htmlspecialchars($log['username']); ?></span>
                            </div>
                        </td>
                        <td style="padding:12px 14px; font-size:0.9rem; color:var(--text-secondary);">
                            <?php echo $log['server_name'] ? '🖥️ ' . htmlspecialchars($log['server_name']) : '--'; ?>
                        </td>
                        <td style="padding:12px 14px; font-size:0.85rem; color:var(--text-secondary); white-space:nowrap;">
                            <?php echo $log['game_time'] ? date('m-d H:i', strtotime($log['game_time'])) : '--'; ?>
                        </td>
                        <td style="padding:12px 14px;">
                            <?php if ($log['is_pinned']): ?>
                                <span style="color:#e74c3c; font-size:1rem;">📌</span>
                            <?php else: ?>
                                <span style="color:var(--text-tertiary);">--</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 14px; font-size:0.85rem; color:var(--text-secondary); white-space:nowrap;">
                            <?php echo date('m-d H:i', strtotime($log['created_at'])); ?>
                        </td>
                        <td style="padding:12px 14px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                <a href="<?php echo BASE_URL; ?>/modules/player_logs/edit.php?id=<?php echo $log['id']; ?>" class="btn-auth" style="text-decoration:none; padding:5px 10px; font-size:0.8rem; background:rgba(52,152,219,0.1); color:#3498db; border:1px solid rgba(52,152,219,0.2);">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/modules/player_logs/delete.php?id=<?php echo $log['id']; ?>" class="btn-auth" style="text-decoration:none; padding:5px 10px; font-size:0.8rem; background:rgba(231,76,60,0.1); color:#e74c3c; border:1px solid rgba(231,76,60,0.2);" onclick="return confirm('确定删除 #<?php echo $log['id']; ?> <?php echo htmlspecialchars(addslashes(mb_substr($log['title'], 0, 20))); ?>？');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:28px;">
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
    .dash-card { transition: all 0.25s ease; }
    .dash-card:hover { transform: translateY(-2px); border-color: var(--mc-green); }
    @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    @media (max-width: 768px) {
        [style*="grid-template-columns: repeat(4, 1fr)"] { grid-template-columns: repeat(2, 1fr) !important; }
        table { font-size: 0.8rem; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>
