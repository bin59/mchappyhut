<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '社区中心';
require_once __DIR__ . '/../../header.php';

$catStmt = $conn->query("SELECT * FROM categories ORDER BY sort_order ASC");
$categories = $catStmt->fetch_all(MYSQLI_ASSOC);
$currentCat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];
$types = "";
if ($currentCat > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $currentCat;
    $types .= "i";
}

$countSql = "SELECT COUNT(*) AS total FROM community_posts p $where";
$countStmt = $conn->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

// 置顶帖子
$pinnedStmt = $conn->prepare("SELECT p.*, u.username, u.avatar, c.name AS cat_name FROM community_posts p JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_pinned = 1 ORDER BY p.created_at DESC LIMIT 5");
$pinnedStmt->execute();
$pinnedPosts = $pinnedStmt->get_result();

// 普通帖子列表
$listSql = "SELECT p.*, u.username, u.avatar, c.name AS cat_name FROM community_posts p JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.is_pinned DESC, p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= "ii";
$listStmt = $conn->prepare($listSql);
$listStmt->bind_param($types, ...$params);
$listStmt->execute();
$posts = $listStmt->get_result();
?>

<!-- 顶部横幅（淡入动画） -->
<div style="position:relative; width:100%; min-height:280px; display:flex; align-items:center; justify-content:flex-start; padding:0 40px; background: linear-gradient(to bottom, rgba(10,14,8,0.35), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/assets/images/ag4.png') center/cover no-repeat; margin-bottom:0; border-bottom:4px solid #c9a84c; animation: fadeInDown 0.8s ease both;">
    <div style="color:#fff; z-index:1;">
        <h1 style="font-size:clamp(2.5rem,5vw,3.8rem); font-weight:800; margin-bottom:8px;">玩家社区</h1>
        <p style="font-size:1.2rem; opacity:0.9;">分享你的方块故事</p>
    </div>
</div>

