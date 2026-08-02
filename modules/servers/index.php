<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '服务器列表';
require_once __DIR__ . '/../../header.php';

$stmt = $conn->query("SELECT * FROM servers ORDER BY name ASC");
$servers = $stmt->fetch_all(MYSQLI_ASSOC);

$categoryLabels = [
    'cn_mobile' => '中国版移动端',
    'cn_java' => '中国版Java端',
    'intl_mobile' => '国际版移动端',
    'intl_java' => '国际版Java端',
    'intl_cross' => '国际版互通端',
];
?>

<!-- 横幅 -->
<div style="position:relative; width:100%; min-height:300px; display:flex; align-items:center; justify-content:flex-end; padding:0 40px; background: linear-gradient(to bottom, rgba(10,14,8,0.35), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/assets/images/ag3.png') center/cover no-repeat; border-bottom:4px solid #c9a84c;">
    <div style="text-align:right; color:#fff; z-index:1; animation: slideInRight 0.8s ease both;">
        <h1 style="font-size:clamp(2.5rem,5vw,3.8rem); font-weight:800; margin-bottom:8px;">服务器列表</h1>
        <p style="font-size:1.2rem; opacity:0.9;">Server List</p>
    </div>
</div>

<div style="background: linear-gradient(to bottom, rgba(245,245,245,0.6), rgba(250,250,250,0.5)), url('<?php echo BASE_URL; ?>/assets/images/bj.png') center/cover no-repeat; background-attachment:fixed; padding:30px 0 60px;">
    <div style="max-width:1400px; margin:0 auto; padding:0 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; animation: fadeInUp 0.6s ease 0.2s both;">
            <h2 style="font-size:1.8rem; font-weight:700; border-left:6px solid var(--mc-green); padding-left:16px;">🖥️ 服务器列表</h2>
            <?php if (isAdmin()): ?>
                <a href="edit.php" class="btn-auth" style="text-decoration:none; padding:12px 28px;"><i class="fas fa-plus"></i> 添加服务器</a>
            <?php endif; ?>
        </div>

        <div class="server-page-layout" style="display:flex; gap:30px; align-items:flex-start;">
            <!-- 左侧列表 -->
            <div class="server-list-panel" style="flex:1; min-width:300px; max-width:420px; animation: fadeInUp 0.6s ease 0.3s both;">
                <?php if (empty($servers)): ?>
                    <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:16px; padding:40px; text-align:center;">暂无服务器</div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php foreach ($servers as $server): ?>
                            <div class="server-item" data-server-id="<?php echo $server['id']; ?>" 
                                 style="background:var(--surface-glass); backdrop-filter:blur(16px); border:1px solid var(--border-light); border-radius:14px; padding:16px; cursor:pointer; transition:all 0.3s ease; display:flex; align-items:center; gap:14px; animation: fadeInLeft 0.5s ease both;"
                                 onclick="selectServer(this, <?php echo $server['id']; ?>)">
                                <div style="flex-shrink:0; width:48px; height:48px; border-radius:12px; overflow:hidden; background:var(--surface-alt);">
                                    <?php if (!empty($server['avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($server['avatar']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--mc-green); font-size:1.5rem;"><i class="fas fa-server"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div style="min-width:0; flex:1;">
                                    <h4 style="font-size:1.1rem; font-weight:700; margin:0 0 2px;"><?php echo htmlspecialchars($server['name']); ?></h4>
                                    <p style="font-size:0.85rem; color:var(--text-secondary); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($server['subtitle'] ?? $server['address']); ?></p>
                                </div>
                                <div style="flex-shrink:0;">
                                    <span style="width:10px; height:10px; border-radius:50%; display:inline-block; background:<?php echo $server['status']==='online'?'#4F8A30':($server['status']==='maintenance'?'#D4942B':'#e74c3c'); ?>; box-shadow:0 0 6px currentColor; animation: pulse 2s infinite;"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 右侧详情代码块 -->
            <div class="server-detail-panel" style="flex:2; min-width:0; position:sticky; top:100px; animation: fadeInRight 0.6s ease 0.5s both;">
                <div style="background:#1e1e1e; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
                    <div style="display:flex; align-items:center; gap:8px; padding:12px 16px; background:#2d2d2d;">
                        <span style="width:12px; height:12px; border-radius:50%; background:#ff5f56;"></span>
                        <span style="width:12px; height:12px; border-radius:50%; background:#ffbd2e;"></span>
                        <span style="width:12px; height:12px; border-radius:50%; background:#27c93f;"></span>
                        <span style="margin-left:auto; color:#999; font-size:0.85rem; animation: blink 1.5s infinite;">● Hello World</span>
                    </div>
                    <div id="server-detail-content" style="color:#ccc; transition: opacity 0.4s ease;">
                        <?php if (!empty($servers)): ?>
                            <script>document.addEventListener('DOMContentLoaded', function() { if(serversData.length) selectServer(document.querySelector('.server-item[data-server-id="'+serversData[0].id+'"]'), serversData[0].id); });</script>
                        <?php else: ?>
                            <div style="padding:40px; text-align:center; color:#aaa;">请先添加服务器</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const serversData = <?php echo json_encode($servers, JSON_UNESCAPED_UNICODE); ?>;
const categoryLabels = <?php echo json_encode($categoryLabels, JSON_UNESCAPED_UNICODE); ?>;

function formatLink(link) {
    if (!link) return '#';
    if (/^https?:\/\//i.test(link)) return link;
    return '//' + link;
}

function renderServerDetail(server) {
    if (!server) return;
    const container = document.getElementById('server-detail-content');
    const statusColor = server.status === 'online' ? '#4F8A30' : (server.status === 'maintenance' ? '#D4942B' : '#e74c3c');
    const statusText = server.status === 'online' ? '在线' : (server.status === 'maintenance' ? '维护中' : '离线');
    const coverUrl = server.cover ? server.cover : '';
    const category = server.category ? (categoryLabels[server.category] || server.category) : '未分类';
    const version = server.version || '未知';
    const hasMod = parseInt(server.has_mod) === 1 ? '是' : '否';
    const clientNote = server.client_note || '无特殊说明';

    container.style.opacity = '0';
    container.innerHTML = `
        <div style="height:250px; background:${coverUrl ? `url('${coverUrl}') center/cover no-repeat` : '#2d2d2d'}; border-bottom:1px solid #333; position:relative;">
            <div style="position:absolute; top:16px; left:16px; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); padding:4px 12px; border-radius:6px; font-size:0.75rem; color:#fff;">${escapeHtml(category)}</div>
        </div>
        <div style="padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="color:#e0e0e0; font-size:1.6rem; font-weight:700; margin:0;">${escapeHtml(server.name)}</h2>
                <span style="display:flex; align-items:center; gap:6px; background:rgba(255,255,255,0.1); padding:4px 14px; border-radius:20px; font-size:0.85rem; color:#fff;">
                    <span style="width:8px; height:8px; border-radius:50%; background:${statusColor}; animation:pulse 2s infinite;"></span>
                    ${statusText}
                </span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; color:#bbb; font-size:0.9rem; line-height:1.6;">
                <div><span style="color:#888;">地址：</span>${escapeHtml(server.address)}${server.port ? ':' + escapeHtml(server.port) : ''}</div>
                <div><span style="color:#888;">端口：</span>${server.port || '25565'}</div>
                <div><span style="color:#888;">类别：</span>${escapeHtml(category)}</div>
                <div><span style="color:#888;">版本：</span>${escapeHtml(version)}</div>
                <div><span style="color:#888;">模组：</span>${hasMod}</div>
                <div><span style="color:#888;">人数：</span>${server.max_players || '未设定'}</div>
                <div style="grid-column:span 2;"><span style="color:#888;">说明：</span>${escapeHtml(clientNote)}</div>
            </div>
            <div style="display:flex; gap:12px; margin-top:20px; align-items:center;">
                <button onclick="copyAddress('${escapeHtml(server.address)}${server.port ? ':' + escapeHtml(server.port) : ''}')" style="background:#333; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">复制地址</button>
                <a href="detail.php?id=${server.id}" style="background:var(--mc-green); color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none;">详情</a>
                <?php if (isAdmin()): ?>
                <a href="edit.php?id=${server.id}" style="background:#D4942B; color:#000; padding:10px 20px; border-radius:8px; text-decoration:none; margin-left:auto;">编辑</a>
                <?php endif; ?>
            </div>
        </div>
    `;
    setTimeout(() => { container.style.opacity = '1'; }, 50);
}

function selectServer(element, serverId) {
    document.querySelectorAll('.server-item').forEach(item => item.style.background = 'var(--surface-glass)');
    element.style.background = 'rgba(79,138,48,0.15)';
    element.style.borderColor = 'var(--mc-green)';
    const server = serversData.find(s => s.id == serverId);
    if (server) renderServerDetail(server);
}

function copyAddress(text) { navigator.clipboard.writeText(text).then(() => alert('地址已复制')); }
function escapeHtml(text) { return text ? String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }
</script>

<style>
    .server-item:hover { transform: translateX(4px); background: rgba(79,138,48,0.08) !important; }
    @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
    @keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0.3; } }
    @keyframes slideInRight { from { opacity:0; transform:translateX(30px); } to { opacity:1; transform:translateX(0); } }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeInLeft { from { opacity:0; transform:translateX(-20px); } to { opacity:1; transform:translateX(0); } }
    @keyframes fadeInRight { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
    
    @media (max-width: 900px) {
        .server-page-layout { flex-direction: column; }
        .server-list-panel { max-width: 100% !important; }
        .server-detail-panel { position: static !important; margin-top: 20px; }
    }
</style>

<?php require_once __DIR__ . '/../../footer.php'; ?>