<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM contributors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$contributor = $stmt->get_result()->fetch_assoc();
if (!$contributor) {
    redirect(BASE_URL . '/modules/about/');
}
$pageTitle = htmlspecialchars($contributor['name']) . ' - 贡献者';
require_once __DIR__ . '/../../header.php';
?>

<div class="contributor-page" style="max-width:1600px; margin:0 auto; padding:100px 20px 60px; animation: fadeIn 0.6s ease;">

    <div class="contributor-layout" style="display:flex; gap:30px; align-items:flex-start;">

        <!-- 左侧：人物卡片 + 3D皮肤模型 -->
        <div class="contributor-sidebar" style="flex-shrink:0; width:320px; position:sticky; top:100px;">
            
            <!-- 上部：人物信息卡 -->
            <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:20px; overflow:visible; box-shadow:var(--shadow-lg); border:1px solid var(--border-light); margin-bottom:20px;">
                <!-- 封面背景 -->
                <?php if (!empty($contributor['cover'])): ?>
                    <div style="height:140px; background: url('<?php echo htmlspecialchars($contributor['cover']); ?>') center/cover no-repeat; position:relative; border-radius:20px 20px 0 0; z-index:0;">
                        <div style="position:absolute; bottom:0; left:0; right:0; height:60%; background:linear-gradient(to top, rgba(0,0,0,0.6), transparent);"></div>
                    </div>
                <?php else: ?>
                    <div style="height:100px; background: linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft)); border-radius:20px 20px 0 0;"></div>
                <?php endif; ?>

                <!-- 头像与信息 (使用 relative + z-index 确保不被背景遮挡) -->
                <div style="padding:0 20px 24px; text-align:center; position:relative; <?php echo !empty($contributor['cover']) ? 'margin-top:-50px;' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($contributor['avatar'] ?: 'assets/images/default-avatar.png'); ?>" 
                         style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:4px solid var(--surface); box-shadow:var(--shadow-md); margin-bottom:12px; position:relative; z-index:3;">
                    <h2 style="font-size:1.5rem; font-weight:800; margin:0 0 4px; word-break:break-word; position:relative; z-index:2;"><?php echo htmlspecialchars($contributor['name']); ?></h2>
                    <?php if ($contributor['subtitle']): ?>
                        <p style="color:var(--mc-green); font-size:0.9rem; font-weight:600; margin:0 0 12px; position:relative; z-index:2;"><?php echo htmlspecialchars($contributor['subtitle']); ?></p>
                    <?php endif; ?>
                    
                    <?php if (isAdmin()): ?>
                        <div style="display:flex; gap:8px; justify-content:center; margin-top:12px; position:relative; z-index:2;">
                            <a href="contributor_edit.php?id=<?php echo $contributor['id']; ?>" class="btn-auth" style="padding:6px 14px; font-size:0.8rem; text-decoration:none;">编辑</a>
                            <a href="contributor_delete.php?id=<?php echo $contributor['id']; ?>" class="btn-auth" style="background:#e74c3c; padding:6px 14px; font-size:0.8rem; text-decoration:none;" onclick="return confirm('确定删除？');">删除</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 下部：3D皮肤模型区 -->
            <div style="background:#0d1117; border-radius:16px; overflow:hidden; box-shadow:var(--shadow-lg); position:relative;">
                <div style="padding:16px; background:#161b22; display:flex; align-items:center; gap:6px; border-bottom:1px solid #30363d;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#ff5f56;"></span>
                    <span style="width:10px; height:10px; border-radius:50%; background:#ffbd2e;"></span>
                    <span style="width:10px; height:10px; border-radius:50%; background:#27c93f;"></span>
                    <span style="margin-left:auto; color:#8b949e; font-size:0.75rem;">skin viewer</span>
                </div>
                <div style="padding:20px; display:flex; align-items:center; justify-content:center; min-height:420px; background: url('<?php echo BASE_URL; ?>/assets/images/bj1.png') center/cover no-repeat;">
                    <div style="position:relative; width:100%; backdrop-filter:blur(4px); background:rgba(0,0,0,0.3); border-radius:12px; padding:10px;">
                        <?php if (!empty($contributor['skin_url'])): ?>
                            <canvas id="skinViewer" width="280" height="350" style="max-width:100%; height:auto; display:block; margin:0 auto;"></canvas>
                            <img id="fallbackSkinImg" src="<?php echo htmlspecialchars($contributor['skin_url']); ?>" style="display:none; max-width:100%; border-radius:8px; margin-top:10px;" alt="皮肤预览">
                        <?php else: ?>
                            <p style="color:#fff; text-align:center; background:rgba(0,0,0,0.6); padding:12px; border-radius:8px;">暂无皮肤展示</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧：详细简介 -->
        <div style="flex:1; min-width:0;">
            <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:30px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); line-height:1.9; word-break:break-word; min-height:400px;">
                <?php if (!empty(trim($contributor['description']))): ?>
                    <?php echo nl2br(htmlspecialchars($contributor['description'])); ?>
                <?php else: ?>
                    <p style="color:var(--text-secondary); text-align:center;">暂无详细介绍</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 3D 皮肤查看器依赖 -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/skinview3d@3.0.0-alpha.1/bundles/skinview3d.bundle.js"></script>
<script>
(function() {
    var skinUrl = '<?php echo htmlspecialchars($contributor['skin_url'] ?? ''); ?>';
    if (!skinUrl) return;

    var canvas = document.getElementById('skinViewer');
    var fallbackImg = document.getElementById('fallbackSkinImg');

    function initViewer(skinData) {
        if (typeof THREE === 'undefined' || typeof skinview3d === 'undefined') {
            canvas.style.display = 'none';
            fallbackImg.style.display = 'block';
            return;
        }
        try {
            new skinview3d.SkinViewer({
                canvas: canvas,
                width: canvas.width,
                height: canvas.height,
                skin: skinData
            });
            canvas.style.display = 'block';
            fallbackImg.style.display = 'none';
        } catch(e) {
            canvas.style.display = 'none';
            fallbackImg.style.display = 'block';
        }
    }

    // 外部链接走代理（如果域名不是本站）
    var currentHost = window.location.hostname;
    if (skinUrl.indexOf('http') === 0 && skinUrl.indexOf(currentHost) === -1) {
        fetch('skin_proxy.php?url=' + encodeURIComponent(skinUrl))
        .then(resp => resp.text())
        .then(text => {
            if (text.startsWith('data:image')) {
                initViewer(text);
            } else {
                initViewer(skinUrl);
            }
        })
        .catch(() => initViewer(skinUrl));
    } else {
        initViewer(skinUrl);
    }
})();
</script>

<style>
    @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

    @media (max-width: 768px) {
        .contributor-page { padding: 80px 16px 40px !important; }
        .contributor-layout { flex-direction: column !important; gap: 20px !important; }
        .contributor-sidebar { width: 100% !important; position: static !important; }
        .contributor-sidebar [style*="height:140px"] { height: 120px !important; }
        .contributor-sidebar img { width: 80px !important; height: 80px !important; }
        #skinViewer { max-width: 100% !important; height: auto !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>