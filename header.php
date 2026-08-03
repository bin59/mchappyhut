<?php
require_once __DIR__ . '/config.php';
$isHome = isset($isHomePage) && $isHomePage;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>方块人快乐小窝</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>/logo.png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --mc-green: #4F8A30;
            --mc-green-hover: #3D6B24;
            --mc-gold: #D4942B;
            --mc-gold-soft: #E8B84B;
            --bg: #FAFAF8;
            --surface: #FFFFFF;
            --surface-alt: #F3F1EC;
            --surface-glass: rgba(255, 255, 255, 0.72);
            --text: #1C1F18;
            --text-secondary: #5E6259;
            --text-tertiary: #8B8F86;
            --border: #E5E2DB;
            --border-light: #F0EDE7;
            --shadow-sm: 0 2px 8px rgba(20,24,16,0.06);
            --shadow-md: 0 8px 24px rgba(20,24,16,0.08);
            --shadow-lg: 0 20px 48px rgba(20,24,16,0.10);
            --radius: 16px;
            --radius-lg: 22px;
            --nav-height: 64px;
            --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
            --font: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
        }
        body.dark {
            --bg: #111510;
            --surface: #1A1F17;
            --surface-alt: #1F241C;
            --surface-glass: rgba(26,31,23,0.78);
            --text: #E8ECE3;
            --text-secondary: #B4BBAE;
            --text-tertiary: #7D8278;
            --border: #2B3027;
            --border-light: #23281F;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.25);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.3);
            --shadow-lg: 0 20px 48px rgba(0,0,0,0.35);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body {
            font-family: var(--font); background: var(--bg); color: var(--text);
            line-height: 1.6; transition: background 0.4s, color 0.4s;
            min-height: 100vh; display: flex; flex-direction: column;
            -webkit-font-smoothing: antialiased; overflow-x: hidden;
        }
        .navbar {
            position: fixed; top:0; left:0; right:0; z-index:500; height:var(--nav-height);
            display:flex; align-items:center; padding:0 36px; gap:16px;
            transition: background 0.35s, border-color 0.35s, box-shadow 0.35s, backdrop-filter 0.35s;
            background: transparent; border-bottom: 1px solid transparent;
            backdrop-filter: none; -webkit-backdrop-filter: none;
        }
        .navbar.scrolled {
            background: var(--surface-glass);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border-bottom: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }
        .navbar:not(.scrolled) .nav-brand-text { color:#fff; text-shadow:0 1px 4px rgba(0,0,0,0.3); }
        .navbar:not(.scrolled) .nav-link { color:rgba(255,255,255,0.85); }
        .navbar:not(.scrolled) .nav-link:hover { background:rgba(255,255,255,0.12); color:#fff; }
        .navbar:not(.scrolled) .btn-theme { color:rgba(255,255,255,0.85); border-color:rgba(255,255,255,0.35); }
        .navbar:not(.scrolled) .menu-toggle { color:#fff; }
        .nav-left { display:flex; align-items:center; gap:13px; flex-shrink:0; }
        .nav-logo-img { height:38px; width:38px; border-radius:8px; object-fit:contain; background:var(--mc-green); }
        .nav-brand-text { font-weight:700; font-size:1.05rem; white-space:nowrap; transition:color 0.35s; }
        .nav-center { display:flex; align-items:center; gap:2px; flex:1; justify-content:center; flex-wrap:wrap; }
        .nav-link { text-decoration:none; color:var(--text-secondary); font-weight:500; font-size:0.875rem; padding:7px 14px; border-radius:20px; transition:var(--transition); cursor:pointer; position:relative; }
        .navbar.scrolled .nav-link:hover { background:var(--surface-alt); color:var(--mc-green); }
        .nav-link::after { content:''; position:absolute; bottom:2px; left:50%; transform:translateX(-50%) scaleX(0); width:20px; height:2px; border-radius:1px; background:var(--mc-green); transition:transform 0.25s; }
        .navbar:not(.scrolled) .nav-link::after { background:#fff; }
        .nav-link:hover::after { transform:translateX(-50%) scaleX(1); }
        .nav-right { display:flex; align-items:center; gap:10px; flex-shrink:0; }
        .btn-auth { background:var(--mc-green); color:#fff; border:none; padding:9px 22px; border-radius:22px; font-weight:600; font-size:0.875rem; cursor:pointer; display:flex; align-items:center; gap:7px; transition:var(--transition); white-space: nowrap; text-decoration: none; }
        .btn-auth:hover { background:var(--mc-green-hover); transform:translateY(-1px); }
        .btn-theme { background:none; border:1px solid var(--border); width:36px; height:36px; border-radius:50%; font-size:1rem; cursor:pointer; color:var(--text-secondary); display:flex; align-items:center; justify-content:center; transition:var(--transition); }
        .menu-toggle { display:none; background:none; border:none; font-size:1.4rem; cursor:pointer; }
        .dropdown { position: relative; }
        .dropdown-menu {
            position: absolute; top: 100%; left: 0; margin-top: 4px;
            background: var(--surface-glass); backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border-radius: 16px; box-shadow: var(--shadow-lg);
            padding: 8px 0; min-width: 140px; opacity: 0; visibility: hidden;
            transform: translateY(-8px); transition: 0.25s ease;
            border: 1px solid var(--border-light); z-index: 20;
        }
        .navbar:not(.scrolled) .dropdown-menu {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-color: rgba(255,255,255,0.25);
        }
        .dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-menu a {
            display: block; padding: 8px 18px; font-size: 0.85rem; color: var(--text);
            text-decoration: none; transition: background 0.2s; white-space: nowrap;
        }
        .navbar:not(.scrolled) .dropdown-menu a { color: #fff; }
        .dropdown-menu a:hover { background: var(--surface-alt); color: var(--mc-green); }
        .mobile-only-login { display: none; }
        .nav-user-avatar { width:30px; height:30px; border-radius:50%; object-fit:cover; }

        /* Hero */
        .hero-brand {
            position:relative; width:100%; min-height:100vh;
            display:flex; align-items:center; justify-content:center;
            background: linear-gradient(to bottom, rgba(10,14,8,0.3), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/home1.png') center/cover no-repeat;
            padding-top: 0;
        }
        .hero-brand-inner { position:relative; z-index:1; display:flex; align-items:center; justify-content:space-between; max-width:1400px; width:100%; padding: calc(var(--nav-height) + 40px) 56px 64px; gap:52px; }
        .hero-left { flex:1; max-width:600px; }
        .hero-badge { display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25); border-radius:28px; padding:7px 20px; font-size:0.8rem; font-weight:600; color:#fff; margin-bottom:26px; backdrop-filter:blur(8px); }
        .hero-left h1 { font-size:clamp(2.8rem,5.5vw,5.4rem); font-weight:850; line-height:1.06; color:#fff; margin-bottom:24px; }
        .hero-left h1 .highlight { color:var(--mc-gold-soft); }
        .hero-desc { font-size:1.22rem; color:rgba(255,255,255,0.78); max-width:500px; margin-bottom:36px; }
        .hero-buttons { display:flex; gap:14px; flex-wrap:wrap; }
        .btn-primary { background:#fff; color:#1C1F18; border:none; padding:14px 34px; border-radius:28px; font-weight:650; cursor:pointer; transition:var(--transition); }
        .btn-primary:hover { transform:translateY(-2px); background:#F5F5F0; }
        .btn-outline { background:transparent; color:#fff; border:1.5px solid rgba(255,255,255,0.45); padding:14px 34px; border-radius:28px; font-weight:600; cursor:pointer; transition:var(--transition); backdrop-filter:blur(4px); }
        .btn-outline:hover { border-color:#fff; background:rgba(255,255,255,0.08); }
        .hero-right img { height:clamp(240px,40vw,480px); max-width:580px; filter:drop-shadow(0 22px 44px rgba(0,0,0,0.38)); animation:gentleFloat 5s ease-in-out infinite; }
        @keyframes gentleFloat { 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-12px); } }

        .discover-section { position: relative; background: var(--surface); padding: 30px 0 48px; transition: background 0.4s; }
        .discover-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 0.8fr 2.2fr 2.2fr 0.8fr; gap: 24px; padding: 0 32px; position: relative; }
        .feature-card { grid-column: 2 / 3; position: relative; margin-top: -150px; background: var(--surface-alt); border-radius: var(--radius-lg); padding: 32px 28px; box-shadow: var(--shadow-lg); z-index: 2; transition: background 0.4s; border: 1px solid var(--border-light); display: flex; flex-direction: column; justify-content: space-between; min-height: 220px; }
        .feature-card .card-tag { font-size:0.85rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--mc-green); font-weight:700; margin-bottom:12px; }
        .feature-card h3 { font-size:1.6rem; font-weight:750; margin-bottom:10px; color:var(--text); }
        .feature-card p { font-size:0.9rem; color:var(--text-secondary); line-height:1.5; margin-bottom:20px; flex:1; }
        .feature-link { display:inline-flex; align-items:center; gap:8px; font-weight:600; font-size:0.9rem; color:var(--mc-green); cursor:pointer; }
        .feature-link i { transition: transform 0.2s; }
        .feature-link:hover i { transform: translateX(4px); }
        .image-cluster { grid-column: 3 / 4; position: relative; margin-top: -60px; display: flex; gap: 16px; z-index: 2; }
        .image-cluster img { width: 50%; aspect-ratio: 16/10; border-radius: var(--radius); box-shadow: var(--shadow-md); object-fit: cover; transition: transform 0.3s; }
        .image-cluster img:hover { transform: scale(1.03); }
        .discover-bottom { text-align:center; margin-top: 36px; padding:0 20px; }
        .discover-bottom h2 { font-size:clamp(1.8rem,3.5vw,2.8rem); font-weight:800; margin-bottom:8px; }
        .discover-bottom .subtitle { font-size:1rem; color:var(--text-secondary); max-width:450px; margin:0 auto; }

        .carousel-section { width: 100vw; margin-left: calc(-50vw + 50%); opacity: 0; transition: opacity 0.7s, transform 0.7s; }
        .carousel-section.from-left { transform: translateX(-30px); }
        .carousel-section.from-right { transform: translateX(30px); }
        .carousel-section.visible { opacity:1; transform: translateX(0); }
        .carousel-wrapper { width:100%; height:700px; position:relative; overflow:hidden; }
        .carousel-track { display:flex; height:100%; transition:transform 0.7s; }
        .carousel-slide { min-width:100%; height:100%; background-size:cover; background-position:center; position:relative; display:flex; align-items:flex-end; padding:56px 64px; }
        .carousel-slide::before { content:''; position:absolute; inset:0; background:linear-gradient(to top, rgba(14,20,10,0.92), rgba(14,20,10,0.3)); z-index:1; }
        .slide-tag { position:absolute; top:36px; left:56px; z-index:2; background:rgba(14,20,10,0.55); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,0.25); border-radius:24px; padding:8px 22px; color:#fff; font-weight:600; font-size:0.82rem; display:flex; align-items:center; gap:8px; }
        .slide-body { position:relative; z-index:2; color:#fff; max-width:750px; }
        .slide-body .slide-label { font-size:0.7rem; text-transform:uppercase; letter-spacing:2.5px; opacity:0.65; margin-bottom:6px; }
        .slide-body h2 { font-size:2.6rem; font-weight:750; margin-bottom:12px; }
        .slide-body p { font-size:1.1rem; opacity:0.9; }
        .server-badge { display:inline-block; background:linear-gradient(135deg,#4F8A30,#6DB840); border:1px solid rgba(255,255,255,0.35); border-radius:20px; padding:4px 16px; font-size:0.75rem; font-weight:600; color:#fff; margin-left:12px; vertical-align:middle; }
        .event-meta { display:flex; align-items:center; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
        .event-organizer { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); border-radius:28px; padding:6px 18px 6px 6px; border:1px solid rgba(255,255,255,0.22); }
        .event-organizer img { width:32px; height:32px; border-radius:50%; object-fit:cover; background:#444; }
        .event-organizer span { font-weight:600; font-size:0.9rem; color:#fff; }
        .event-status { font-size:0.8rem; font-weight:600; padding:5px 16px; border-radius:20px; backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,0.25); }
        .event-status.ongoing { background:linear-gradient(135deg,#4F8A30,#5DA63C); color:#fff; }
        .event-status.upcoming { background:linear-gradient(135deg,#D4942B,#E8B84B); color:#1C1F18; }
        .event-status.ended { background:linear-gradient(135deg,#5E6259,#7D8278); color:#fff; }
        .event-time { font-size:0.9rem; color:rgba(255,255,255,0.9); display:flex; align-items:center; gap:8px; margin-bottom:18px; }
        .forum-poster { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
        .forum-poster img { width:36px; height:36px; border-radius:50%; object-fit:cover; background:#555; border:2px solid rgba(255,255,255,0.4); }
        .poster-name { font-weight:650; font-size:0.95rem; color:#fff; }
        .post-date { font-size:0.75rem; color:rgba(255,255,255,0.75); display:flex; align-items:center; gap:4px; }
        .carousel-arrow {
            position:absolute; top:50%; transform:translateY(-50%); z-index:10;
            background:rgba(255,255,255,0.12); backdrop-filter:blur(10px);
            border:1px solid rgba(255,255,255,0.22); color:#fff;
            width:46px; height:46px; border-radius:50%; cursor:pointer;
            opacity:0; transition:var(--transition);
            display:flex; align-items:center; justify-content:center;
        }
        .carousel-wrapper:hover .carousel-arrow { opacity:1; }
        .carousel-arrow.prev { left: 20px; right: auto; }
        .carousel-arrow.next { left: auto; right: 20px; }
        .carousel-indicators { position:absolute; bottom:24px; right:56px; display:flex; gap:8px; }
        .carousel-indicators .dot { width:10px; height:10px; border-radius:50%; background:rgba(255,255,255,0.4); cursor:pointer; }
        .carousel-indicators .dot.active { background:var(--mc-gold-soft); width:28px; border-radius:8px; }

        .feedback-section {
            position: relative; width: 100%; min-height: 620px;
            display: flex; align-items: center; justify-content: center; text-align: center;
            background: linear-gradient(to bottom, rgba(10,14,8,0.6), rgba(10,14,8,0.75)), url('<?php echo BASE_URL; ?>/Sug1.png') center/cover no-repeat;
            background-color: #1a1f16; padding: 100px 20px;
        }
        .feedback-content { position: relative; z-index: 1; color: #fff; }
        .feedback-content h2 { font-size: 2.8rem; font-weight: 800; margin-bottom: 12px; }
        .feedback-content p { font-size: 1.1rem; opacity: 0.8; margin-bottom: 36px; }
        .btn-feedback {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.35); color: #fff;
            padding: 16px 42px; border-radius: 40px; font-weight: 650;
            font-size: 1rem; cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2); letter-spacing: 0.02em;
        }
        .btn-feedback:hover {
            background: rgba(255,255,255,0.25); transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.3);
        }
        .social-bar { background: var(--surface); padding: 20px 0; text-align: center; transition: background 0.4s; border-top: 1px solid var(--border-light); }
        .social-icons { display: flex; justify-content: center; gap: 32px; flex-wrap: wrap; }
        .social-icons a { color: var(--text-secondary); font-size: 1.8rem; transition: color 0.2s, transform 0.2s; }
        .social-icons a:hover { color: var(--mc-green); transform: translateY(-3px); }
        .footer { margin-top: auto; background: var(--surface-alt); border-top: 1px solid var(--border-light); padding: 48px 32px 28px; color: var(--text-secondary); }
        .footer-inner { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr 1fr; gap: 28px; }
        .footer-brand { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
        .footer-logo { height: 42px; width: 42px; border-radius: 8px; object-fit: contain; background: var(--mc-green); }
        .footer-brand-text { font-weight: 700; font-size: 1.05rem; color: var(--text); }
        .footer-col h4 { color: var(--text); margin-bottom: 14px; font-weight: 700; font-size: 0.9rem; }
        .footer-col p { margin-bottom: 7px; font-size: 0.85rem; cursor: pointer; transition: var(--transition); color: var(--text-secondary); }
        .footer-col p:hover { color: var(--mc-green); }
        .footer-bottom {
            max-width: 1400px; margin: 32px auto 0; padding-top: 24px;
            border-top: 1px solid var(--border-light); text-align: center; font-size: 0.85rem;
            color: var(--text-tertiary); line-height: 1.5;
        }
        .disclaimer { font-size: 0.75rem; color: var(--text-tertiary); margin-top: 8px; max-width: 800px; margin-left: auto; margin-right: auto; padding: 0 16px; }

        @media (max-width: 1200px) {
            .discover-grid { padding:0 20px; gap:16px; }
            .feature-card { margin-top:-120px; }
            .image-cluster { margin-top:-40px; }
            .carousel-wrapper { height:600px; }
        }
        @media (max-width: 900px) {
            .navbar { padding:0 18px; }
            .menu-toggle { display:block; margin-left:auto; z-index:501; }
            .nav-center {
                display:none; position:absolute; top:var(--nav-height); left:0; width:100%;
                background: var(--surface-glass); backdrop-filter: blur(20px) saturate(160%);
                -webkit-backdrop-filter: blur(20px) saturate(160%);
                flex-direction:column; align-items:flex-start; padding:20px 24px;
                gap:6px; border-bottom:1px solid var(--border-light); box-shadow:var(--shadow-lg);
                z-index:99; border-radius:0 0 20px 20px;
            }
            .nav-center.active {
                 display:flex;
                 flex-direction: row;          /* 横向排列 */
                 flex-wrap: wrap;              /* 允许换行 */
                 justify-content: center;      /* 居中 */
                 gap: 6px;                     /* 按钮间距 */
            }
           .nav-center .nav-link {
                 color:var(--text);
                 width: auto;                  /* 宽度由内容决定，不再是100% */
                 padding: 6px 12px;            /* 缩小内边距 */
                 font-size:0.8rem;             /* 文字稍小 */
                 white-space: nowrap;          /* 防止文字换行 */
                 border-radius: 16px;
}
            .nav-center .dropdown-menu a { color:var(--text); }
            .navbar:not(.scrolled) .nav-center .nav-link { color:var(--text); }
            .navbar:not(.scrolled) .nav-center .dropdown-menu a { color:var(--text); }
            .dropdown-menu {
                position:static; box-shadow:none; border:none; padding:0 0 0 16px;
                opacity:1; visibility:visible; transform:none;
                background:transparent; backdrop-filter:none;
                display: flex;
                flex-direction: column;
            }
            .hero-right { display: none; }
            .hero-brand-inner { padding: calc(var(--nav-height) + 30px) 24px 50px; text-align:center; flex-direction:column; }
            .hero-left { display:flex; flex-direction:column; align-items:center; }
            .hero-buttons { justify-content:center; }
            .discover-grid { grid-template-columns:1fr 1fr; gap:20px; padding:0 16px; }
            .feature-card { grid-column:1/2; margin-top:-80px; }
            .image-cluster { grid-column:2/3; margin-top:-40px; }
            .carousel-wrapper { height:500px; }
            .carousel-slide { padding:32px; }
            .slide-tag { top:24px; left:28px; font-size:0.75rem; }
            .slide-body h2 { font-size:1.8rem; }
            .carousel-arrow { opacity:1; width:40px; height:40px; }
            .carousel-arrow.prev { left:12px; }
            .carousel-arrow.next { right:12px; }
            .footer-inner { grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
            .footer-col:first-child { grid-column: span 3; }
            .footer-col { margin-bottom: 12px; }
            .feedback-section { min-height: 500px; padding: 80px 20px; }
            .btn-auth { padding: 8px 16px; font-size: 0.8rem; }
            .nav-right .btn-auth { display: none; }
            .mobile-only-login {
                display: flex !important;
                width: 100%;
                justify-content: center;
                margin-top: 8px;
                padding: 10px;
            }
        }
        @media (max-width:600px) {
            .hero-left h1 { font-size:2.2rem; }
            .discover-grid { grid-template-columns:1fr; }
            .feature-card, .image-cluster { grid-column:auto; margin-top:0; }
            .image-cluster { flex-direction:row; gap:8px; }
            .image-cluster img { width:48%; }
            .carousel-wrapper { height:420px; }
            .carousel-slide { padding:20px; }
            .slide-tag { top:16px; left:16px; font-size:0.7rem; padding:6px 14px; }
            .slide-body h2 { font-size:1.4rem; }
            .carousel-arrow { width:36px; height:36px; }
            .carousel-arrow.prev { left:8px; }
            .carousel-arrow.next { right:8px; }
            .footer-inner { grid-template-columns: 1fr 1fr; gap: 16px; }
            .footer-col:first-child { grid-column: span 2; }
            .footer-col { margin-bottom: 8px; }
            .footer-col h4 { font-size: 0.85rem; }
            .footer-col p { font-size: 0.78rem; }
            .social-icons { gap: 20px; }
            .feedback-section { min-height: 450px; padding: 60px 16px; }
            .feedback-content h2 { font-size: 2rem; }
            .btn-auth { padding: 7px 14px; font-size: 0.75rem; }
            .footer-bottom { font-size: 0.75rem; }
            .disclaimer { font-size: 0.7rem; padding: 0 12px; }
        }
    </style>
</head>
<body>
<header class="navbar <?php echo $isHome ? '' : 'scrolled'; ?>" id="navbar">
    <div class="nav-left">
        <a href="<?php echo BASE_URL; ?>/index.php"><img src="<?php echo BASE_URL; ?>/logo.png" alt="Logo" class="nav-logo-img" onerror="this.style.background='#4F8A30'"></a>
        <span class="nav-brand-text">方块人快乐小窝</span>
    </div>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    <nav class="nav-center" id="navCenter">
        <?php
        // 动态导航：从 nav_items 表读取，不存在时退回硬编码
        $useDynamicNav = false;
        $topItems = [];
        $subItemsMap = [];
        try {
            $check = $conn->query("SHOW TABLES LIKE 'nav_items'");
            if ($check && $check->num_rows > 0) {
                $useDynamicNav = true;
                $result = $conn->query("SELECT * FROM nav_items WHERE parent_id IS NULL AND is_visible = 1 ORDER BY sort_order ASC");
                if ($result) $topItems = $result->fetch_all(MYSQLI_ASSOC);
                
                // 预加载所有下拉子项
                $allSubs = $conn->query("SELECT * FROM nav_items WHERE parent_id IS NOT NULL AND is_visible = 1 ORDER BY sort_order ASC");
                if ($allSubs) {
                    while ($sub = $allSubs->fetch_assoc()) {
                        $subItemsMap[$sub['parent_id']][] = $sub;
                    }
                }
            }
        } catch (Exception $e) {}
        
        if ($useDynamicNav && !empty($topItems)):
            foreach ($topItems as $navItem):
                if ($navItem['type'] === 'dropdown'):
                    $subs = $subItemsMap[$navItem['id']] ?? [];
        ?>
            <div class="dropdown">
                <a class="nav-link"><?php echo htmlspecialchars($navItem['title']); ?> <i class="fas fa-chevron-down" style="font-size:0.7rem; margin-left:4px;"></i></a>
                <div class="dropdown-menu">
                    <?php foreach ($subs as $sub): ?>
                    <a href="<?php echo (strpos($sub['url'], 'http') === 0) ? htmlspecialchars($sub['url']) : BASE_URL . htmlspecialchars($sub['url']); ?>"
                       <?php if ($sub['target'] === '_blank'): ?>target="_blank" rel="noopener"<?php endif; ?>>
                       <?php echo htmlspecialchars($sub['title']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <a class="nav-link" href="<?php echo (strpos($navItem['url'], 'http') === 0) ? htmlspecialchars($navItem['url']) : BASE_URL . htmlspecialchars($navItem['url']); ?>"
               <?php if ($navItem['target'] === '_blank'): ?>target="_blank" rel="noopener"<?php endif; ?>>
               <?php echo htmlspecialchars($navItem['title']); ?>
            </a>
        <?php endif;
            endforeach;
        else: // 硬编码兜底 ?>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">首页</a>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/announcements/index.php">公告</a>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/rules/index.php">规则</a>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/servers/index.php">列表</a>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/community/index.php">社区</a>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/events/index.php">活动</a>
        <a class="nav-link" href="https://docs.qq.com/sheet/DWk5DandtVGdzQ1du" target="_blank" rel="noopener">文档</a>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/sponsor/index.php">赞助</a>
        <div class="dropdown">
            <a class="nav-link">更多 <i class="fas fa-chevron-down" style="font-size:0.7rem; margin-left:4px;"></i></a>
            <div class="dropdown-menu">
                <a href="<?php echo BASE_URL; ?>/modules/feedback/index.php">反馈</a>
                <a href="<?php echo BASE_URL; ?>/modules/groups/index.php">玩家团体</a>
                <a href="http://igm.mchappyhut.club" target="_blank" rel="noopener">合集</a>
                <a href="<?php echo BASE_URL; ?>/modules/timeline/index.php">事件</a>
                <a href="<?php echo BASE_URL; ?>/modules/figures/index.php">人物志</a>
                <a href="<?php echo BASE_URL; ?>/modules/player_logs/index.php">玩家日志</a>
                <a href="<?php echo BASE_URL; ?>/modules/help/index.php">帮助中心</a>
            </div>
        </div>
        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/about/index.php">关于我们</a>
        <?php endif; ?>
        <?php if (!isLoggedIn()): ?>
            <a class="btn-auth mobile-only-login" href="<?php echo BASE_URL; ?>/modules/user/login.php">登录/注册</a>
        <?php endif; ?>
    </nav>
    <div class="nav-right">
        <button class="btn-theme" id="themeToggle"><i class="fas fa-moon"></i></button>
        <?php if (isLoggedIn()): ?>
            <div class="dropdown">
                <a class="nav-link" style="padding:4px 10px;">
                    <img src="<?php echo currentUser()['avatar']; ?>" class="nav-user-avatar" alt="头像">
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo BASE_URL; ?>/modules/user/profile.php">个人主页</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/modules/admin/dashboard.php">管理后台</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/modules/user/logout.php">退出登录</a>
                </div>
            </div>
        <?php else: ?>
            <a class="btn-auth" href="<?php echo BASE_URL; ?>/modules/user/login.php">登录/注册</a>
        <?php endif; ?>
    </div>
</header>