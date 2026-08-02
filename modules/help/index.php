<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '帮助中心';
require_once __DIR__ . '/../../header.php';

$stmt = $conn->query("SELECT * FROM help_articles ORDER BY sort_order ASC, id ASC");
$articles = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!-- 顶部横幅（加高，标题左对齐，淡入动画） -->
<div style="position:relative; width:100%; min-height:320px; display:flex; align-items:center; justify-content:flex-start; padding:0 40px; background: linear-gradient(to bottom, rgba(10,14,8,0.35), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/assets/images/ag7.png') center/cover no-repeat; border-bottom:4px solid #c9a84c; animation: fadeInDown 0.8s ease both;">
    <div style="text-align:left; color:#fff; z-index:1;">
        <h1 style="font-size:clamp(2.5rem,5vw,3.5rem); font-weight:800; margin-bottom:8px;">帮助中心</h1>
        <p style="font-size:1.2rem; opacity:0.9;">寻找答案，快速上手</p>
    </div>
</div>

<!-- 帮助文档容器（横排三列，依次飞入） -->
<div style="max-width:1300px; margin:0 auto; padding:40px 20px 60px;">

    <?php if (isAdmin()): ?>
    <div style="display:flex; justify-content:flex-end; margin-bottom:24px;">
        <a href="edit.php" class="btn-auth" style="text-decoration:none;"><i class="fas fa-plus"></i> 添加文档</a>
    </div>
    <?php endif; ?>

    <?php if (empty($articles)): ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:20px; padding:60px; text-align:center; color:var(--text-secondary); border:1px solid var(--border-light); animation: fadeInUp 0.6s ease 0.2s both;">
            <i class="far fa-file-alt" style="font-size:2.5rem; opacity:0.5; margin-bottom:15px; display:block;"></i>
            暂无帮助文档
        </div>
    <?php else: ?>
        <div class="help-grid" style="
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        ">
            <?php $index = 0; foreach ($articles as $article): ?>
            <a href="detail.php?id=<?php echo $article['id']; ?>" style="text-decoration:none; color:inherit;">
                <div class="help-card" style="
                    background: var(--surface-glass);
                    backdrop-filter: blur(12px);
                    border: 1px solid var(--border-light);
                    border-radius: 18px;
                    padding: 28px 24px;
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                    transition: all 0.25s ease;
                    box-shadow: var(--shadow-sm);
                    animation: fadeInUp 0.6s ease both;
                    animation-delay: <?php echo 0.1 * $index; ?>s;
                ">
                    <div style="
                        width: 48px;
                        height: 48px;
                        background: linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft));
                        border-radius: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #fff;
                        font-size: 1.4rem;
                        margin-bottom: 18px;
                    ">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h3 style="font-size:1.2rem; font-weight:700; margin:0 0 10px; line-height:1.4;">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </h3>
                    <p style="
                        color: var(--text-secondary);
                        font-size:0.9rem;
                        line-height:1.5;
                        flex:1;
                        margin:0 0 15px;
                        display: -webkit-box;
                        -webkit-line-clamp: 3;
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                    ">
                        <?php echo mb_substr(strip_tags($article['content']), 0, 100); ?>
                    </p>
                    <div style="
                        font-size:0.8rem;
                        color: var(--mc-green);
                        font-weight:600;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        margin-top: auto;
                    ">
                        阅读更多 <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i>
                    </div>
                </div>
            </a>
            <?php $index++; endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .help-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.1);
        border-color: var(--mc-green);
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .help-grid {
            grid-template-columns: 1fr !important;
        }
        .help-card {
            padding: 20px;
        }
    }

    @media (max-width: 480px) {
        .help-grid {
            gap: 16px;
        }
    }
</style>

<?php require_once __DIR__ . '/../../footer.php'; ?>