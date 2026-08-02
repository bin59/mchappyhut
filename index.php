<?php
$pageTitle = '首页';
$isHomePage = true;
require_once __DIR__ . '/header.php';

$stmtAnnounce = $conn->prepare("SELECT * FROM announcements WHERE is_pinned = 1 ORDER BY created_at DESC LIMIT 5");
$stmtAnnounce->execute();
$announcements = $stmtAnnounce->get_result();

$stmtEvent = $conn->prepare("SELECT * FROM events WHERE is_pinned = 1 ORDER BY created_at DESC LIMIT 5");
$stmtEvent->execute();
$events = $stmtEvent->get_result();

$stmtPost = $conn->prepare("SELECT p.*, u.username as poster_name, u.avatar as poster_avatar FROM community_posts p JOIN users u ON p.user_id = u.id WHERE p.is_pinned = 1 ORDER BY p.created_at DESC LIMIT 5");
$stmtPost->execute();
$posts = $stmtPost->get_result();

$stmtFeatured = $conn->prepare("SELECT * FROM announcements WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 1");
$stmtFeatured->execute();
$featured = $stmtFeatured->get_result()->fetch_assoc();
?>

<!-- Hero 区域（已添加淡入动画） -->
<section class="hero-brand" id="heroBrand" style="animation: fadeInUp 0.8s ease both;">
    <div class="hero-brand-inner">
        <div class="hero-left">
            <span class="hero-badge"><span class="badge-dot"></span> 官方服务器 · 在线中</span>
            <h1>方块人<br><span class="highlight">快乐小窝</span></h1>
            <p class="hero-desc">充满创造力与温暖的Minecraft社区，邀你探索无限方块世界。</p>
            <div class="hero-buttons">
                <button class="btn-primary" onclick="window.open('https://qun.qq.com/universal-share/share?ac=1&authKey=%2BnvwR6Quqqh1Q9yaaraNvYXA8vKPmWDinTau8jGE50GlixWjv4erzCRbnIKPRDa6&busi_data=eyJncm91cENvZGUiOiIxMTAyNjQwODgyIiwidG9rZW4iOiJPRW9xb3Bac2lDVjhiZVVxT0dia0NlWXpjQWN1anVwV1liaG8zei9Tb0dMMzIrY09waDNZTC9ONHgvWXhTV0swIiwidWluIjoiMTkxOTUzMTI4NiJ9&data=EcqpOac_KLB9R-Mp5zbgez_XHtYpeKvL8PU0L3dfFVWT-uZqCqWfXQtuQbELumM5FAF5yN6TX2ph-6nvr-uhOg&svctype=4&tempid=h5_group_info', '_blank')"><i class="fas fa-play"></i> 立即加入</button>
                <a href="modules/help/index.php" class="btn-outline" style="text-decoration: none;"><i class="fas fa-compass"></i> 了解更多</a>
            </div>
        </div>
        <div class="hero-right">
            <img src="log.png" alt="服务器展示">
        </div>
    </div>
</section>

<!-- 说明区保持不变 -->
<section class="discover-section">
    <div class="discover-grid">
        <div></div>
        <?php if ($featured): ?>
        <div class="feature-card">
            <span class="card-tag">特别公告</span>
            <h3><?php echo htmlspecialchars($featured['title']); ?></h3>
            <?php if ($featured['subtitle']): ?>
                <p style="color:var(--text-secondary); font-size:0.9rem;"><?php echo htmlspecialchars($featured['subtitle']); ?></p>
            <?php endif; ?>
            <a class="feature-link" href="modules/announcements/detail.php?id=<?php echo $featured['id']; ?>">前往探索 <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php else: ?>
        <div class="feature-card">
            <span class="card-tag">特别公告</span>
            <h3>暂无特别公告</h3>
            <p>敬请期待最新消息。</p>
        </div>
        <?php endif; ?>
        <div class="image-cluster">
            <?php if ($featured && $featured['cover']): ?>
                <img src="<?php echo htmlspecialchars($featured['cover']); ?>" alt="公告封面" style="width:50%; aspect-ratio:16/10; object-fit:cover; border-radius:var(--radius); box-shadow:var(--shadow-md);">
            <?php else: ?>
                <img src="mc1.png" alt="默认图1" style="width:50%; aspect-ratio:16/10; object-fit:cover; border-radius:var(--radius); box-shadow:var(--shadow-md);">
            <?php endif; ?>
            <img src="home1.png" alt="服务器展示" style="width:50%; aspect-ratio:16/10; object-fit:cover; border-radius:var(--radius); box-shadow:var(--shadow-md);">
        </div>
        <div></div>
    </div>
    <div class="discover-bottom">
        <h2>探索世界</h2>
        <p class="subtitle">加入成千上万的方块人，共同书写你的冒险故事</p>
    </div>
</section>

