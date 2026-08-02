<?php
require_once __DIR__ . '/../../config.php';

$stmt = $conn->query("SELECT * FROM wechat_qr WHERE id = 1");
$wechat = $stmt->fetch_assoc();
$qr_image_url = $wechat['qr_image_url'] ?? '';

$pageTitle = '微信联系我们';
$isHomePage = false;
require_once __DIR__ . '/../../header.php';
?>

<style>
    .wechat-page {
        min-height: 100vh;
        background: #07C160; /* 微信绿 */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
    }
    .wechat-card {
        background: #fff;
        border-radius: 24px;
        padding: 40px 30px;
        max-width: 400px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .wechat-icon {
        font-size: 3.5rem;
        color: #07C160;
        margin-bottom: 10px;
    }
    .wechat-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #07C160;
        margin: 0 0 20px;
    }
    .wechat-qr {
        width: 220px;
        height: 220px;
        margin: 0 auto 20px;
        background: #f5f5f5;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .wechat-qr img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .wechat-qr .placeholder {
        font-size: 0.9rem;
        color: #aaa;
    }
    .admin-link {
        margin-top: 20px;
        font-size: 0.9rem;
    }
</style>

<div class="wechat-page">
    <div class="wechat-card">
        <div class="wechat-icon">
            <i class="fab fa-weixin"></i>
        </div>
        <h1 class="wechat-title">方块人微信小窝</h1>
        
        <div class="wechat-qr">
            <?php if ($qr_image_url): ?>
                <img src="<?php echo htmlspecialchars($qr_image_url); ?>" alt="微信二维码">
            <?php else: ?>
                <span class="placeholder">暂未上传二维码</span>
            <?php endif; ?>
        </div>
        
        <?php if (isAdmin()): ?>
            <div class="admin-link">
                <a href="admin.php" class="btn-auth" style="background:#07C160;">编辑二维码</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>