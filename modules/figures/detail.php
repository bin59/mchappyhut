<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM figures WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$figure = $stmt->get_result()->fetch_assoc();
if (!$figure) {
    redirect(BASE_URL . '/modules/figures/');
}
$pageTitle = htmlspecialchars($figure['name']) . ' - 人物志';
require_once __DIR__ . '/../../header.php';
?>

<div class="figure-detail" style="animation: fadeIn 0.6s ease;">

    <!-- 顶部全宽横幅 (使用cover字段) -->
    <div style="position:relative; width:100%; height:45vh; max-height:500px; background: <?php echo $figure['cover'] ? "url('".htmlspecialchars($figure['cover'])."') center/cover no-repeat" : 'linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft))'; ?>; display:flex; align-items:flex-end; border-bottom:4px solid var(--mc-green);">
        <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.5), transparent 60%);"></div>
        <div style="position:relative; z-index:1; padding:0 40px 30px; width:100%;">
            <h1 style="font-size:clamp(2rem, 4vw, 3rem); font-weight:800; color:#fff; text-shadow:0 2px 10px rgba(0,0,0,0.5); margin:0;"><?php echo htmlspecialchars($figure['name']); ?></h1>
        </div>
    </div>

    <!-- 主内容区：左侧人物卡片 + 右侧正文 -->
    <div style="max-width:1600px; margin:0 auto; padding:30px 20px 60px; display:flex; gap:30px; align-items:flex-start;">
        <!-- 左侧人物信息卡 -->
        <div class="figure-sidebar" style="flex-shrink:0; width:300px; position:sticky; top:100px;">
            <div style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:20px; overflow:hidden; box-shadow:var(--shadow-lg); border:1px solid var(--border-light);">
                <!-- 上半部分：封面区域 -->
                <div style="height:160px; background: <?php echo $figure['cover'] ? "url('".htmlspecialchars($figure['cover'])."') center/cover no-repeat" : 'linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft))'; ?>; position:relative;">
                    <div style="position:absolute; bottom:0; left:0; right:0; height:50%; background:linear-gradient(to top, rgba(0,0,0,0.5), transparent);"></div>
                    <div style="position:absolute; bottom:-40px; left:50%; transform:translateX(-50%);">
                        <img src="<?php echo htmlspecialchars($figure['avatar'] ?: 'assets/images/default-avatar.png'); ?>" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:4px solid var(--surface); box-shadow:var(--shadow-md);">
                    </div>
                </div>
                <!-- 下半部分：名字和副标题 -->
                <div style="padding:60px 20px 24px; text-align:center;">
                    <h2 style="font-size:1.8rem; font-weight:800; margin:0 0 8px; word-break:break-word;"><?php echo htmlspecialchars($figure['name']); ?></h2>
                    <?php if ($figure['subtitle']): ?>
                        <p style="color:var(--mc-green); font-size:1rem; font-weight:600; margin:0;"><?php echo htmlspecialchars($figure['subtitle']); ?></p>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <div style="margin-top:20px; display:flex; gap:8px; justify-content:center;">
                            <a href="edit.php?id=<?php echo $figure['id']; ?>" class="btn-auth" style="padding:8px 16px; font-size:0.85rem;">编辑</a>
                            <a href="delete.php?id=<?php echo $figure['id']; ?>" class="btn-auth" style="background:#e74c3c; padding:8px 16px; font-size:0.85rem;" onclick="return confirm('确定删除？');">删除</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 右侧正文区块（渲染HTML） -->
        <div style="flex:1; min-width:0;">
            <?php if (!empty(trim(strip_tags($figure['description'])))): ?>
                <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:30px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); line-height:1.9; word-break:break-word;">
                    <?php echo $figure['description']; // 直接输出HTML，Quill 产生的安全内容 ?>
                </div>
            <?php else: ?>
                <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:60px 20px; text-align:center; color:var(--text-secondary); border:1px solid var(--border-light);">
                    暂无详细介绍
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .figure-detail img { max-width:100%; height:auto; border-radius:8px; margin:12px 0; }
    @media (max-width: 768px) {
        .figure-detail { padding-top: 80px; }
        .figure-sidebar { width: 100% !important; position: static !important; margin-bottom: 20px; }
        [style*="display:flex; gap:30px"] { flex-direction: column; }
        .figure-sidebar [style*="height:160px"] { height: 120px !important; }
        .figure-sidebar img { width: 80px !important; height: 80px !important; bottom: -30px !important; }
        .figure-sidebar h2 { font-size: 1.5rem !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>