<!-- 公告轮播（移除正文，显示副标题） -->
<div class="carousel-section from-left" data-carousel="announce" data-direction="left">
    <div class="carousel-wrapper">
        <div class="carousel-track" id="track-announce">
            <?php while ($row = $announcements->fetch_assoc()): ?>
            <div class="carousel-slide" style="background-image:url('<?php echo $row['cover'] ? $row['cover'] : 'mc1.png'; ?>')">
                <span class="slide-tag"><i class="fas fa-bullhorn"></i> 置顶公告</span>
                <div class="slide-body">
                    <div class="slide-label">Announcement</div>
                    <h2>
                        <a href="modules/announcements/detail.php?id=<?php echo $row['id']; ?>" style="color:#fff; text-decoration:none;">
                            <?php echo htmlspecialchars($row['title']); ?>
                            <?php if ($row['tag']): ?><span class="server-badge"><?php echo htmlspecialchars($row['tag']); ?></span><?php endif; ?>
                            <span class="server-badge"><?php echo $row['server_id'] ? '指定服务器' : '全服'; ?></span>
                        </a>
                    </h2>
                    <?php if ($row['subtitle']): ?>
                        <p style="color:rgba(255,255,255,0.8); font-size:1rem;"><?php echo htmlspecialchars($row['subtitle']); ?></p>
                    <?php endif; ?>
                    <a href="modules/announcements/detail.php?id=<?php echo $row['id']; ?>" style="display:inline-block; margin-top:12px; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.25); padding:8px 20px; border-radius:20px; color:#fff; text-decoration:none; font-size:0.85rem;">查看详情 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if ($announcements->num_rows == 0): ?>
            <div class="carousel-slide" style="background-image:url('mc1.png')">
                <span class="slide-tag"><i class="fas fa-bullhorn"></i> 置顶公告</span>
                <div class="slide-body">
                    <div class="slide-label">Announcement</div>
                    <h2>暂无公告 <span class="server-badge">全服</span></h2>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <button class="carousel-arrow prev" data-action="prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-arrow next" data-action="next"><i class="fas fa-chevron-right"></i></button>
        <div class="carousel-indicators" id="indicators-announce"></div>
    </div>
</div>
<div style="height:1px; background:var(--border-light); width:100vw; margin-left:calc(-50vw + 50%);"></div>

<!-- 活动轮播（移除正文，显示副标题） -->
<div class="carousel-section from-right" data-carousel="event" data-direction="right">
    <div class="carousel-wrapper">
        <div class="carousel-track" id="track-event">
            <?php while ($event = $events->fetch_assoc()): ?>
            <div class="carousel-slide" style="background-image:url('<?php echo $event['cover'] ?? 'mc1.png'; ?>')">
                <span class="slide-tag"><i class="fas fa-gift"></i> 最新活动</span>
                <div class="slide-body">
                    <div class="slide-label">Event</div>
                    <h2>
                        <a href="modules/events/detail.php?id=<?php echo $event['id']; ?>" style="color:#fff; text-decoration:none;">
                            <?php echo htmlspecialchars($event['title']); ?>
                        </a>
                    </h2>
                    <div class="event-meta">
                        <div class="event-organizer">
                            <img src="<?php echo $event['organizer_avatar'] ?: 'https://via.placeholder.com/32'; ?>">
                            <span><?php echo htmlspecialchars($event['organizer_name'] ?: '未知'); ?></span>
                        </div>
                        <span class="event-status <?php
                            $now = new DateTime();
                            $start = new DateTime($event['start_time']);
                            $end = new DateTime($event['end_time']);
                            if ($now < $start) echo 'upcoming';
                            elseif ($now > $end) echo 'ended';
                            else echo 'ongoing';
                        ?>">
                            <?php
                            if ($now < $start) echo '未开始';
                            elseif ($now > $end) echo '已结束';
                            else echo '进行中';
                            ?>
                        </span>
                    </div>
                    <div class="event-time"><i class="far fa-calendar-alt"></i> <?php echo date('Y年m月d日', strtotime($event['start_time'])); ?> — <?php echo date('m月d日', strtotime($event['end_time'])); ?></div>
                    <?php if ($event['subtitle']): ?>
                        <p style="color:rgba(255,255,255,0.8); font-size:1rem;"><?php echo htmlspecialchars($event['subtitle']); ?></p>
                    <?php endif; ?>
                    <a href="modules/events/detail.php?id=<?php echo $event['id']; ?>" style="display:inline-block; margin-top:12px; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.25); padding:8px 20px; border-radius:20px; color:#fff; text-decoration:none; font-size:0.85rem;">查看详情 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if ($events->num_rows == 0): ?>
            <div class="carousel-slide" style="background-image:url('mc1.png')">
                <span class="slide-tag"><i class="fas fa-gift"></i> 最新活动</span>
                <div class="slide-body">
                    <div class="slide-label">Event</div>
                    <h2>暂无活动</h2>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <button class="carousel-arrow prev" data-action="prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-arrow next" data-action="next"><i class="fas fa-chevron-right"></i></button>
        <div class="carousel-indicators" id="indicators-event"></div>
    </div>
