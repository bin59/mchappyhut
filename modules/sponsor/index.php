<?php
require_once __DIR__ . '/../../config.php';

$stmt = $conn->query("SELECT * FROM sponsors ORDER BY sort_order ASC, created_at DESC");
$sponsors = $stmt->fetch_all(MYSQLI_ASSOC);
$config = $conn->query("SELECT * FROM sponsor_config WHERE id = 1")->fetch_assoc();
$note = $config['note'] ?? '感谢您的赞助，您的支持是我们前进的动力！';
$qr_image_url = $config['qr_image_url'] ?? '';

$pageTitle = '赞助我们';
$isHomePage = false;
require_once __DIR__ . '/../../header.php';
?>

<div class="sponsor-page" style="
  position: relative; min-height: 100vh;
  background: linear-gradient(135deg, rgba(20,20,20,0.82) 0%, rgba(5,5,5,0.9) 100%),
              url('<?php echo BASE_URL; ?>/assets/images/sss.jpg') center/cover no-repeat;
  padding: calc(var(--nav-height) + 60px) 20px 80px;
">

  <!-- 标题区域 -->
  <div class="sponsor-hero" style="text-align:center; margin-bottom: 50px;">
    <h1 class="hero-title" style="
      font-size: clamp(2.6rem, 5vw, 4rem);
      font-weight: 900;
      color: #fff;
      text-shadow: 0 4px 20px rgba(0,0,0,0.5);
      letter-spacing: 0.04em;
      margin-bottom: 8px;
      opacity: 0;
      transform: translateY(30px);
      animation: titleFadeIn 0.8s ease forwards;
    ">赞助我们</h1>
    <p class="hero-sub" style="
      font-size: 1.1rem;
      color: rgba(255,255,255,0.7);
      opacity: 0;
      animation: subtitleFade 0.8s ease 0.3s forwards;
    ">每一份支持，都是照亮前路的光</p>
  </div>

  <?php if (isAdmin()): ?>
  <div style="text-align:center; margin-bottom: 40px; opacity:0; animation: simpleFade 0.6s ease 0.8s forwards;">
    <a href="admin.php" class="admin-btn" style="
      display:inline-block;
      background: var(--mc-gold);
      color: #fff;
      padding: 10px 28px;
      border-radius: 30px;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
      transition: transform 0.2s;
    " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      编辑赞助内容
    </a>
  </div>
  <?php endif; ?>

  <!-- 左说明 + 右赞助码 -->
  <div style="max-width:1300px; margin:0 auto 50px; display:flex; gap:40px; flex-wrap:wrap; align-items:stretch;">
    
    <!-- 左侧：赞助说明卡片 -->
    <div class="sponsor-note" style="
      flex: 1 1 55%;
      min-width: 280px;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 24px;
      padding: 28px 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      opacity: 0;
      animation: cardFadeIn 0.8s ease 0.5s forwards;
    ">
      <h3 style="font-size:1.3rem; font-weight:700; color:#fff; margin-bottom:18px; border-left:3px solid var(--mc-gold-soft); padding-left:14px;">赞助说明</h3>
      <div class="note-content" style="color:rgba(255,255,255,0.85); font-size:0.95rem; line-height:1.8; word-break:break-word;">
        <?php echo $note; ?>
      </div>
    </div>

    <!-- 右侧：赞助码 -->
    <div class="sponsor-qr" style="
      flex: 0 0 280px;
      width: 280px;
      opacity: 0;
      animation: slideRight 0.8s ease 0.5s forwards;
    ">
      <h3 style="font-size:1.3rem; font-weight:700; color:#fff; margin-bottom:18px; border-left:3px solid var(--mc-gold-soft); padding-left:14px;">赞助码</h3>
      <div style="
        background: rgba(20,20,20,0.5);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 24px;
        padding: 28px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      ">
        <?php if ($qr_image_url): ?>
          <img src="<?php echo htmlspecialchars($qr_image_url); ?>" style="width:200px; height:200px; object-fit:contain; background:#fff; border-radius:18px; padding:8px; margin:0 auto;">
        <?php else: ?>
          <div style="width:200px; height:200px; margin:0 auto; background:rgba(255,255,255,0.1); border-radius:18px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.3); font-size:0.9rem;">
            暂无赞助码
          </div>
        <?php endif; ?>
        <p style="color:rgba(255,255,255,0.6); font-size:0.85rem; margin-top:14px;">扫描二维码支持我们</p>
      </div>
    </div>
  </div>

  <!-- 赞助人员名单 -->
  <div style="max-width:1300px; margin:0 auto;">
    <h2 style="font-size:1.6rem; font-weight:700; color:#fff; margin-bottom:25px; border-left:4px solid var(--mc-gold-soft); padding-left:16px;">赞助伙伴</h2>
    <?php if (empty($sponsors)): ?>
      <div class="empty-box" style="
        background: rgba(255,255,255,0.06);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 50px 20px;
        text-align: center;
        color: rgba(255,255,255,0.6);
        border: 1px solid rgba(255,255,255,0.1);
        font-size:0.95rem;
        opacity:0;
        animation: simpleFade 0.6s ease 1s forwards;
      ">暂无赞助记录，期待您的加入</div>
    <?php else: ?>
      <div class="sponsor-grid-container" style="
        background: rgba(20,20,20,0.3);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 24px;
        padding: 20px;
        max-height: 520px;
        overflow-y: auto;
        opacity: 0;
        animation: cardFadeIn 0.8s ease 1s forwards;
      ">
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap:14px;">
          <?php foreach ($sponsors as $index => $s): ?>
            <div class="sponsor-card" style="
              text-align: center;
              padding: 18px 10px 14px;
              border-radius: 16px;
              background: rgba(255,255,255,0.04);
              transition: all 0.25s ease;
              opacity: 0;
              animation: sponsorFlyIn 0.5s ease forwards;
              animation-delay: <?php echo 1.2 + $index * 0.05; ?>s;
            " onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'; this.style.transform='none'">
              <?php if ($s['image_url']): ?>
                <img src="<?php echo htmlspecialchars($s['image_url']); ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,0.2); margin-bottom:10px;">
              <?php else: ?>
                <div style="width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg, var(--mc-green), var(--mc-gold)); display:inline-flex; align-items:center; justify-content:center; font-size:1.3rem; color:#fff; font-weight:700; margin-bottom:10px;">
                  <?php echo mb_substr($s['name'], 0, 1); ?>
                </div>
              <?php endif; ?>
              <div style="font-size:0.8rem; color:rgba(255,255,255,0.9); font-weight:600; line-height:1.3; word-break:break-all;">
                <?php echo htmlspecialchars($s['name']); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  @keyframes titleFadeIn {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
  }
  @keyframes subtitleFade {
    0% { opacity: 0; }
    100% { opacity: 1; }
  }
  @keyframes cardFadeIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
  }
  @keyframes simpleFade {
    0% { opacity: 0; }
    100% { opacity: 1; }
  }
  @keyframes sponsorFlyIn {
    0% { opacity: 0; transform: translateY(15px) scale(0.96); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
  }
  @keyframes slideRight {
    0% { opacity: 0; transform: translateX(30px); }
    100% { opacity: 1; transform: translateX(0); }
  }

  /* 说明区域内链接变为按钮样式 */
  .note-content a {
    display: inline-block;
    background: var(--mc-gold);
    color: #fff !important;
    padding: 4px 16px;
    border-radius: 20px;
    font-weight: 600;
    text-decoration: none;
    margin: 0 4px;
    transition: background 0.2s, transform 0.2s;
    font-size: 0.9rem;
  }
  .note-content a:hover {
    background: #c17d1f;
    transform: translateY(-1px);
  }

  /* 滚动条美化 */
  .sponsor-grid-container::-webkit-scrollbar { width: 6px; }
  .sponsor-grid-container::-webkit-scrollbar-track { background: transparent; }
  .sponsor-grid-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }

  @media (max-width: 768px) {
    .hero-title { font-size: 2.2rem !important; }
    .hero-sub { font-size: 0.95rem; }
    .sponsor-note { padding: 20px 18px; }
    .note-content { font-size: 0.85rem; }
    .sponsor-qr { width: 100% !important; flex: auto !important; }
    .sponsor-grid-container { max-height: 400px; }
    .sponsor-card img, .sponsor-card .avatar-placeholder { width: 50px !important; height: 50px !important; }
    .sponsor-card .name { font-size: 0.72rem; }
  }
</style>

<?php require_once __DIR__ . '/../../footer.php'; ?>