<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '玩家团体';
require_once __DIR__ . '/../../header.php';

$stmt = $conn->query("SELECT * FROM `groups` ORDER BY created_at DESC");
$groups = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!-- 顶部横幅 (ag6.png) -->
<div style="position:relative; width:100%; min-height:300px; display:flex; align-items:center; justify-content:flex-end; padding:0 40px; background: linear-gradient(to bottom, rgba(10,14,8,0.35), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/assets/images/ag6.png') center/cover no-repeat; border-bottom:4px solid #c9a84c;">
    <div style="text-align:right; color:#fff; z-index:1;">
        <h1 style="font-size:clamp(2.5rem,5vw,3.8rem); font-weight:800; margin-bottom:8px; animation: fadeInDown 0.8s ease both;">玩家团体</h1>
        <p style="font-size:1.2rem; opacity:0.9; animation: fadeInUp 0.8s ease 0.2s both;">志同道合，一起冒险</p>
    </div>
</div>

<div style="max-width:1800px; margin:0 auto; padding:40px 20px 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; animation: fadeIn 0.6s ease 0.3s both;">
        <h2 style="font-size:2rem; font-weight:700; border-left:6px solid var(--mc-green); padding-left:16px;">全部团体</h2>
        <?php if (isAdmin()): ?>
            <a href="edit.php" class="btn-auth" style="text-decoration:none; padding:12px 28px; font-size:1rem;"><i class="fas fa-plus"></i> 创建团体</a>
        <?php endif; ?>
    </div>

    <?php if (empty($groups)): ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:80px 20px; text-align:center; color:var(--text-secondary); border:1px solid var(--border-light); animation: fadeIn 0.5s ease 0.4s both;">暂无玩家团体</div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:28px;">
            <?php $index = 0; foreach ($groups as $group): ?>
                <a href="detail.php?id=<?php echo $group['id']; ?>" style="text-decoration:none; color:inherit; animation: fadeInUp 0.6s ease <?php echo 0.1 * $index; ?>s both;">
                    <div class="group-card" style="background:var(--surface-glass); backdrop-filter:blur(16px); border-radius:20px; overflow:hidden; border:1px solid var(--border-light); transition: all 0.3s ease; box-shadow:var(--shadow-sm); height:100%; display:flex; flex-direction:column;">
                        <?php if (!empty($group['cover'])): ?>
                            <img src="<?php echo htmlspecialchars($group['cover']); ?>" style="width:100%; height:200px; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:200px; background: linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:2.5rem; opacity:0.8;"><i class="fas fa-users"></i></div>
                        <?php endif; ?>
                        
                        <div style="padding:20px 24px; flex:1; display:flex; flex-direction:column;">
                            <h3 style="font-size:1.4rem; font-weight:700; margin:0 0 6px;"><?php echo htmlspecialchars($group['name']); ?></h3>
                            <?php if (!empty($group['subtitle'])): ?>
                                <p style="color:var(--text-secondary); font-size:0.95rem; margin:0 0 12px;"><?php echo htmlspecialchars($group['subtitle']); ?></p>
                            <?php endif; ?>
                            
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px; margin-top:auto;">
                                <?php if (!empty($group['leader_avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($group['leader_avatar']); ?>" style="width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid var(--surface);">
                                <?php else: ?>
                                    <div style="width:38px; height:38px; border-radius:50%; background:var(--surface-alt); display:flex; align-items:center; justify-content:center; color:var(--text-tertiary);"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                                <div>
                                    <span style="font-size:0.9rem; font-weight:600;"><?php echo htmlspecialchars($group['leader_name'] ?: '未知'); ?></span>
                                    <?php if (!empty($group['type'])): ?>
                                        <span style="display:inline-block; margin-left:8px; background:var(--mc-green); color:#fff; font-size:0.75rem; padding:2px 10px; border-radius:12px;"><?php echo htmlspecialchars($group['type']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div style="font-size:0.85rem; color:var(--text-tertiary); border-top:1px solid var(--border-light); padding-top:14px;">
                                创建于 <?php echo date('Y-m-d', strtotime($group['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php $index++; endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .group-card:hover { transform: translateY(-6px); box-shadow: 0 16px 36px rgba(0,0,0,0.12); border-color: var(--mc-green); }
    
    @keyframes fadeInDown { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

    @media (max-width: 768px) {
        .group-card { margin-bottom: 16px; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>