<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '反馈中心';
require_once __DIR__ . '/../../header.php';
?>

<div style="position:relative; width:100%; min-height:100vh; background: linear-gradient(to bottom, rgba(10,14,8,0.4), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/Sug1.png') center/cover no-repeat; display:flex; align-items:center; justify-content:center;">
    <div style="max-width:900px; width:100%; padding:40px 20px; text-align:center;">
        <h1 style="font-size:3.2rem; font-weight:800; color:#fff; margin-bottom:8px; text-shadow:0 2px 10px rgba(0,0,0,0.5); animation: fadeInDown 0.8s ease both;">工单及反馈</h1>
        <p style="color:rgba(255,255,255,0.85); font-size:1.2rem; margin-bottom:48px; animation: fadeInUp 0.8s ease 0.2s both;">选择您需要的服务类型</p>
        
        <div style="display:flex; flex-wrap:wrap; gap:28px; justify-content:center; perspective: 1000px;">
            <!-- 工单按钮 -->
            <a href="tickets.php" style="text-decoration:none; background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,0.25); border-radius:24px; padding:44px 36px; width:250px; color:#fff; transition: all 0.4s ease; display:flex; flex-direction:column; align-items:center; gap:16px; animation: fadeInUp 0.6s ease 0.3s both;"
               onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 16px 40px rgba(0,0,0,0.4)';"
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='';">
                <div style="width:72px; height:72px; border-radius:20px; background:rgba(232,168,32,0.2); display:flex; align-items:center; justify-content:center; font-size:2.2rem; color:var(--mc-gold-soft);">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <span style="font-size:1.5rem; font-weight:700;">工单</span>
                <span style="font-size:0.95rem; opacity:0.85;">发起工单3个工作日内回复</span>
            </a>
            
            <!-- 表单按钮 -->
            <a href="forms.php" style="text-decoration:none; background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,0.25); border-radius:24px; padding:44px 36px; width:250px; color:#fff; transition: all 0.4s ease; display:flex; flex-direction:column; align-items:center; gap:16px; animation: fadeInUp 0.6s ease 0.45s both;"
               onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 16px 40px rgba(0,0,0,0.4)';"
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='';">
                <div style="width:72px; height:72px; border-radius:20px; background:rgba(52,152,219,0.2); display:flex; align-items:center; justify-content:center; font-size:2.2rem; color:#3498db;">
                    <i class="fas fa-poll"></i>
                </div>
                <span style="font-size:1.5rem; font-weight:700;">表单</span>
                <span style="font-size:0.95rem; opacity:0.85;">填写问卷、投票</span>
            </a>
            
            <!-- 建议按钮 -->
            <a href="suggestions.php" style="text-decoration:none; background:var(--surface-glass); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,0.25); border-radius:24px; padding:44px 36px; width:250px; color:#fff; transition: all 0.4s ease; display:flex; flex-direction:column; align-items:center; gap:16px; animation: fadeInUp 0.6s ease 0.6s both;"
               onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 16px 40px rgba(0,0,0,0.4)';"
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='';">
                <div style="width:72px; height:72px; border-radius:20px; background:rgba(155,89,182,0.2); display:flex; align-items:center; justify-content:center; font-size:2.2rem; color:#9b59b6;">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <span style="font-size:1.5rem; font-weight:700;">建议</span>
                <span style="font-size:0.95rem; opacity:0.85;">提出想法，帮助我们改进</span>
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes fadeInDown { from { opacity:0; transform:translateY(-30px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
    @media (max-width: 768px) {
        h1 { font-size: 2.4rem !important; }
        a[href] { width: 100% !important; max-width: 300px; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>