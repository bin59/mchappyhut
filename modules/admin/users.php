<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

// 手动添加用户（仅超级管理员）
$addError = '';
$addSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    if (!hasRole('super_admin')) {
        $addError = '仅超级管理员可手动添加用户';
    } else {
        $newUsername = trim($_POST['new_username'] ?? '');
        $newEmail = trim($_POST['new_email'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $newRole = $_POST['new_role'] ?? 'adventurer';

        if (empty($newUsername) || empty($newEmail) || empty($newPassword)) {
            $addError = '请填写所有必填字段';
        } elseif (strlen($newPassword) < 6) {
            $addError = '密码长度至少6位';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $addError = '邮箱格式不正确';
        } else {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $checkStmt->bind_param("ss", $newEmail, $newUsername);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $addError = '邮箱或用户名已存在';
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                // 默认头像
                $defaultAvatar = BASE_URL . '/assets/default-avatar.png';
                $insertStmt = $conn->prepare("INSERT INTO users (username, email, password, role, avatar) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->bind_param("sssss", $newUsername, $newEmail, $hashed, $newRole, $defaultAvatar);
                if ($insertStmt->execute()) {
                    $addSuccess = "用户 {$newUsername} 添加成功！（角色：" . roleLabel($newRole) . "）";
                } else {
                    $addError = '添加失败：' . $conn->error;
                }
            }
        }
    }
}

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
    <?php if ($addSuccess): ?>
        <div style="background:rgba(46,204,113,0.1); border:1px solid rgba(46,204,113,0.3); color:#2ecc71; padding:12px 20px; border-radius:10px; margin-bottom:16px;">✓ <?php echo htmlspecialchars($addSuccess); ?></div>
    <?php endif; ?>
    <?php if ($addError): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid rgba(231,76,60,0.3); color:#e74c3c; padding:12px 20px; border-radius:10px; margin-bottom:16px;">✗ <?php echo htmlspecialchars($addError); ?></div>
    <?php endif; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <h1 style="font-size:2.2rem;">👥 用户管理</h1>
        <div style="display:flex; gap:8px;">
            <?php if (hasRole('super_admin')): ?>
                <button onclick="openAddModal()" class="btn-auth" style="padding:8px 18px; background:var(--mc-green); color:#fff; white-space:nowrap;">
                    <i class="fas fa-user-plus"></i> 添加用户
                </button>
            <?php endif; ?>
            <form method="GET" style="display:flex; gap:8px;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索用户名或邮箱" style="padding:8px 16px; border:1px solid var(--border); border-radius:20px; background:var(--bg); color:var(--text);">
                <button type="submit" class="btn-auth" style="padding:8px 16px;">搜索</button>
            </form>
        </div>
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

<!-- 添加用户弹窗（仅超级管理员可见） -->
<?php if (hasRole('super_admin')): ?>
<div id="addUserModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1001; align-items:center; justify-content:center;">
    <div style="background:var(--surface); border-radius:16px; padding:32px; max-width:460px; width:100%; max-height:90vh; overflow-y:auto;">
        <h3 style="margin-bottom:20px;">➕ 手动添加用户</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_user">
            
            <div style="margin-bottom:14px;">
                <label style="font-weight:600; display:block; margin-bottom:4px;">用户名 <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="new_username" required placeholder="输入用户名"
                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-weight:600; display:block; margin-bottom:4px;">邮箱 <span style="color:#e74c3c;">*</span></label>
                <input type="email" name="new_email" required placeholder="输入邮箱地址"
                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-weight:600; display:block; margin-bottom:4px;">密码 <span style="color:#e74c3c;">*</span></label>
                <input type="password" name="new_password" required placeholder="至少6位密码" minlength="6"
                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="font-weight:600; display:block; margin-bottom:4px;">角色</label>
                <select name="new_role" style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); font-size:0.95rem;">
                    <option value="super_admin">超级管理员</option>
                    <option value="admin">管理员</option>
                    <option value="group_leader">团体负责人</option>
                    <option value="senior_adventurer">高级冒险家</option>
                    <option value="adventurer" selected>冒险家（默认）</option>
                    <option value="restricted">受限用户</option>
                </select>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeAddModal()" class="btn-auth" style="background:#95a5a6; color:#fff;">取消</button>
                <button type="submit" class="btn-auth" style="background:var(--mc-green); color:#fff;">添加用户</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

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

// 添加用户弹窗
function openAddModal() { document.getElementById('addUserModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addUserModal').style.display = 'none'; }
document.getElementById('addUserModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<?php require_once __DIR__ . '/../../footer.php'; ?>