</div>
<div style="height:1px; background:var(--border-light); width:100vw; margin-left:calc(-50vw + 50%);"></div>

<!-- 精选推文轮播（移除正文，显示副标题） -->
<div class="carousel-section from-left" data-carousel="forum" data-direction="left">
    <div class="carousel-wrapper">
        <div class="carousel-track" id="track-forum">
            <?php while ($post = $posts->fetch_assoc()): ?>
            <div class="carousel-slide" style="background-image:url('<?php echo $post['cover'] ? $post['cover'] : 'mc1.png'; ?>')">
                <span class="slide-tag"><i class="fas fa-thumbtack"></i> 精选推文</span>
                <div class="slide-body">
                    <div class="slide-label">Forum Highlight</div>
                    <h2>
                        <a href="modules/community/detail.php?id=<?php echo $post['id']; ?>" style="color:#fff; text-decoration:none;">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </h2>
                    <div class="forum-poster">
                        <img src="<?php echo $post['poster_avatar']; ?>">
                        <div>
                            <span class="poster-name"><?php echo htmlspecialchars($post['poster_name']); ?></span>
                            <span class="post-date"><i class="far fa-clock"></i> <?php echo date('Y年m月d日', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php if (!empty($post['subtitle'])): ?>
                        <p style="color:rgba(255,255,255,0.8); font-size:1rem;"><?php echo htmlspecialchars($post['subtitle']); ?></p>
                    <?php endif; ?>
                    <a href="modules/community/detail.php?id=<?php echo $post['id']; ?>" style="display:inline-block; margin-top:12px; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.25); padding:8px 20px; border-radius:20px; color:#fff; text-decoration:none; font-size:0.85rem;">查看详情 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if ($posts->num_rows == 0): ?>
            <div class="carousel-slide" style="background-image:url('mc1.png')">
                <span class="slide-tag"><i class="fas fa-thumbtack"></i> 精选推文</span>
                <div class="slide-body">
                    <div class="slide-label">Forum Highlight</div>
                    <h2>暂无精选帖子</h2>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <button class="carousel-arrow prev" data-action="prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-arrow next" data-action="next"><i class="fas fa-chevron-right"></i></button>
        <div class="carousel-indicators" id="indicators-forum"></div>
    </div>
</div>

<section class="feedback-section">
    <div class="feedback-content">
        <h2>反馈及建议</h2>
        <p>我们珍视每一位玩家的声音，帮助我们变得更好</p>
        <button class="btn-feedback" onclick="location.href='<?php echo BASE_URL; ?>/modules/feedback/index.php'"><i class="fas fa-comment-dots"></i> 立即反馈</button>
    </div>
</section>

<div class="social-bar">
    <div class="social-icons">
        <a href="https://qun.qq.com/universal-share/share?ac=1&authKey=%2BnvwR6Quqqh1Q9yaaraNvYXA8vKPmWDinTau8jGE50GlixWjv4erzCRbnIKPRDa6&busi_data=eyJncm91cENvZGUiOiIxMTAyNjQwODgyIiwidG9rZW4iOiJPRW9xb3Bac2lDVjhiZVVxT0dia0NlWXpjQWN1anVwV1liaG8zei9Tb0dMMzIrY09waDNZTC9ONHgvWXhTV0swIiwidWluIjoiMTkxOTUzMTI4NiJ9&data=EcqpOac_KLB9R-Mp5zbgez_XHtYpeKvL8PU0L3dfFVWT-uZqCqWfXQtuQbELumM5FAF5yN6TX2ph-6nvr-uhOg&svctype=4&tempid=h5_group_info" target="_blank" title="QQ"><i class="fab fa-qq"></i></a>
        <a href="<?php echo BASE_URL; ?>/modules/wechat/index.php" title="微信"><i class="fab fa-weixin"></i></a>
        <a href="https://b23.tv/uKwA4mB" target="_blank" title="哔哩哔哩"><i class="fab fa-bilibili"></i></a>
        <a href="https://v.douyin.com/F8UTXYO293Y/" target="_blank" title="抖音"><i class="fab fa-tiktok"></i></a>
        <a href="https://github.com" target="_blank" title="GitHub"><i class="fab fa-github"></i></a>
        <a href="https://pd.qq.com/s/22e94kq8u" target="_blank" title="QQ频道">
    <img src="<?php echo BASE_URL; ?>/assets/images/channel.png" style="width: 1.8rem; height: 1.8rem; object-fit: contain; vertical-align: middle;">
</a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>