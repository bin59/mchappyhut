<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '人物志';
require_once __DIR__ . '/../../header.php';

$stmt = $conn->query("SELECT * FROM figures ORDER BY sort_order ASC, id ASC");
$figures = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!-- 顶部横幅 -->
<div style="position:relative; width:100%; min-height:300px; display:flex; align-items:center; justify-content:flex-end; padding:0 40px; background: linear-gradient(to bottom, rgba(10,14,8,0.35), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/assets/images/ag5.png') center/cover no-repeat; border-bottom:4px solid #c9a84c;">
    <div style="text-align:right; color:#fff; z-index:1;">
        <h1 style="font-size:clamp(2.5rem,5vw,3.8rem); font-weight:800; margin-bottom:8px; animation: fadeInDown 0.8s ease both;">人物志</h1>
        <p style="font-size:1.2rem; opacity:0.9; animation: fadeInUp 0.8s ease 0.2s both;">记录方块世界的传奇人物</p>
    </div>
</div>

<div style="max-width:1800px; margin:0 auto; padding:40px 20px 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; animation: fadeIn 0.6s ease 0.3s both;">
        <h2 style="font-size:2rem; font-weight:700; border-left:6px solid var(--mc-green); padding-left:16px;"> 人物传记</h2>
        <?php if (isAdmin()): ?>
            <a href="edit.php" class="btn-auth" style="text-decoration:none; padding:12px 28px; font-size:1rem;"><i class="fas fa-plus"></i> 添加人物</a>
        <?php endif; ?>
    </div>

    <?php if (empty($figures)): ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:80px 20px; text-align:center; color:var(--text-secondary); border:1px solid var(--border-light); animation: fadeIn 0.5s ease 0.4s both;">暂无人物记载</div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:30px;">
            <?php $index = 0; foreach ($figures as $figure): ?>
                <a href="detail.php?id=<?php echo $figure['id']; ?>" style="text-decoration:none; color:inherit; animation: fadeInUp 0.6s ease <?php echo 0.1 * $index; ?>s both;">
                    <!-- 书皮封面卡片 -->
                    <div class="figure-cover" style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:16px; overflow:hidden; box-shadow:var(--shadow-md); transition: all 0.3s ease; height:100%; display:flex; flex-direction:column; position:relative;">
                        <!-- 封面背景 (使用头像或纯色渐变) -->
                        <div style="height:240px; background: <?php echo $figure['cover'] ? "url('".htmlspecialchars($figure['cover'])."') center/cover no-repeat" : 'linear-gradient(135deg, #2d5a3d, #4F8A30, #D4942B)'; ?>; position:relative;">
                            <div style="position:absolute; bottom:0; left:0; right:0; height:60%; background:linear-gradient(to top, rgba(0,0,0,0.6), transparent);"></div>
                            <!-- 头像叠加在封面上 -->
                            <div style="position:absolute; bottom:-40px; left:50%; transform:translateX(-50%);">
                                <img src="<?php echo htmlspecialchars($figure['avatar'] ?: 'assets/images/default-avatar.png'); ?>" style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:4px solid var(--surface); box-shadow:var(--shadow-md);">
                            </div>
                        </div>
                        <!-- 书名和副标题 -->
                        <div style="padding:50px 20px 20px; text-align:center; flex:1; display:flex; flex-direction:column; justify-content:center;">
                            <h3 style="font-size:1.4rem; font-weight:700; margin:0 0 6px; word-break:break-word;"><?php echo htmlspecialchars($figure['name']); ?></h3>
                            <?php if ($figure['subtitle']): ?>
                                <p style="color:var(--text-secondary); font-size:0.9rem; margin:0; line-height:1.4;"><?php echo htmlspecialchars($figure['subtitle']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php $index++; endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .figure-cover:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(0,0,0,0.18); }
    @keyframes fadeInDown { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    @media (max-width: 768px) {
        .figure-cover { margin-bottom: 16px; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>