<div style="max-width:1800px; margin:0 auto; padding:40px 20px 60px; display:flex; gap:30px; align-items:flex-start;">
    <!-- 左侧分类侧栏（桌面端） -->
    <div class="community-sidebar" style="flex-shrink:0; width:220px; position:sticky; top:100px; z-index:10;">
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; padding:20px; border:1px solid var(--border-light);">
            <h3 style="margin:0 0 16px; font-size:1.2rem;">分类</h3>
            <ul style="list-style:none; padding:0; margin:0;">
                <li style="margin-bottom:6px;"><a href="?" style="display:block; padding:8px 12px; border-radius:8px; text-decoration:none; color:<?php echo $currentCat===0 ? '#fff' : 'var(--text)'; ?>; background:<?php echo $currentCat===0 ? 'var(--mc-green)' : 'transparent'; ?>;">全部</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li style="margin-bottom:6px;"><a href="?cat=<?php echo $cat['id']; ?>" style="display:block; padding:8px 12px; border-radius:8px; text-decoration:none; color:<?php echo $currentCat===$cat['id'] ? '#fff' : 'var(--text)'; ?>; background:<?php echo $currentCat===$cat['id'] ? 'var(--mc-green)' : 'transparent'; ?>;"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php if (isAdmin()): ?><div style="margin-top:16px; border-top:1px solid var(--border-light); padding-top:12px;"><a href="<?php echo BASE_URL; ?>/modules/admin/categories.php" class="btn-auth" style="width:100%; justify-content:center; font-size:0.85rem; padding:8px;">管理分类</a></div><?php endif; ?>
        </div>
    </div>

    <!-- 右侧主内容区 -->
    <div style="flex:1; min-width:0;">
        <!-- 手机端分类下拉 -->
        <div class="mobile-cat-select" style="display:none; margin-bottom:20px;">
            <select onchange="location.href=this.value" style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--border); background:var(--surface-glass); color:var(--text);">
                <option value="?" <?php echo $currentCat===0?'selected':''; ?>>全部</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="?cat=<?php echo $cat['id']; ?>" <?php echo $currentCat===$cat['id']?'selected':''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 用户快捷发布 -->
        <?php if (isLoggedIn()): $user = currentUser(); ?>
            <div style="display:flex; align-items:center; gap:16px; padding:20px; background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:16px; margin-bottom:20px; animation: fadeInUp 0.6s ease 0.2s both;">
                <img src="<?php echo $user['avatar']; ?>" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">
                <div style="flex:1;"><strong><?php echo htmlspecialchars($user['username']); ?></strong></div>
                <?php if (canPostInCommunity()): ?><a href="edit.php" class="btn-auth" style="padding:8px 20px;">发布帖子</a><?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- 置顶轮播 -->
        <?php if ($pinnedPosts->num_rows > 0): ?>
            <div class="community-carousel" style="width:100%; margin-bottom:24px; border-radius:16px; overflow:hidden; box-shadow:var(--shadow-md); animation: fadeInUp 0.6s ease 0.3s both;">
                <div class="carousel-wrapper" style="height:300px; position:relative; overflow:hidden;">
                    <div class="carousel-track" id="track-pinned" style="display:flex; height:100%; transition:transform 0.7s;">
                        <?php while ($pin = $pinnedPosts->fetch_assoc()): ?>
                            <div class="carousel-slide" style="min-width:100%; height:100%; background-image:url('<?php echo $pin['cover'] ? $pin['cover'] : 'mc1.png'; ?>'); background-size:cover; background-position:center; position:relative; display:flex; align-items:flex-end; padding:40px;">
                                <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.8), transparent);"></div>
                                <div style="position:relative; z-index:1; color:#fff; max-width:600px;">
                                    <span style="display:inline-block; background:rgba(255,255,255,0.2); backdrop-filter:blur(8px); padding:4px 14px; border-radius:20px; font-size:0.8rem; margin-bottom:10px;">置顶</span>
                                    <h3 style="font-size:1.8rem; margin:0 0 8px;"><a href="detail.php?id=<?php echo $pin['id']; ?>" style="color:#fff; text-decoration:none;"><?php echo htmlspecialchars($pin['title']); ?></a></h3>
                                    <p style="opacity:0.9; margin-bottom:16px;"><?php echo mb_substr(strip_tags($pin['content']), 0, 100) . '...'; ?></p>
                                    <a href="detail.php?id=<?php echo $pin['id']; ?>" style="display:inline-block; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.3); padding:8px 22px; border-radius:20px; color:#fff; text-decoration:none; font-size:0.9rem;">查看详情</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <button class="carousel-arrow prev" style="position:absolute; top:50%; left:16px; transform:translateY(-50%); background:rgba(255,255,255,0.2); border:none; color:#fff; width:40px; height:40px; border-radius:50%; cursor:pointer; z-index:2;"><i class="fas fa-chevron-left"></i></button>
                    <button class="carousel-arrow next" style="position:absolute; top:50%; right:16px; transform:translateY(-50%); background:rgba(255,255,255,0.2); border:none; color:#fff; width:40px; height:40px; border-radius:50%; cursor:pointer; z-index:2;"><i class="fas fa-chevron-right"></i></button>
                    <div class="carousel-indicators" id="indicators-pinned" style="position:absolute; bottom:16px; right:24px; display:flex; gap:6px; z-index:2;"></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- 帖子网格（统一高度，限制正文行数） -->
        <div class="post-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
            <?php if ($posts->num_rows === 0): ?>
                <div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text-secondary); animation: fadeInUp 0.4s ease;">暂无帖子</div>
            <?php else: ?>
                <?php $index = 0; while ($post = $posts->fetch_assoc()): ?>
                    <a href="detail.php?id=<?php echo $post['id']; ?>" style="text-decoration:none; color:inherit; animation: fadeInUp 0.5s ease <?php echo 0.1 * ($index % 6); ?>s both;">
                        <div class="post-card" style="
                            background:var(--surface-glass); backdrop-filter:blur(14px);
                            border-radius:16px; overflow:hidden;
                            border:1px solid var(--border-light);
                            transition:transform 0.2s, box-shadow 0.2s;
                            height:100%; display:flex; flex-direction:column;
                        ">
                            <?php if ($post['cover']): ?>
                                <img src="<?php echo htmlspecialchars($post['cover']); ?>" style="width:100%; height:160px; object-fit:cover;">
                            <?php else: ?>
                                <div style="height:120px; background:linear-gradient(135deg, var(--mc-green), var(--mc-gold-soft));"></div>
                            <?php endif; ?>
                            <div style="padding:16px; flex:1; display:flex; flex-direction:column;">
                                <h3 style="font-size:1.15rem; font-weight:700; margin:0 0 6px; line-height:1.3;">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </h3>
                                <?php if ($post['subtitle']): ?>
                                    <p style="color:var(--text-secondary); font-size:0.85rem; margin:0 0 8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($post['subtitle']); ?></p>
                                <?php endif; ?>
                                <p style="color:var(--text-secondary); font-size:0.9rem; line-height:1.5; flex:1; margin:0 0 12px;
                                    display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical;
                                    overflow:hidden; text-overflow:ellipsis;
                                ">
                                    <?php echo strip_tags($post['content']); ?>
                                </p>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:auto;">
                                    <img src="<?php echo $post['avatar']; ?>" style="width:22px; height:22px; border-radius:50%;">
                                    <span style="font-size:0.8rem;"><?php echo htmlspecialchars($post['username']); ?></span>
                                    <span style="font-size:0.8rem; color:var(--text-tertiary);">·</span>
                                    <span style="font-size:0.8rem; color:var(--text-tertiary);"><?php echo date('m/d', strtotime($post['created_at'])); ?></span>
                                </div>
                                <div style="margin-top:6px; display:flex; gap:6px; flex-wrap:wrap;">
                                    <?php if ($post['tag']): ?><span style="background:rgba(79,138,48,0.15); color:var(--mc-green); font-size:0.7rem; padding:2px 8px; border-radius:10px;"><?php echo htmlspecialchars($post['tag']); ?></span><?php endif; ?>
                                    <?php if ($post['cat_name']): ?><span style="background:rgba(212,148,43,0.15); color:#b8860b; font-size:0.7rem; padding:2px 8px; border-radius:10px;"><?php echo htmlspecialchars($post['cat_name']); ?></span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php $index++; endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- 分页 -->
        <?php if ($totalPages > 1): ?>
        <div style="display:flex; justify-content:center; gap:8px; margin-top:36px; animation: fadeInUp 0.4s ease;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?cat=<?php echo $currentCat; ?>&page=<?php echo $i; ?>" style="padding:8px 16px; border-radius:20px; text-decoration:none; font-weight:500; background:<?php echo $i===$page?'var(--mc-green)':'var(--surface-alt)'; ?>; color:<?php echo $i===$page?'#fff':'var(--text)'; ?>;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .post-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    
    @keyframes fadeInDown {
        from { opacity:0; transform:translateY(-20px); }
        to { opacity:1; transform:translateY(0); }
    }
    @keyframes fadeInUp {
        from { opacity:0; transform:translateY(20px); }
        to { opacity:1; transform:translateY(0); }
    }

    @media (max-width: 900px) {
        .community-sidebar { display: none; }
        .mobile-cat-select { display: block !important; }
    }
