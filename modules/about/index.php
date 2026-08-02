<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '关于我们';
require_once __DIR__ . '/../../header.php';

// 获取关于我们内容
$aboutStmt = $conn->query("SELECT content FROM about LIMIT 1");
$about = $aboutStmt->fetch_assoc();

// 获取贡献者列表
$contributors = $conn->query("SELECT * FROM contributors ORDER BY sort_order ASC, id ASC");
?>

<!-- 全屏背景 bj1.png，叠加渐变和毛玻璃（透明度随主题变化） -->
<div style="position:relative; width:100%; min-height:100vh; background: linear-gradient(to bottom, var(--bg), transparent), url('<?php echo BASE_URL; ?>/assets/images/bj1.png') center/cover no-repeat fixed; backdrop-filter: blur(6px);">

    <!-- 顶部横幅区域 -->
    <div style="padding:120px 20px 40px; text-align:center; position:relative; z-index:1;">
        <h1 style="font-size:3rem; font-weight:800; color:var(--text); margin-bottom:16px;">关于方块人快乐小窝</h1>
        <?php if ($about && $about['content']): ?>
            <div style="max-width:1200px; margin:0 auto; background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:20px; padding:32px; box-shadow:var(--shadow-lg); text-align:left; line-height:1.8; word-break:break-word;">
                <?php echo $about['content']; ?>
            </div>
        <?php else: ?>
            <p style="color:var(--text-secondary);">暂无介绍内容。</p>
        <?php endif; ?>
    </div>

    <!-- 贡献者区域 -->
    <div style="width:100%; max-width:1200px; margin:0 auto; padding:0 20px 60px; position:relative; z-index:1;">
        <h2 style="text-align:center; font-size:2.2rem; font-weight:700; margin-bottom:40px; color:var(--text);">🌍 世界贡献者</h2>
        <?php if ($contributors->num_rows === 0): ?>
            <p style="text-align:center; color:var(--text-secondary);">暂无贡献者。</p>
        <?php else: ?>
            <div style="background:#0d1117; border-radius:16px; overflow:hidden; box-shadow:var(--shadow-lg);">
                <div style="background:#161b22; padding:12px 20px; display:flex; align-items:center; gap:8px; border-bottom:1px solid #30363d;">
                    <span style="width:12px; height:12px; border-radius:50%; background:#ff5f56;"></span>
                    <span style="width:12px; height:12px; border-radius:50%; background:#ffbd2e;"></span>
                    <span style="width:12px; height:12px; border-radius:50%; background:#27c93f;"></span>
                    <span style="margin-left:auto; color:#8b949e; font-size:0.85rem;">contributors@blockman:~$ ls</span>
                </div>
                <div style="padding:32px; display:flex; flex-wrap:wrap; justify-content:center; gap:32px;">
                    <?php while ($contributor = $contributors->fetch_assoc()): ?>
                        <a href="contributor_detail.php?id=<?php echo $contributor['id']; ?>" style="text-decoration:none; color:#c9d1d9; display:flex; flex-direction:column; align-items:center; gap:10px; transition: transform 0.2s;"
                           onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            <div style="border:2px solid #30363d; border-radius:50%; padding:4px; background:#0d1117;">
                                <img src="<?php echo htmlspecialchars($contributor['avatar'] ?: 'assets/images/default-avatar.png'); ?>" style="width:90px; height:90px; border-radius:50%; object-fit:cover; display:block;">
                            </div>
                            <span style="font-weight:700; font-size:1rem; color:#e6edf3;"><?php echo htmlspecialchars($contributor['name']); ?></span>
                            <?php if ($contributor['subtitle']): ?>
                                <span style="font-size:0.85rem; color:#8b949e;"><?php echo htmlspecialchars($contributor['subtitle']); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
            <div style="text-align:center; margin-top:30px; display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
                <a href="edit.php" class="btn-auth" style="text-decoration:none; padding:10px 24px; font-size:0.9rem; width:auto;">编辑关于我们</a>
                <a href="contributor_edit.php" class="btn-auth" style="text-decoration:none; padding:10px 24px; font-size:0.9rem; width:auto;">添加贡献者</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        h1 { font-size: 2.2rem !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>