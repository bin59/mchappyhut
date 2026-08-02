<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM help_articles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
if (!$article) {
    redirect(BASE_URL . '/modules/help/');
}
$pageTitle = htmlspecialchars($article['title']) . ' - 帮助中心';
require_once __DIR__ . '/../../header.php';
?>

<div style="animation: fadeIn 0.6s ease;">
    <!-- 顶部标题横幅 -->
    <div style="position:relative; width:100%; background:linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft)); padding:60px 20px; text-align:center;">
        <h1 style="font-size:clamp(2rem,4vw,3rem); font-weight:800; color:#fff; margin:0;"><?php echo htmlspecialchars($article['title']); ?></h1>
        <div style="margin-top:10px; color:rgba(255,255,255,0.8); font-size:0.9rem;">
            <i class="far fa-clock"></i> 更新于 <?php echo date('Y-m-d', strtotime($article['updated_at'])); ?>
        </div>
    </div>

    <!-- 内容区 -->
    <div style="max-width:1000px; margin:0 auto; padding:40px 20px 60px;">
        <?php if (!empty(trim(strip_tags($article['content'])))): ?>
            <div class="help-content" style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:40px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); line-height:1.9; word-break:break-word;">
                <?php echo $article['content']; ?>
            </div>
        <?php else: ?>
            <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:16px; padding:60px 20px; text-align:center; color:var(--text-secondary); border:1px solid var(--border-light);">
                暂无详细内容
            </div>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
            <div style="margin-top:30px; display:flex; gap:12px; justify-content:center;">
                <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn-auth" style="text-decoration:none;">编辑文档</a>
                <a href="delete.php?id=<?php echo $article['id']; ?>" class="btn-auth" style="background:#e74c3c; text-decoration:none;" onclick="return confirm('确定删除？');">删除文档</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .help-content img { max-width:100%; height:auto; border-radius:8px; margin:12px 0; }
    .help-content h2, .help-content h3 { margin-top:24px; }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>