<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
$currentUser = currentUser();
$currentUserId = $currentUser['id'];
$currentUserRole = $currentUser['role'];
$isAdmin = ($currentUserRole === 'super_admin' || $currentUserRole === 'admin');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT t.*, u.username, u.avatar FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
if (!$ticket || (!$isAdmin && $ticket['user_id'] != $currentUserId)) die('无权访问该工单');

// 撤回回复
if (isset($_GET['recall_reply']) && is_numeric($_GET['recall_reply'])) {
    $replyId = intval($_GET['recall_reply']);
    $reply = $conn->query("SELECT user_id FROM ticket_replies WHERE id = $replyId AND ticket_id = $id AND deleted_at IS NULL")->fetch_assoc();
    if ($reply && ($reply['user_id'] == $currentUserId || $isAdmin)) {
        $conn->query("UPDATE ticket_replies SET deleted_at = NOW() WHERE id = $replyId");
    }
    redirect(BASE_URL . "/modules/feedback/ticket_detail.php?id=$id");
}
// 删除回复
if (isset($_GET['delete_reply']) && is_numeric($_GET['delete_reply'])) {
    $replyId = intval($_GET['delete_reply']);
    $reply = $conn->query("SELECT user_id FROM ticket_replies WHERE id = $replyId AND ticket_id = $id")->fetch_assoc();
    if ($reply && ($reply['user_id'] == $currentUserId || $isAdmin)) {
        $conn->query("DELETE FROM ticket_replies WHERE id = $replyId");
    }
    redirect(BASE_URL . "/modules/feedback/ticket_detail.php?id=$id");
}

// 管理员回复
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin && isset($_POST['reply_content'])) {
    $replyContent = trim($_POST['reply_content']);
    $newStatus = $_POST['new_status'] ?? '';
    $sendEmail = isset($_POST['send_email']) ? 1 : 0;
    if (empty($replyContent) || !in_array($newStatus, ['processing','on_hold','resolved'])) {
        $error = '请填写回复并选择状态';
    } else {
        $stmt = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, is_admin, content, send_email) VALUES (?, ?, 1, ?, ?)");
        $stmt->bind_param("iisi", $id, $currentUserId, $replyContent, $sendEmail);
        $stmt->execute();
        $conn->query("UPDATE tickets SET status = '$newStatus' WHERE id = $id");
        if ($sendEmail || $newStatus === 'resolved') {
            $user = $conn->query("SELECT email, username FROM users WHERE id = " . $ticket['user_id'])->fetch_assoc();
            require_once __DIR__ . '/send_work_mail.php';
            sendWorkMail($user['email'], "工单{$ticket['ticket_no']}更新", "<p>您的工单已更新为<strong>" . ($newStatus==='resolved'?'已办结':'留置') . "</strong></p><p>回复：{$replyContent}</p>");
        }
        redirect(BASE_URL . "/modules/feedback/ticket_detail.php?id=$id");
    }
}
// 用户追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isAdmin && isset($_POST['user_reply']) && in_array($ticket['status'], ['received','on_hold'])) {
    $content = trim($_POST['user_reply']);
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, is_admin, content) VALUES (?, ?, 0, ?)");
        $stmt->bind_param("iis", $id, $currentUserId, $content);
        $stmt->execute();
        redirect(BASE_URL . "/modules/feedback/ticket_detail.php?id=$id");
    }
}

$replies = $conn->query("SELECT r.*, u.username, u.avatar FROM ticket_replies r JOIN users u ON r.user_id = u.id WHERE r.ticket_id = $id AND r.deleted_at IS NULL ORDER BY r.created_at ASC");
$attachments = $conn->query("SELECT * FROM ticket_attachments WHERE ticket_id = $id");
$pageTitle = '工单 ' . $ticket['ticket_no'];
require_once __DIR__ . '/../../header.php';
?>

