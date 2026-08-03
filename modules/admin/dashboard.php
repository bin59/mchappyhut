<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();
$pageTitle = '管理控制台';
require_once __DIR__ . '/../../header.php';

// 统计数据
$userCount = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
$postCount = $conn->query("SELECT COUNT(*) AS cnt FROM community_posts")->fetch_assoc()['cnt'];
$announceCount = $conn->query("SELECT COUNT(*) AS cnt FROM announcements")->fetch_assoc()['cnt'];
$eventCount = $conn->query("SELECT COUNT(*) AS cnt FROM events")->fetch_assoc()['cnt'];
$serverCount = $conn->query("SELECT COUNT(*) AS cnt FROM servers")->fetch_assoc()['cnt'];
$feedbackCount = $conn->query("SELECT COUNT(*) AS cnt FROM form_answers")->fetch_assoc()['cnt'];
$logCount = $conn->query("SELECT COUNT(*) AS cnt FROM player_logs")->fetch_assoc()['cnt'];
?>

<div style="max-width:100%; margin:0; padding:100px 0 40px; animation: fadeIn 0.5s ease;">

    <!-- 顶部欢迎区 -->
    <div style="max-width:1600px; margin:0 auto 32px; padding:0 24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <h1 style="font-size:2.4rem; font-weight:800; margin:0; letter-spacing:-0.02em;">管理控制台</h1>
                <p style="color:var(--text-secondary); margin:6px 0 0;">系统概览 · 快速入口</p>
            </div>
            <div style="display:flex; gap:10px;">
                <span style="font-size:0.9rem; color:var(--text-secondary); align-self:center;"><?php echo date('Y-m-d H:i'); ?></span>
                <a href="<?php echo BASE_URL; ?>/modules/admin/users.php" class="btn-auth" style="text-decoration:none; padding:10px 20px;">用户管理</a>
            </div>
        </div>
    </div>

    <!-- 主内容区：全宽网格，卡片铺开 -->
    <div style="max-width:1600px; margin:0 auto; padding:0 24px; display:grid; grid-template-columns: 1fr 0.6fr; gap:24px;">

        <!-- 左列：核心统计 + 图表占位 -->
        <div style="display:flex; flex-direction:column; gap:24px;">
            <!-- 核心数据卡片（一行3个） -->
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px;">
                <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:22px 20px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:0.85rem; color:var(--text-secondary);">注册用户</div>
                            <div style="font-size:2.2rem; font-weight:800; color:var(--text);"><?php echo $userCount; ?></div>
                        </div>
                        <div style="width:40px; height:40px; border-radius:12px; background:rgba(79,138,48,0.12); display:flex; align-items:center; justify-content:center; color:var(--mc-green);"><i class="fas fa-users fa-lg"></i></div>
                    </div>
                </div>
                <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:22px 20px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:0.85rem; color:var(--text-secondary);">社区帖子</div>
                            <div style="font-size:2.2rem; font-weight:800; color:var(--text);"><?php echo $postCount; ?></div>
                        </div>
                        <div style="width:40px; height:40px; border-radius:12px; background:rgba(212,148,43,0.12); display:flex; align-items:center; justify-content:center; color:#D4942B;"><i class="fas fa-file-alt fa-lg"></i></div>
                    </div>
                </div>
                <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:22px 20px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:0.85rem; color:var(--text-secondary);">公告</div>
                            <div style="font-size:2.2rem; font-weight:800; color:var(--text);"><?php echo $announceCount; ?></div>
                        </div>
                        <div style="width:40px; height:40px; border-radius:12px; background:rgba(52,152,219,0.12); display:flex; align-items:center; justify-content:center; color:#3498db;"><i class="fas fa-bullhorn fa-lg"></i></div>
                    </div>
                </div>
            </div>

            <!-- 图表占位区（未来可集成真实图表） -->
            <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:24px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm); min-height:240px; display:flex; align-items:center; justify-content:center; color:var(--text-secondary);">
                <div style="text-align:center;">
                    <i class="fas fa-chart-line" style="font-size:2.5rem; opacity:0.3; display:block; margin-bottom:12px;"></i>
                    <p style="margin:0;">数据趋势图（即将上线）</p>
                </div>
            </div>
        </div>

        <!-- 右列：次要统计 + 快捷工具 -->
        <div style="display:flex; flex-direction:column; gap:24px;">
            <!-- 活动、服务器、日志 -->
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:18px 16px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                    <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:4px;">活动</div>
                    <div style="font-size:1.8rem; font-weight:800; color:var(--text);"><?php echo $eventCount; ?></div>
                </div>
                <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:18px 16px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                    <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:4px;">服务器</div>
                    <div style="font-size:1.8rem; font-weight:800; color:var(--text);"><?php echo $serverCount; ?></div>
                </div>
                <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:18px 16px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                    <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:4px;">玩家日志</div>
                    <div style="font-size:1.8rem; font-weight:800; color:var(--text);"><?php echo $logCount; ?></div>
                </div>
            </div>

            <!-- 工单卡片 -->
            <div class="dash-card" style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:20px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-size:0.85rem; color:var(--text-secondary);">反馈工单</div>
                        <div style="font-size:2rem; font-weight:800; color:var(--text);"><?php echo $feedbackCount; ?></div>
                    </div>
                    <div style="width:40px; height:40px; border-radius:12px; background:rgba(230,126,34,0.12); display:flex; align-items:center; justify-content:center; color:#e67e22;"><i class="fas fa-ticket-alt fa-lg"></i></div>
                </div>
                <a href="<?php echo BASE_URL; ?>/modules/feedback/index.php" class="btn-auth" style="width:100%; justify-content:center; margin-top:16px; padding:10px; background:rgba(230,126,34,0.1); color:#e67e22; border:1px solid rgba(230,126,34,0.25); text-decoration:none;">处理工单</a>
            </div>

            <!-- 快捷操作面板 -->
            <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:20px; border:1px solid var(--border-light); box-shadow:var(--shadow-sm);">
                <h3 style="margin:0 0 14px; font-size:1rem; font-weight:700;">快捷管理</h3>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                    <a href="<?php echo BASE_URL; ?>/modules/announcements/edit.php" class="btn-auth" style="text-decoration:none; padding:10px; background:rgba(52,152,219,0.1); border:1px solid rgba(52,152,219,0.2); color:#3498db; justify-content:center; font-size:0.85rem;">发布公告</a>
                    <a href="<?php echo BASE_URL; ?>/modules/events/edit.php" class="btn-auth" style="text-decoration:none; padding:10px; background:rgba(155,89,182,0.1); border:1px solid rgba(155,89,182,0.2); color:#9b59b6; justify-content:center; font-size:0.85rem;">发布活动</a>
                    <a href="<?php echo BASE_URL; ?>/modules/servers/edit.php" class="btn-auth" style="text-decoration:none; padding:10px; background:rgba(26,188,156,0.1); border:1px solid rgba(26,188,156,0.2); color:#1abc9c; justify-content:center; font-size:0.85rem;">添加服务器</a>
                    <a href="<?php echo BASE_URL; ?>/modules/rules/edit.php" class="btn-auth" style="text-decoration:none; padding:10px; background:rgba(231,76,60,0.1); border:1px solid rgba(231,76,60,0.2); color:#e74c3c; justify-content:center; font-size:0.85rem;">添加规则</a>
                    <a href="<?php echo BASE_URL; ?>/modules/admin/player_logs.php" class="btn-auth" style="text-decoration:none; padding:10px; background:rgba(79,138,48,0.1); border:1px solid rgba(79,138,48,0.2); color:var(--mc-green); justify-content:center; font-size:0.85rem;">日志管理</a>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .dash-card { transition: all 0.25s ease; }
    .dash-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.1); border-color: var(--mc-green); }
    @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

    /* 手机端：全部变为单列 */
    @media (max-width: 768px) {
        body { padding: 0 12px; }
        h1 { font-size: 1.8rem !important; }
        [style*="grid-template-columns: 1fr 0.6fr"] { grid-template-columns: 1fr !important; }
        [style*="grid-template-columns: repeat(3, 1fr)"] { grid-template-columns: 1fr !important; }
        [style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; }
        .dash-card { padding: 16px !important; }
        .dash-card [style*="font-size:2.2rem"] { font-size: 1.6rem !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>