</style>

<script>
(function() {
    // 置顶轮播
    const track = document.getElementById('track-pinned');
    if (!track) return;
    const slides = track.querySelectorAll('.carousel-slide');
    if (slides.length === 0) return;
    let current = 0;
    const total = slides.length;
    const indicatorsContainer = document.getElementById('indicators-pinned');
    for (let i = 0; i < total; i++) {
        const dot = document.createElement('span');
        dot.style.cssText = 'width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.5);cursor:pointer;transition:0.2s;';
        if (i === 0) dot.style.background = '#fff';
        dot.addEventListener('click', () => goTo(i));
        indicatorsContainer.appendChild(dot);
    }
    function update() { 
        track.style.transform = `translateX(-${current * 100}%)`; 
        indicatorsContainer.querySelectorAll('span').forEach((d,i) => d.style.background = i===current?'#fff':'rgba(255,255,255,0.5)'); 
    }
    function goTo(i) { current = i; update(); resetAuto(); }
    function next() { current = (current+1)%total; update(); resetAuto(); }
    function prev() { current = (current-1+total)%total; update(); resetAuto(); }
    let interval = setInterval(next, 4000);
    function resetAuto() { clearInterval(interval); interval = setInterval(next, 4000); }
    document.querySelector('.carousel-arrow.next')?.addEventListener('click', next);
    document.querySelector('.carousel-arrow.prev')?.addEventListener('click', prev);
})();
</script>

<?php require_once __DIR__ . '/../../footer.php'; ?>