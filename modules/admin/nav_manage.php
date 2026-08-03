<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

// ===== 自动建表 + 首次填充默认导航 =====
$tableCheck = $conn->query("SHOW TABLES LIKE 'nav_items'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    // 建表
    $conn->query("CREATE TABLE IF NOT EXISTS nav_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(50) NOT NULL,
        url VARCHAR(500) NOT NULL DEFAULT '',
        type ENUM('link','dropdown') NOT NULL DEFAULT 'link',
        parent_id INT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_visible TINYINT(1) NOT NULL DEFAULT 1,
        target VARCHAR(10) NOT NULL DEFAULT '_self',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_parent_sort (parent_id, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

$countCheck = $conn->query("SELECT COUNT(*) AS cnt FROM nav_items");
$rowCount = $countCheck ? $countCheck->fetch_assoc()['cnt'] : 0;

if ($rowCount == 0) {
    $stmt = $conn->prepare("INSERT INTO nav_items (title, url, type, parent_id, sort_order, is_visible, target) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // 一级导航（与 header.php 硬编码兜底保持一致）
    $topItems = [
        ['首页',   '/index.php',                              'link',     1, '_self'],
        ['公告',   '/modules/announcements/index.php',        'link',     2, '_self'],
        ['规则',   '/modules/rules/index.php',                'link',     3, '_self'],
        ['列表',   '/modules/servers/index.php',              'link',     4, '_self'],
        ['社区',   '/modules/community/index.php',            'link',     5, '_self'],
        ['活动',   '/modules/events/index.php',               'link',     6, '_self'],
        ['文档',   'https://docs.qq.com/sheet/DWk5DandtVGdzQ1du','link',  7, '_blank'],
        ['赞助',   '/modules/sponsor/index.php',              'link',     8, '_self'],
        ['更多',   '',                                       'dropdown',  9, '_self'],
        ['关于我们','/modules/about/index.php',               'link',    10, '_self'],
    ];
    
    foreach ($topItems as $item) {
        $null = NULL;
        $stmt->bind_param("sssiisi", $item[0], $item[1], $item[2], $null, $item[3], $visOne, $item[4]);
        $visOne = 1;
        $stmt->execute();
    }
    
    // 获取"更多"的 ID
    $moreResult = $conn->query("SELECT id FROM nav_items WHERE title='更多' AND type='dropdown' LIMIT 1");
    $moreId = $moreResult->fetch_assoc()['id'];
    
    // "更多"下拉子项
    $subItems = [
        ['反馈',     '/modules/feedback/index.php',     1, '_self'],
        ['玩家团体', '/modules/groups/index.php',       2, '_self'],
        ['合集',     'http://igm.mchappyhut.club',      3, '_blank'],
        ['事件',     '/modules/timeline/index.php',     4, '_self'],
        ['人物志',   '/modules/figures/index.php',      5, '_self'],
        ['玩家日志', '/modules/player_logs/index.php',  6, '_self'],
        ['帮助中心', '/modules/help/index.php',         7, '_self'],
    ];
    
    $typeLink = 'link';
    foreach ($subItems as $item) {
        $stmt->bind_param("sssiisi", $item[0], $item[1], $typeLink, $moreId, $item[2], $visOne, $item[3]);
        $visOne = 1;
        $stmt->execute();
    }
}

// 修复：如果"更多"存在但缺少子项，自动补填（修复旧版 bug 遗留数据）
$moreCheck = $conn->query("SELECT id FROM nav_items WHERE title='更多' AND type='dropdown' LIMIT 1");
if ($moreCheck && $moreCheck->num_rows > 0) {
    $moreId = $moreCheck->fetch_assoc()['id'];
    $subCount = $conn->query("SELECT COUNT(*) AS cnt FROM nav_items WHERE parent_id = $moreId")->fetch_assoc()['cnt'];
    if ($subCount == 0) {
        $fixStmt = $conn->prepare("INSERT INTO nav_items (title, url, type, parent_id, sort_order, is_visible, target) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $fixSubs = [
            ['反馈',     '/modules/feedback/index.php',     1, '_self'],
            ['玩家团体', '/modules/groups/index.php',       2, '_self'],
            ['合集',     'http://igm.mchappyhut.club',      3, '_blank'],
            ['事件',     '/modules/timeline/index.php',     4, '_self'],
            ['人物志',   '/modules/figures/index.php',      5, '_self'],
            ['玩家日志', '/modules/player_logs/index.php',  6, '_self'],
            ['帮助中心', '/modules/help/index.php',         7, '_self'],
        ];
        $fixType = 'link';
        foreach ($fixSubs as $fs) {
            $fixStmt->bind_param("sssiisi", $fs[0], $fs[1], $fixType, $moreId, $fs[2], $visOne, $fs[3]);
            $visOne = 1;
            $fixStmt->execute();
        }
    }
}

// ===== 处理 POST 请求 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 保存排序
    if ($action === 'reorder') {
        $order = json_decode($_POST['order'] ?? '[]', true);
        $stmt = $conn->prepare("UPDATE nav_items SET sort_order = ? WHERE id = ?");
        foreach ($order as $item) {
            $stmt->bind_param("ii", $item['sort_order'], $item['id']);
            $stmt->execute();
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // 移动排序 (上移/下移)
    if ($action === 'move') {
        $id = intval($_POST['id']);
        $dir = $_POST['dir'] === 'up' ? -1 : 1;
        $item = $conn->query("SELECT * FROM nav_items WHERE id = $id")->fetch_assoc();
        if ($item) {
            $pid = $item['parent_id'] ? intval($item['parent_id']) : 'NULL';
            $neighbor = $conn->query("SELECT id, sort_order FROM nav_items WHERE parent_id " . ($item['parent_id'] ? "= $pid" : "IS NULL") . " AND sort_order " . ($dir > 0 ? '>' : '<') . " {$item['sort_order']} ORDER BY sort_order " . ($dir > 0 ? 'ASC' : 'DESC') . " LIMIT 1")->fetch_assoc();
            if ($neighbor) {
                $conn->query("UPDATE nav_items SET sort_order = {$neighbor['sort_order']} WHERE id = $id");
                $conn->query("UPDATE nav_items SET sort_order = {$item['sort_order']} WHERE id = {$neighbor['id']}");
            }
        }
        redirect(BASE_URL . '/modules/admin/nav_manage.php');
    }

    // 切换可见性
    if ($action === 'toggle') {
        $id = intval($_POST['id']);
        $conn->query("UPDATE nav_items SET is_visible = 1 - is_visible WHERE id = $id");
        $newVis = $conn->query("SELECT is_visible FROM nav_items WHERE id = $id")->fetch_assoc()['is_visible'];
        echo json_encode(['success' => true, 'is_visible' => (int)$newVis]);
        exit;
    }

    // 保存/新增项目
    if ($action === 'save') {
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $target = $_POST['target'] ?? '_self';
        $type = $_POST['type'] ?? 'link';
        $parent_id = $_POST['parent_id'] ? intval($_POST['parent_id']) : null;
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (empty($title)) {
            $error = '标题不能为空';
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare("UPDATE nav_items SET title=?, url=?, target=?, type=? WHERE id=?");
                $stmt->bind_param("ssssi", $title, $url, $target, $type, $id);
            } else {
                // 新项目取最大排序+1
                $pidCond = $parent_id ? "= $parent_id" : "IS NULL";
                $max = $conn->query("SELECT COALESCE(MAX(sort_order),0) + 1 AS nxt FROM nav_items WHERE parent_id $pidCond")->fetch_assoc()['nxt'];
                $stmt = $conn->prepare("INSERT INTO nav_items (title, url, type, parent_id, sort_order, target) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiii", $title, $url, $type, $parentIdVar, $max, $target);
                $parentIdVar = $parent_id;
            }
            $stmt->execute();
        }
        redirect(BASE_URL . '/modules/admin/nav_manage.php');
    }

    // 删除
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        // 如果是 dropdown 类型，同时删除其子项
        $item = $conn->query("SELECT id, type FROM nav_items WHERE id = $id")->fetch_assoc();
        if ($item) {
            if ($item['type'] === 'dropdown') {
                $conn->query("DELETE FROM nav_items WHERE parent_id = $id");
            }
            $conn->query("DELETE FROM nav_items WHERE id = $id");
        }
        redirect(BASE_URL . '/modules/admin/nav_manage.php');
    }
}

// ===== 查询数据 =====
// 注意：变量名加 manage_ 前缀，避免被 header.php 中的同名变量覆盖
// header.php 中会用 is_visible=1 过滤重新查询 $topItems，导致隐藏项消失
$manageTopItems = $conn->query("
    SELECT * FROM nav_items
    WHERE parent_id IS NULL
    ORDER BY sort_order ASC
")->fetch_all(MYSQLI_ASSOC);

$manageMoreItem = null;
foreach ($manageTopItems as $manageItem) {
    if ($manageItem['type'] === 'dropdown') {
        $manageMoreItem = $manageItem['id'];
        break;
    }
}

$manageSubItems = [];
if ($manageMoreItem) {
    $result = $conn->query("SELECT * FROM nav_items WHERE parent_id = $manageMoreItem ORDER BY sort_order ASC");
    $manageSubItems = $result->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = '导航管理';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:100px 20px 40px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:2.2rem; margin:0;">🧭 导航管理</h1>
            <p style="color:var(--text-secondary); margin:4px 0 0;">拖拽排序 · 编辑 · 显示/隐藏</p>
        </div>
        <button onclick="openEdit()" class="btn-auth" style="padding:10px 22px; white-space:nowrap;">
            <i class="fas fa-plus"></i> 添加一级导航
        </button>
    </div>

    <?php if (isset($error)): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid rgba(231,76,60,0.3); color:#e74c3c; padding:12px 20px; border-radius:10px; margin-bottom:16px;">✗ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- 一级导航列表（可拖拽） -->
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); overflow:hidden; margin-bottom:32px;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border-light); font-weight:700; font-size:0.9rem; color:var(--text-secondary);">
            一级导航 <span style="font-weight:400; font-size:0.8rem;">（拖拽左侧手柄排序）</span>
        </div>
        <div id="sortList" style="padding:8px;">
            <?php foreach ($manageTopItems as $idx => $manageItem): 
                $isMore = $manageItem['type'] === 'dropdown';
            ?>
            <div class="sort-item" data-id="<?php echo $manageItem['id']; ?>" data-visible="<?php echo $manageItem['is_visible']; ?>" draggable="true"
                 style="display:flex; align-items:center; gap:10px; padding:10px 14px; margin:4px 8px; background:var(--bg); border-radius:10px; border:1px solid var(--border-light); transition:0.2s; cursor:move;<?php echo $manageItem['is_visible'] ? '' : ' opacity:0.55;'; ?>">
                
                <!-- 拖拽手柄 -->
                <span style="color:var(--text-tertiary); cursor:grab; font-size:1.1rem; flex-shrink:0;">⋮⋮</span>
                
                <!-- 排序号 -->
                <span style="color:var(--text-tertiary); font-size:0.75rem; width:22px; flex-shrink:0;"><?php echo $idx + 1; ?></span>

                <!-- 图标 + 标题 -->
                <span style="font-weight:600; flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    <?php if ($isMore): ?>📂<?php else: ?>🔗<?php endif; ?>
                    <?php echo htmlspecialchars($manageItem['title']); ?>
                    <?php if ($manageItem['target'] === '_blank'): ?><span style="font-size:0.7rem; color:var(--text-tertiary);">↗</span><?php endif; ?>
                </span>

                <!-- URL 预览 -->
                <span style="font-size:0.75rem; color:var(--text-tertiary); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:none;" class="url-preview">
                    <?php echo htmlspecialchars($manageItem['url']); ?>
                </span>

                <!-- 可见性 -->
                <span class="vis-label" style="font-size:0.72rem; color:<?php echo $manageItem['is_visible'] ? 'var(--mc-green)' : '#e74c3c'; ?>; flex-shrink:0;">
                    <?php echo $manageItem['is_visible'] ? '显示' : '隐藏'; ?>
                </span>

                <!-- 操作按钮 -->
                <div style="display:flex; gap:4px; flex-shrink:0;">
                    <?php if ($idx > 0): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="move">
                        <input type="hidden" name="id" value="<?php echo $manageItem['id']; ?>">
                        <input type="hidden" name="dir" value="up">
                        <button type="submit" title="上移" style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:var(--text-secondary); font-size:0.7rem;">▲</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($idx < count($manageTopItems) - 1): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="move">
                        <input type="hidden" name="id" value="<?php echo $manageItem['id']; ?>">
                        <input type="hidden" name="dir" value="down">
                        <button type="submit" title="下移" style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:var(--text-secondary); font-size:0.7rem;">▼</button>
                    </form>
                    <?php endif; ?>
                    <button onclick="openEdit(<?php echo $manageItem['id']; ?>, '<?php echo addslashes($manageItem['title']); ?>', '<?php echo addslashes($manageItem['url']); ?>', '<?php echo $manageItem['target']; ?>', '<?php echo $manageItem['type']; ?>')" title="编辑"
                        style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:var(--text-secondary);">✎</button>
                    <button type="button" onclick="toggleItem(<?php echo $manageItem['id']; ?>, this)" title="<?php echo $manageItem['is_visible'] ? '点击隐藏' : '点击显示'; ?>"
                        style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:<?php echo $manageItem['is_visible'] ? '#e74c3c' : 'var(--mc-green)'; ?>; font-size:0.75rem;"><?php echo $manageItem['is_visible'] ? '👁' : '👁‍🗨'; ?></button>
                    <?php if ($manageItem['type'] !== 'dropdown' || true): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('确定删除「<?php echo addslashes($manageItem['title']); ?>」？<?php echo $isMore ? "\n将同时删除其下的所有子项！" : ''; ?>')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $manageItem['id']; ?>">
                        <button type="submit" title="删除" style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:#e74c3c;">✕</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- "更多"的下拉子项管理 -->
    <?php if ($manageMoreItem): ?>
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); overflow:hidden; margin-bottom:32px;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border-light); display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:700; font-size:0.9rem; color:var(--text-secondary);">📂 「更多」下拉子项 <span style="font-weight:400; font-size:0.8rem;">（拖拽排序）</span></span>
            <button onclick="openEdit(0, '', '', '_self', 'link', <?php echo $manageMoreItem; ?>)" class="btn-auth" style="padding:6px 16px; font-size:0.8rem;">
                <i class="fas fa-plus"></i> 添加子项
            </button>
        </div>
        <div id="subSortList" style="padding:8px;">
            <?php foreach ($manageSubItems as $sidx => $manageSub): ?>
            <div class="sort-item sub-item" data-id="<?php echo $manageSub['id']; ?>" draggable="true"
                 style="display:flex; align-items:center; gap:10px; padding:10px 14px; margin:4px 8px; margin-left:28px; background:var(--bg); border-radius:10px; border:1px solid var(--border-light); transition:0.2s; cursor:move;">
                
                <span style="color:var(--text-tertiary); cursor:grab; font-size:1.1rem; flex-shrink:0;">⋮⋮</span>
                <span style="color:var(--text-tertiary); font-size:0.75rem; width:22px; flex-shrink:0;"><?php echo $sidx + 1; ?></span>
                <span style="flex:1; font-weight:600; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    ↳ <?php echo htmlspecialchars($manageSub['title']); ?>
                    <?php if ($manageSub['target'] === '_blank'): ?><span style="font-size:0.7rem; color:var(--text-tertiary);">↗</span><?php endif; ?>
                </span>
                <span class="vis-label" style="font-size:0.72rem; color:<?php echo $manageSub['is_visible'] ? 'var(--mc-green)' : '#e74c3c'; ?>; flex-shrink:0;">
                    <?php echo $manageSub['is_visible'] ? '显示' : '隐藏'; ?>
                </span>
                <div style="display:flex; gap:4px; flex-shrink:0;">
                    <?php if ($sidx > 0): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?php echo $manageSub['id']; ?>"><input type="hidden" name="dir" value="up">
                        <button type="submit" title="上移" style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:var(--text-secondary); font-size:0.7rem;">▲</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($sidx < count($manageSubItems) - 1): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?php echo $manageSub['id']; ?>"><input type="hidden" name="dir" value="down">
                        <button type="submit" title="下移" style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:var(--text-secondary); font-size:0.7rem;">▼</button>
                    </form>
                    <?php endif; ?>
                    <button onclick="openEdit(<?php echo $manageSub['id']; ?>, '<?php echo addslashes($manageSub['title']); ?>', '<?php echo addslashes($manageSub['url']); ?>', '<?php echo $manageSub['target']; ?>', 'link', <?php echo $manageMoreItem; ?>)" title="编辑"
                        style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:var(--text-secondary);">✎</button>
                    <button onclick="toggleItem(<?php echo $manageSub['id']; ?>, this)" title="<?php echo $manageSub['is_visible'] ? '点击隐藏' : '点击显示'; ?>"
                        style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:<?php echo $manageSub['is_visible'] ? '#e74c3c' : 'var(--mc-green)'; ?>; font-size:0.75rem;"><?php echo $manageSub['is_visible'] ? '👁' : '👁‍🗨'; ?></button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('确定删除「<?php echo addslashes($manageSub['title']); ?>」？')">
                        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $manageSub['id']; ?>">
                        <button type="submit" style="background:none; border:1px solid var(--border); border-radius:6px; padding:4px 8px; cursor:pointer; color:#e74c3c;">✕</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($manageSubItems)): ?>
                <div style="text-align:center; padding:24px; color:var(--text-tertiary);">暂无下拉子项</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ===== 编辑弹窗 ===== -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1001; align-items:center; justify-content:center;">
    <div style="background:var(--surface); border-radius:16px; padding:32px; max-width:480px; width:100%; max-height:90vh; overflow-y:auto;">
        <h3 id="modalTitle" style="margin-bottom:20px;">添加导航项</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="editId" value="0">
            <input type="hidden" name="type" id="editType" value="link">
            <input type="hidden" name="parent_id" id="editParentId" value="">

            <div style="margin-bottom:14px;">
                <label style="font-weight:600; display:block; margin-bottom:4px;">标题 <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="title" id="editTitle" required placeholder="如：首页、公告..."
                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-weight:600; display:block; margin-bottom:4px;">链接 URL</label>
                <input type="text" name="url" id="editUrl" placeholder="/modules/xxx/index.php 或 https://..."
                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
            </div>

            <div style="margin-bottom:20px; display:flex; gap:16px;">
                <div style="flex:1;">
                    <label style="font-weight:600; display:block; margin-bottom:4px;">打开方式</label>
                    <select name="target" id="editTarget" style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
                        <option value="_self">当前页 (_self)</option>
                        <option value="_blank">新标签页 (_blank)</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeModal()" class="btn-auth" style="background:#95a5a6; color:#fff;">取消</button>
                <button type="submit" class="btn-auth" style="background:var(--mc-green); color:#fff;">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
