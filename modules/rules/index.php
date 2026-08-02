<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '行为准则';
require_once __DIR__ . '/../../header.php';

$stmt = $conn->query("SELECT * FROM rules ORDER BY sort_order ASC, created_at ASC");
$rules = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!-- 顶部横幅 + 动画 -->
<div style="position:relative; width:100%; min-height:320px; display:flex; align-items:center; background: linear-gradient(to bottom, rgba(10,14,8,0.3), rgba(10,14,8,0.75)), url('<?php echo BASE_URL; ?>/assets/images/ag2.png') center/cover no-repeat; margin-bottom:0; border-bottom:4px solid #c9a84c;">
    <div style="max-width:1300px; margin:0 auto; width:100%; padding:0 30px;">
        <h1 style="font-size:clamp(2.4rem, 5vw, 3.5rem); font-weight:800; color:#fff; margin-bottom:10px; animation: fadeInDown 0.8s ease both;">
            行为准则
        </h1>
        <p style="color:rgba(255,255,255,0.9); font-size:1.15rem; animation: fadeInUp 0.8s ease 0.2s both;">
            方块人快乐小窝 · 社区规范
        </p>
    </div>
</div>

<!-- 主内容区 -->
<div style="background: var(--bg); padding: 30px 0 60px;">
    <div style="max-width:1300px; margin:0 auto; padding:0 20px;">
        <!-- 工具栏（轻微动画） -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; animation: fadeIn 0.6s ease 0.4s both;">
            <h2 style="font-size:1.8rem; font-weight:700; border-left:6px solid var(--mc-green); padding-left:16px;">全部规则</h2>
            <?php if (isAdmin()): ?>
                <a href="edit.php" class="btn-auth" style="text-decoration:none; padding:12px 28px; font-size:1rem;"><i class="fas fa-plus"></i> 添加规则</a>
            <?php endif; ?>
        </div>

        <?php if (empty($rules)): ?>
            <div style="text-align:center; padding:80px; background:var(--surface-glass); backdrop-filter:blur(12px); border-radius:12px; color:var(--text-secondary); animation: fadeIn 0.5s ease 0.5s both;">暂无规则发布</div>
        <?php else: ?>
            <!-- 规则条目列表，每个条目有独立动画延迟 -->
            <div style="display:flex; flex-direction:column; gap:8px;">
                <?php foreach ($rules as $index => $rule): ?>
                    <a href="detail.php?id=<?php echo $rule['id']; ?>" style="text-decoration:none; color:inherit; animation: fadeInUp 0.5s ease <?php echo 0.1 * ($index + 1); ?>s both;">
                        <div class="rule-row" style="display:flex; align-items:center; justify-content:space-between; padding:18px 28px; background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid var(--border-light); border-radius:10px; transition: all 0.3s ease; gap:16px;">
                            <div style="display:flex; align-items:center; gap:20px; flex:1; min-width:0;">
                                <span style="font-size:1.2rem; font-weight:600; color:var(--mc-green); width:36px; text-align:center;"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
                                <div style="min-width:0;">
                                    <h3 style="font-size:1.2rem; font-weight:600; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($rule['title']); ?></h3>
                                    <?php if (!empty($rule['subtitle'])): ?>
                                        <p style="color:var(--text-secondary); font-size:0.9rem; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($rule['subtitle']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px; flex-shrink:0;">
                                <?php if (!empty($rule['tag'])): ?>
                                    <span style="background:var(--mc-green); color:#fff; padding:3px 12px; border-radius:16px; font-size:0.8rem;"><?php echo htmlspecialchars($rule['tag']); ?></span>
                                <?php endif; ?>
                                <span style="font-size:0.85rem; color:var(--text-tertiary);"><?php echo date('Y-m-d', strtotime($rule['created_at'])); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* 动画关键帧 */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(25px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 规则行悬停效果 */
    .rule-row:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        background: var(--surface);
    }

    /* 手机适配 */
    @media (max-width: 768px) {
        .rule-row {
            flex-direction: column;
            align-items: flex-start !important;
            padding: 16px 20px;
        }
        .rule-row > div:first-child {
            flex-direction: row;
            align-items: center;
            width: 100%;
        }
        .rule-row > div:last-child {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .rule-row h3 {
            font-size: 1.05rem !important;
            white-space: normal !important;
        }
        .rule-row p {
            white-space: normal !important;
        }
    }
</style>

<?php require_once __DIR__ . '/../../footer.php'; ?>