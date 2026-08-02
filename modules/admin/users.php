<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
$types = '';
if ($search) {
    $where = " WHERE username LIKE ? OR email LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$totalStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM users $where");
if ($types) $totalStmt->bind_param($types, ...$params);
$totalStmt->execute();
$total = $totalStmt->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($total / $perPage);

$stmt = $conn->prepare("SELECT id, username, email, avatar, role, created_at FROM users $where ORDER BY id DESC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$types .= "ii";
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$users = $stmt->get_result();

$pageTitle = '用户管理';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:1300px; margin:0 auto; padding:100px 20px 40px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h1 style="font-size:2.2rem;">👥 用户管理</h1>
        <form method="GET" style="display:flex; gap:8px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索用户名或邮箱" style="padding:8px 16px; border:1px solid var(--border); border-radius:20px; background:var(--bg); color:var(--text);">
            <button type="submit" class="btn-auth" style="padding:8px 16px;">搜索</button>
        </form>
    </div>

    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:var(--surface-alt);">
                    <th style="padding:12px; text-align:left;">头像</th>
                    <th style="padding:12px; text-align:left;">用户名</th>
                    <th style="padding:12px; text-align:left;">邮箱</th>
                    <th style="padding:12px; text-align:left;">当前角色</th>
                    <th style="padding:12px; text-align:left;">注册时间</th>
                    <th style="padding:12px; text-align:left;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:12px;"><img src="<?php echo htmlspecialchars($user['avatar']); ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover;"></td>
                        <td style="padding:12px;"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td style="padding:12px;"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td style="padding:12px;">
                            <span style="background:<?php echo $user['role'] === 'super_admin' ? '#e74c3c' : ($user['role'] === 'admin' ? '#D4942B' : ($user['role'] === 'group_leader' ? '#3498db' : ($user['role'] === 'senior_adventurer' ? '#9b59b6' : ($user['role'] === 'restricted' ? '#95a5a6' : '#4F8A30')))); ?>; color:#fff; padding:2px 10px; border-radius:12px; font-size:0.8rem;">
                                <?php echo $user['role'] === 'super_admin' ? '超级管理员' : ($user['role'] === 'admin' ? '管理员' : ($user['role'] === 'group_leader' ? '团体负责人' : ($user['role'] === 'senior_adventurer' ? '高级冒险家' : ($user['role'] === 'restricted' ? '受限用户' : '冒险家')))); ?>
                            </span>
                        </td>
                        <td style="padding:12px; font-size:0.85rem; color:var(--text-tertiary);"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td style="padding:12px;">
                            <button onclick="editRole(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo $user['role']; ?>')" class="btn-auth" style="padding:4px 12px; font-size:0.8rem;">修改角色</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($users->num_rows === 0): ?>
                    <tr><td colspan="6" style="padding:24px; text-align:center; color:var(--text-secondary);">暂无用户</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:24px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" style="padding:8px 16px; border-radius:20px; text-decoration:none; background:<?php echo $i === $page ? 'var(--mc-green)' : 'var(--surface-alt)'; ?>; color:<?php echo $i === $page ? '#fff' : 'var(--text)'; ?>;"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- 角色编辑弹窗 -->
<div id="roleModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:var(--surface); border-radius:16px; padding:32px; max-width:400px; width:100%;">
        <h3 style="margin-bottom:16px;">修改用户角色：<span id="modalUsername"></span></h3>
        <form method="POST" action="update_role.php">
            <input type="hidden" name="user_id" id="modalUserId">
            <select name="role" id="modalRole" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text); margin-bottom:16px;">
                <?php if (hasRole('super_admin')): ?>
                    <option value="super_admin">超级管理员</option>
                <?php endif; ?>
                <option value="admin">管理员</option>
                <option value="group_leader">团体负责人</option>
                <option value="senior_adventurer">高级冒险家</option>
                <option value="adventurer">冒险家</option>
                <option value="restricted">受限用户</option>
            </select>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('roleModal').style.display='none'" class="btn-auth" style="background:#95a5a6;">取消</button>
                <button type="submit" class="btn-auth">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function editRole(id, username, currentRole) {
    document.getElementById('modalUserId').value = id;
    document.getElementById('modalUsername').textContent = username;
    document.getElementById('modalRole').value = currentRole;
    document.getElementById('roleModal').style.display = 'flex';
}
// 点击背景关闭
document.getElementById('roleModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<?php require_once __DIR__ . '/../../footer.php'; ?>