<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
if (currentUser()['role'] === 'restricted') die('受限用户无法提交工单');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = $id > 0;
$ticket = ['title'=>'','game_name'=>'','gender'=>null,'server_id'=>0,'email'=>'','contact'=>'','content'=>'','status'=>'draft'];
$errors = [];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, currentUser()['id']);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();
    if (!$ticket || $ticket['status'] !== 'draft') die('工单不存在或不可编辑');
}

$servers = $conn->query("SELECT id, name FROM servers ORDER BY name");
$userEmail = currentUser()['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $game_name = trim($_POST['game_name'] ?? '');
    $genderInput = $_POST['gender'] ?? '';
    $gender = ($genderInput !== '') ? $genderInput : null;
    $server_id = intval($_POST['server_id'] ?? 0);
    $useAccountEmail = isset($_POST['use_account_email']);
    $newEmail = trim($_POST['new_email'] ?? '');
    $emailCode = trim($_POST['email_code'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $action = $_POST['action'] ?? 'draft';

    $finalEmail = $userEmail;
    if (!$useAccountEmail) {
        if (empty($newEmail)) $errors[] = '请输入新邮箱';
        elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) $errors[] = '邮箱格式不正确';
        elseif (!isset($_SESSION['ticket_email_code']) || strtoupper($emailCode) !== $_SESSION['ticket_email_code'])
            $errors[] = '邮箱验证码错误';
        else $finalEmail = $newEmail;
    }
    if (empty($title) || empty($content)) $errors[] = '标题和内容不能为空';

    if (empty($errors)) {
        $status = ($action === 'send') ? 'sent' : 'draft';
        $userId = currentUser()['id'];
        if ($editMode) {
            $stmt = $conn->prepare("UPDATE tickets SET title=?, game_name=?, gender=?, server_id=?, email=?, contact=?, content=?, status=? WHERE id=?");
            $stmt->bind_param("sssissssi", $title, $game_name, $gender, $server_id, $finalEmail, $contact, $content, $status, $id);
        } else {
            do {
                $ticketNo = 'A' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            } while ($conn->query("SELECT id FROM tickets WHERE ticket_no='$ticketNo'")->num_rows > 0);
            $stmt = $conn->prepare("INSERT INTO tickets (ticket_no, user_id, title, game_name, gender, server_id, email, contact, content, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sisssissss", $ticketNo, $userId, $title, $game_name, $gender, $server_id, $finalEmail, $contact, $content, $status);
        }
        if ($stmt->execute()) {
            $ticketId = $editMode ? $id : $conn->insert_id;
            // 处理附件上传
            if (!empty($_FILES['attachments']['name'][0])) {
                $fileCount = count($_FILES['attachments']['name']);
                $totalSize = 0;
                for ($i = 0; $i < $fileCount; $i++) {
                    $totalSize += $_FILES['attachments']['size'][$i];
                }
                if ($fileCount > 10) {
                    $errors[] = '附件数量不能超过10个';
                } elseif ($totalSize > 100 * 1024 * 1024) {
                    $errors[] = '附件总大小不能超过100MB';
                } else {
                    for ($i = 0; $i < $fileCount; $i++) {
                        $file = [
                            'name' => $_FILES['attachments']['name'][$i],
                            'type' => $_FILES['attachments']['type'][$i],
                            'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                            'error' => $_FILES['attachments']['error'][$i],
                            'size' => $_FILES['attachments']['size'][$i]
                        ];
                        $upload = uploadFile($file, ['image/jpeg','image/png','image/gif','image/webp','application/pdf','text/plain','application/zip','application/x-rar-compressed'], 20*1024*1024);
                        if ($upload['success']) {
                            $stmt = $conn->prepare("INSERT INTO ticket_attachments (ticket_id, file_name, file_path, file_size) VALUES (?,?,?,?)");
                            $stmt->bind_param("issi", $ticketId, $file['name'], $upload['url'], $file['size']);
                            $stmt->execute();
                        }
                    }
                }
            }
            if (empty($errors)) {
                unset($_SESSION['ticket_email_code']);
                redirect(BASE_URL . '/modules/feedback/tickets.php');
            }
        } else {
            $errors[] = '保存失败：' . $stmt->error;
        }
    }
}

$pageTitle = $editMode ? '编辑工单' : '提交工单';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:1400px; margin:0 auto; padding:100px 30px 60px;">
    <h1 style="font-size:2.5rem; font-weight:800; margin-bottom:8px;"><?php echo $editMode?'编辑工单':'提交新工单'; ?></h1>
    <p style="color:var(--text-secondary); font-size:1.1rem; margin-bottom:32px;">请详细描述您遇到的问题，我们将尽快处理</p>

    <?php if (!empty($errors)): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; padding:16px; border-radius:12px; margin-bottom:28px; color:#e74c3c; font-size:0.95rem;">
            <?php foreach ($errors as $e) echo '<div>• '.$e.'</div>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="ticketForm">
        <!-- 基本信息卡片 -->
        <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:24px; padding:36px; margin-bottom:24px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
            <h2 style="margin:0 0 24px; font-size:1.4rem; font-weight:700;">基本信息</h2>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
                <div>
                    <label style="font-weight:600; margin-bottom:8px; display:block; font-size:1rem;">工单名称 *</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($ticket['title']); ?>" required style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
                <div>
                    <label style="font-weight:600; margin-bottom:8px; display:block; font-size:1rem;">你的游戏名字</label>
                    <input type="text" name="game_name" value="<?php echo htmlspecialchars($ticket['game_name']); ?>" placeholder="输入你的 Minecraft ID" style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
                <div>
                    <label style="font-weight:600; margin-bottom:8px; display:block; font-size:1rem;">性别</label>
                    <select name="gender" style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                        <option value="">请选择</option>
                        <option value="male" <?php echo ($ticket['gender']==='male')?'selected':''; ?>>男</option>
                        <option value="female" <?php echo ($ticket['gender']==='female')?'selected':''; ?>>女</option>
                        <option value="other" <?php echo ($ticket['gender']==='other')?'selected':''; ?>>其他</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight:600; margin-bottom:8px; display:block; font-size:1rem;">相关服务器</label>
                    <select name="server_id" style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                        <option value="0">其他事务</option>
                        <?php while ($srv = $servers->fetch_assoc()): ?>
                            <option value="<?php echo $srv['id']; ?>" <?php echo ($ticket['server_id']==$srv['id'])?'selected':''; ?>><?php echo htmlspecialchars($srv['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label style="font-weight:600; margin-bottom:8px; display:block; font-size:1rem;">联系方式（QQ/微信）</label>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($ticket['contact']); ?>" placeholder="QQ号或微信号" style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
            </div>
        </div>

        <!-- 联系邮箱卡片 -->
        <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:24px; padding:36px; margin-bottom:24px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
            <h2 style="margin:0 0 20px; font-size:1.4rem; font-weight:700;">联系邮箱</h2>
            <label style="display:block; margin-bottom:14px; font-size:1rem;">
                <input type="radio" name="use_account_email" value="1" checked onchange="toggleEmail(false)"> 使用账号邮箱 (<?php echo $userEmail; ?>)
            </label>
            <label style="display:block; margin-bottom:14px; font-size:1rem;">
                <input type="radio" name="use_account_email" value="0" onchange="toggleEmail(true)"> 使用其他邮箱
            </label>
            <div id="newEmailBox" style="display:none; margin-top:16px;">
                <input type="email" name="new_email" placeholder="请输入新邮箱" style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                <div style="display:flex; gap:12px; margin-top:14px;">
                    <input type="text" name="email_code" placeholder="邮箱验证码" style="flex:1; padding:16px; border:1px solid var(--border); border-radius:12px;">
                    <button type="button" id="sendCodeBtn" class="btn-auth" style="white-space:nowrap; padding:16px 28px; font-size:1rem;">发送验证码</button>
                </div>
            </div>
        </div>

        <!-- 详细说明卡片 -->
        <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:24px; padding:36px; margin-bottom:24px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
            <h2 style="margin:0 0 20px; font-size:1.4rem; font-weight:700;">详细说明</h2>
            <textarea name="content" rows="8" required style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;"><?php echo htmlspecialchars($ticket['content']); ?></textarea>
            <div style="margin-top:24px;">
                <label style="font-weight:600; display:block; margin-bottom:10px; font-size:1rem;">附件（最多10个，总大小不超过100MB）</label>
                <input type="file" name="attachments[]" multiple style="width:100%; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
            </div>
        </div>

        <div style="display:flex; gap:16px; justify-content:flex-end;">
            <button type="submit" name="action" value="draft" class="btn-auth" style="background:#95a5a6; padding:16px 40px; font-size:1rem; border-radius:12px;">保存草稿</button>
            <button type="submit" name="action" value="send" class="btn-auth" style="padding:16px 40px; font-size:1rem; border-radius:12px;">提交工单</button>
        </div>
    </form>
</div>

<script>
function toggleEmail(show) {
    document.getElementById('newEmailBox').style.display = show ? 'block' : 'none';
}
document.getElementById('sendCodeBtn')?.addEventListener('click', async function() {
    const email = document.querySelector('input[name="new_email"]').value;
    if (!email) { alert('请先填写邮箱'); return; }
    this.disabled = true;
    this.textContent = '发送中...';
    try {
        const resp = await fetch('send_ticket_code.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'email=' + encodeURIComponent(email)
        });
        const data = await resp.json();
        alert(data.message);
    } catch(e) { alert('网络错误'); }
    this.disabled = false;
    this.textContent = '发送验证码';
});
</script>

<style>
    @media (max-width: 768px) {
        h1 { font-size: 2rem !important; }
        [style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
        input, select, textarea, button { padding: 12px !important; font-size: 0.9rem !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>