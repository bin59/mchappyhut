<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM servers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$server = $stmt->get_result()->fetch_assoc();
if (!$server) redirect(BASE_URL . '/modules/servers/');
$pageTitle = htmlspecialchars($server['name']) . ' - 服务器';
require_once __DIR__ . '/../../header.php';

function fixUrl($url) {
    if (empty($url)) return '#';
    if (preg_match('/^(https?:)?\/\//i', $url)) return $url;
    return '//' . $url;
}

$categories = [
    'cn_mobile' => '中国版移动端',
    'cn_java' => '中国版Java端',
    'intl_mobile' => '国际版移动端',
    'intl_java' => '国际版Java端',
    'intl_cross' => '国际版互通端',
];
$categoryDisplay = isset($server['category']) && isset($categories[$server['category']]) ? $categories[$server['category']] : ($server['category'] ?? '未分类');
?>

<div style="max-width:100%; animation: fadeIn 0.6s ease;">
    <?php if (!empty($server['cover'])): ?>
        <div style="width:100%; height:60vh; max-height:500px; background: url('<?php echo htmlspecialchars($server['cover']); ?>') center/cover no-repeat; position:relative;">
            <div style="position:absolute; inset:0; background:linear-gradient(to bottom, transparent 40%, rgba(0,0,0,0.7));"></div>
        </div>
    <?php endif; ?>

    <div style="max-width:1200px; margin:0 auto; padding:30px 20px 60px; position:relative; z-index:1; <?php echo !empty($server['cover']) ? 'margin-top:-80px;' : ''; ?>">
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:40px; box-shadow:var(--shadow-lg);">
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
                <?php if (!empty($server['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($server['avatar']); ?>" style="width:64px; height:64px; border-radius:16px; object-fit:cover;">
                <?php endif; ?>
                <div style="flex:1;">
                    <h1 style="font-size:2.2rem; font-weight:800;"><?php echo htmlspecialchars($server['name']); ?></h1>
                    <p style="color:var(--text-secondary);"><?php echo htmlspecialchars($server['subtitle'] ?? ''); ?></p>
                </div>
                <div>
                    <span style="display:flex; align-items:center; gap:6px; background:rgba(0,0,0,0.05); padding:6px 18px; border-radius:20px; font-weight:600;">
                        <span style="width:8px; height:8px; border-radius:50%; background:<?php echo $server['status']==='online'?'#4F8A30':($server['status']==='maintenance'?'#D4942B':'#e74c3c'); ?>; animation:pulse 2s infinite;"></span>
                        <?php echo $server['status']==='online'?'在线':($server['status']==='maintenance'?'维护中':'离线'); ?>
                    </span>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px; margin-bottom:30px;">
                <div style="background:var(--surface-alt); border-radius:12px; padding:16px;"><span style="color:var(--text-secondary);">地址</span><br><strong><?php echo htmlspecialchars($server['address']); ?><?php echo $server['port'] ? ':'.htmlspecialchars($server['port']) : ''; ?></strong></div>
                <div style="background:var(--surface-alt); border-radius:12px; padding:16px;"><span style="color:var(--text-secondary);">端口</span><br><strong><?php echo $server['port'] ?: '25565'; ?></strong></div>
                <div style="background:var(--surface-alt); border-radius:12px; padding:16px;"><span style="color:var(--text-secondary);">游戏类别</span><br><strong><?php echo htmlspecialchars($categoryDisplay); ?></strong></div>
                <div style="background:var(--surface-alt); border-radius:12px; padding:16px;"><span style="color:var(--text-secondary);">版本</span><br><strong><?php echo htmlspecialchars($server['version'] ?? '未知'); ?></strong></div>
                <div style="background:var(--surface-alt); border-radius:12px; padding:16px;"><span style="color:var(--text-secondary);">模组</span><br><strong><?php echo $server['has_mod'] ? '是' : '否'; ?></strong></div>
                <div style="background:var(--surface-alt); border-radius:12px; padding:16px;"><span style="color:var(--text-secondary);">人数上限</span><br><strong><?php echo $server['max_players'] ?: '未设定'; ?></strong></div>
            </div>

            <?php if (!empty($server['client_note'])): ?>
                <div style="background:var(--surface-alt); border-radius:12px; padding:16px; margin-bottom:24px;">
                    <span style="color:var(--text-secondary);">客户端说明</span><br>
                    <div style="margin-top:4px;"><?php echo nl2br(htmlspecialchars($server['client_note'])); ?></div>
                </div>
            <?php endif; ?>

            <div class="server-description" style="line-height:1.8; word-break:break-word;">
                <?php echo $server['description']; ?>
            </div>

            <div class="server-actions" style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
                <button onclick="copyAddress('<?php echo htmlspecialchars($server['address']); ?><?php echo $server['port'] ? ':'.htmlspecialchars($server['port']) : ''; ?>')" class="btn-auth" style="padding:10px 24px; white-space:nowrap;">复制地址</button>
                <?php if ($server['join_link']): ?><a href="<?php echo fixUrl($server['join_link']); ?>" target="_blank" class="btn-auth" style="text-decoration:none; padding:10px 24px; white-space:nowrap;">立即加入</a><?php endif; ?>
                <?php if (isAdmin()): ?>
                    <a href="edit.php?id=<?php echo $server['id']; ?>" class="btn-auth" style="background:#D4942B; padding:10px 24px; white-space:nowrap;">编辑</a>
                    <a href="delete.php?id=<?php echo $server['id']; ?>" class="btn-auth" style="background:#e74c3c; padding:10px 24px; white-space:nowrap;" onclick="return confirm('确定删除？');">删除</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
    .server-description img { max-width: 100% !important; height: auto !important; display: block; margin: 12px 0; border-radius: 8px; }
    @media (max-width: 768px) {
        h1 { font-size: 1.6rem !important; }
        div[style*="grid-template-columns"] { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
        .server-description { font-size: 0.85rem; }
        .btn-auth { padding: 10px 16px !important; font-size: 0.85rem; width: 100% !important; text-align: center !important; }
        .server-actions { flex-direction: column !important; }
    }
</style>
<script>
function copyAddress(text) { navigator.clipboard.writeText(text).then(() => alert('地址已复制')); }
</script>
<?php require_once __DIR__ . '/../../footer.php'; ?>