<div class="ticket-detail" style="max-width:1400px; margin:0 auto; padding:100px 30px 60px;">
    <!-- 头部信息 -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px; margin-bottom:30px;">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <img src="<?php echo $ticket['avatar']; ?>" style="width:44px; height:44px; border-radius:50%;">
            <div>
                <div style="font-weight:700; font-size:1.1rem;"><?php echo htmlspecialchars($ticket['username']); ?></div>
                <div style="color:var(--text-secondary); font-size:0.9rem;">工单号：<?php echo $ticket['ticket_no']; ?></div>
            </div>
        </div>
        <div>
            <span style="background:<?php $c=['draft'=>'#95a5a6','sent'=>'#3498db','received'=>'#2ecc71','processing'=>'#f39c12','on_hold'=>'#e74c3c','resolved'=>'#27ae60']; echo $c[$ticket['status']]; ?>; color:#fff; padding:6px 18px; border-radius:20px;"><?php echo ['draft'=>'草稿','sent'=>'已发送','received'=>'已接收','processing'=>'处理中','on_hold'=>'留置','resolved'=>'已办结'][$ticket['status']]; ?></span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:24px;">
            <h3>基本信息</h3>
            <div style="display:grid; gap:10px; margin-top:12px;">
                <div><strong>游戏名：</strong> <?php echo htmlspecialchars($ticket['game_name'] ?: '-'); ?></div>
                <div><strong>性别：</strong> <?php echo $ticket['gender'] ? ($ticket['gender']=='male'?'男':'女') : '-'; ?></div>
                <div><strong>服务器：</strong> <?php echo $ticket['server_id']==0 ? '其他事务' : htmlspecialchars($conn->query("SELECT name FROM servers WHERE id=".$ticket['server_id'])->fetch_assoc()['name'] ?? ''); ?></div>
                <div><strong>邮箱：</strong> <?php echo htmlspecialchars($ticket['email']); ?></div>
                <div><strong>联系方式：</strong> <?php echo htmlspecialchars($ticket['contact'] ?: '-'); ?></div>
                <div><strong>时间：</strong> <?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></div>
            </div>
        </div>
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:24px;">
            <h3>附件</h3>
            <?php if ($attachments->num_rows > 0): ?>
                <ul style="padding-left:20px;">
                <?php while ($att = $attachments->fetch_assoc()): ?>
                    <li><a href="<?php echo $att['file_path']; ?>" target="_blank"><?php echo htmlspecialchars($att['file_name']); ?></a></li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?><p style="color:var(--text-secondary);">无附件</p><?php endif; ?>
        </div>
    </div>

    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:24px; margin-bottom:24px;">
        <h3>详细说明</h3>
        <div style="line-height:1.8;"><?php echo nl2br(htmlspecialchars($ticket['content'])); ?></div>
    </div>

    <!-- 沟通记录 -->
    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:24px; margin-bottom:24px;">
        <h3>沟通记录</h3>
        <?php if ($replies->num_rows > 0): ?>
            <?php while ($reply = $replies->fetch_assoc()): ?>
                <div style="border-bottom:1px solid var(--border-light); padding:16px 0;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                        <img src="<?php echo $reply['avatar']; ?>" style="width:32px; height:32px; border-radius:50%;">
                        <span style="font-weight:700;"><?php echo htmlspecialchars($reply['username']); ?></span>
                        <?php if ($reply['is_admin']): ?><span style="background:var(--mc-green); color:#fff; padding:2px 10px; border-radius:10px; font-size:0.75rem;">管理员</span><?php endif; ?>
                        <span style="margin-left:auto; color:var(--text-secondary); font-size:0.85rem;"><?php echo date('m-d H:i', strtotime($reply['created_at'])); ?></span>
                    </div>
                    <div style="line-height:1.7;"><?php echo $reply['content']; ?></div>
                    <?php if ($reply['user_id'] == $currentUserId || $isAdmin): ?>
                    <div style="margin-top:6px;">
                        <a href="?id=<?php echo $id; ?>&recall_reply=<?php echo $reply['id']; ?>" style="color:#f39c12;">撤回</a>
                        <a href="?id=<?php echo $id; ?>&delete_reply=<?php echo $reply['id']; ?>" style="color:#e74c3c; margin-left:10px;">删除</a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?><p style="color:var(--text-secondary);">暂无沟通</p><?php endif; ?>
    </div>

    <!-- 用户补充 -->
    <?php if (!$isAdmin && in_array($ticket['status'], ['received','on_hold'])): ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:20px; margin-bottom:24px;">
            <h4>补充说明</h4>
            <form method="POST">
                <textarea name="user_reply" rows="3" required style="width:100%; padding:12px; border-radius:10px;"></textarea>
                <button type="submit" class="btn-auth" style="margin-top:10px;">提交</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- 管理员回复 -->
    <?php if ($isAdmin && $ticket['status'] !== 'resolved'): ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:24px; padding:28px; box-shadow:var(--shadow-lg);">
            <h3>回复工单</h3>
            <form method="POST" id="adminReplyForm">
                <textarea name="reply_content" id="replyContent" rows="5" required style="width:100%; padding:14px; border-radius:12px;"></textarea>
                <div style="display:flex; align-items:center; gap:16px; margin-top:14px; flex-wrap:wrap;">
                    <select name="new_status" style="padding:10px; border-radius:10px;">
                        <option value="processing">处理中</option>
                        <option value="on_hold">留置</option>
                        <option value="resolved">已办结</option>
                    </select>
                    <label style="display:flex; align-items:center; gap:6px;">
                        <input type="checkbox" name="send_email" value="1" checked> 邮件通知
                    </label>
                    <button type="button" id="uploadImageBtn" style="padding:10px 18px; border-radius:10px; background:var(--surface-alt);"><i class="fas fa-image"></i> 图片</button>
                    <input type="file" id="imageInput" accept="image/*" style="display:none;">
                    <button type="submit" style="padding:12px 28px; background:var(--mc-green); color:#fff; border-radius:10px; font-weight:600; border:none; cursor:pointer;">发送回复</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('uploadImageBtn')?.addEventListener('click', ()=> document.getElementById('imageInput').click());
document.getElementById('imageInput')?.addEventListener('change', async function(){
    const file = this.files[0];
    if (!file) return;
    const fd = new FormData(); fd.append('image', file);
    const resp = await fetch('/upload.php', {method:'POST', body:fd});
    const data = await resp.json();
    if (data.success) {
        const textarea = document.getElementById('replyContent');
        textarea.value += `<img src="${data.url}" style="max-width:100%; margin:8px 0;">`;
    }
    this.value = '';
});
</script>

<style>
@media (max-width: 768px) {
    .ticket-detail { padding: 80px 16px 40px !important; }
    .ticket-detail h1 { font-size: 1.8rem !important; }
    [style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
    input, select, textarea, button { padding: 10px !important; font-size: 0.9rem !important; }
    img[style*="width:44px"] { width: 36px !important; height: 36px !important; }
}
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>