// ===== 切换可见性 (AJAX, 无刷新) =====
    function toggleItem(id, btn) {
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle&id=' + id
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) {
                var row = btn.closest('.sort-item');
                var label = row.querySelector('.vis-label');
                var newVis = d.is_visible;
                // 更新眼睛图标
                btn.innerHTML = newVis ? '👁' : '👁‍🗨';
                btn.title = newVis ? '点击隐藏' : '点击显示';
                btn.style.color = newVis ? '#e74c3c' : 'var(--mc-green)';
                // 更新状态文字
                label.textContent = newVis ? '显示' : '隐藏';
                label.style.color = newVis ? 'var(--mc-green)' : '#e74c3c';
                // 更新整行透明度（隐藏项变半透明，但始终可见）
                row.style.opacity = newVis ? '1' : '0.55';
                row.setAttribute('data-visible', newVis);
                // 短暂高亮
                label.style.transition = '0.2s';
                label.style.fontWeight = '700';
                setTimeout(function() { label.style.fontWeight = ''; }, 600);
            }
        });
    }

// ===== 弹窗 =====
function openEdit(id, title, url, target, type, parentId) {
    document.getElementById('editId').value = id || 0;
    document.getElementById('editTitle').value = title || '';
    document.getElementById('editUrl').value = url || '';
    document.getElementById('editTarget').value = target || '_self';
    document.getElementById('editType').value = type || 'link';
    document.getElementById('editParentId').value = parentId || '';
    document.getElementById('modalTitle').textContent = id > 0 ? '编辑导航项' : (parentId ? '添加下拉子项' : '添加一级导航');
    document.getElementById('editModal').style.display = 'flex';
}
function closeModal() { document.getElementById('editModal').style.display = 'none'; }
document.getElementById('editModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

// ===== HTML5 拖拽排序 =====
let dragSrc = null;
const sortLists = document.querySelectorAll('#sortList, #subSortList');

sortLists.forEach(function(list) {
    list.addEventListener('dragstart', function(e) {
        dragSrc = e.target.closest('.sort-item');
        if (!dragSrc) return;
        e.dataTransfer.effectAllowed = 'move';
        dragSrc.style.opacity = '0.5';
    });

    list.addEventListener('dragend', function(e) {
        if (dragSrc) dragSrc.style.opacity = '1';
        dragSrc = null;
    });

    list.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });

    list.addEventListener('drop', function(e) {
        e.preventDefault();
        let target = e.target.closest('.sort-item');
        if (!target || !dragSrc || target === dragSrc) return;
        
        // 检查是否同一区域（一级 vs 子项）
        if (dragSrc.classList.contains('sub-item') !== target.classList.contains('sub-item')) return;
        
        let items = Array.from(list.querySelectorAll('.sort-item'));
        let fromIdx = items.indexOf(dragSrc);
        let toIdx = items.indexOf(target);
        
        if (fromIdx < toIdx) {
            list.insertBefore(dragSrc, target.nextSibling);
        } else {
            list.insertBefore(dragSrc, target);
        }

        // AJAX 保存新排序
        let newItems = Array.from(list.querySelectorAll('.sort-item'));
        let order = newItems.map(function(item, idx) {
            return { id: parseInt(item.dataset.id), sort_order: idx + 1 };
        });

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=reorder&order=' + encodeURIComponent(JSON.stringify(order))
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) {
                location.reload();
            }
        });
    });
});
</script>

<style>
    .sort-item { transition: background 0.2s, box-shadow 0.2s, opacity 0.2s; }
    .sort-item:hover { box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    @media (max-width: 600px) {
        .sort-item { flex-wrap: wrap; }
        .sort-item .url-preview { display: none !important; }
    }
</style>

<?php require_once __DIR__ . '/../../footer.php'; ?>
