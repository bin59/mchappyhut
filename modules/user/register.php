<?php
require_once __DIR__ . '/../../config.php';
if (isLoggedIn()) redirect(BASE_URL . '/index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    // 收集6位验证码
    $code = '';
    for ($i = 1; $i <= 6; $i++) {
        $code .= $_POST['code' . $i] ?? '';
    }

    if (!isset($_SESSION['reg_code']) || strtoupper($code) !== $_SESSION['reg_code']) {
        $error = '邮箱验证码错误';
    } elseif (empty($username) || empty($email) || empty($password)) {
        $error = '请填写所有字段';
    } elseif ($password !== $confirm) {
        $error = '两次密码不一致';
    } elseif (strlen($password) < 6) {
        $error = '密码长度至少6位';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = '邮箱或用户名已存在';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'adventurer')");
            $stmt->bind_param("sss", $username, $email, $hashed);
            if ($stmt->execute()) {
                $success = '注册成功！现在可以登录了。';
                unset($_SESSION['reg_code']);
            } else {
                $error = '注册失败，请重试';
            }
        }
    }
}

$pageTitle = '注册';
require_once __DIR__ . '/../../header.php';
?>

<div class="auth-page" style="min-height:100vh; display:flex; align-items:stretch; background:var(--bg);">
    <!-- 左侧品牌展示区 -->
    <div class="auth-left" style="flex:1; background: linear-gradient(to bottom, rgba(10,14,8,0.3), rgba(10,14,8,0.7)), url('<?php echo BASE_URL; ?>/home1.png') center/cover no-repeat; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:60px 40px; position:relative;">
        <div style="text-align:center; color:#fff; z-index:1;">
            <img src="<?php echo BASE_URL; ?>/logo.png" alt="Logo" style="width:90px; height:90px; border-radius:24px; margin-bottom:20px; box-shadow:0 8px 24px rgba(0,0,0,0.4);">
            <h1 style="font-size:2.8rem; font-weight:800; margin-bottom:8px;">方块人快乐小窝</h1>
            <p style="font-size:1.1rem; opacity:0.85;">创造 · 冒险 · 温暖</p>
        </div>
    </div>

    <!-- 右侧注册表单 -->
    <div class="auth-right" style="flex:1; min-width:380px; max-width:520px; display:flex; align-items:center; justify-content:center; padding:40px;">
        <div style="background:var(--surface-glass); backdrop-filter:blur(18px); border-radius:24px; padding:48px 40px; width:100%; max-width:420px; box-shadow:var(--shadow-lg); border:1px solid var(--border-light);">
            <h2 style="font-size:2rem; font-weight:700; margin-bottom:4px; color:var(--text);">创建账号</h2>
            <p style="color:var(--text-secondary); margin-bottom:28px;">加入方块人快乐小窝</p>

            <?php if ($error): ?>
                <div style="background:#ffeaea; color:#c0392b; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo $error; ?></div>
            <?php elseif ($success): ?>
                <div style="background:#eafaf1; color:#27ae60; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div style="margin-bottom:14px;">
                    <label style="font-weight:600; font-size:0.9rem; color:var(--text-secondary);">用户名</label>
                    <input type="text" name="username" required style="width:100%; padding:14px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-weight:600; font-size:0.9rem; color:var(--text-secondary);">邮箱</label>
                    <input type="email" name="email" id="emailInput" required style="width:100%; padding:14px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-weight:600; font-size:0.9rem; color:var(--text-secondary);">密码</label>
                    <input type="password" name="password" required style="width:100%; padding:14px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="font-weight:600; font-size:0.9rem; color:var(--text-secondary);">确认密码</label>
                    <input type="password" name="confirm" required style="width:100%; padding:14px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>

                <!-- 6位验证码输入 -->
                <div style="margin-bottom:14px;">
                    <label style="font-weight:600; font-size:0.9rem; color:var(--text-secondary);">邮箱验证码</label>
                    <div style="display:flex; gap:8px; margin-bottom:12px;" id="codeInputs">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <input type="text" name="code<?php echo $i; ?>" maxlength="1" inputmode="numeric" pattern="[0-9A-Za-z]" 
                                   style="width:100%; aspect-ratio:1; text-align:center; font-size:1.4rem; font-weight:700; 
                                          border:2px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text); 
                                          padding:0; outline:none; transition:0.2s;"
                                   onkeyup="focusNext(this, <?php echo $i; ?>)">
                        <?php endfor; ?>
                    </div>
                    <button type="button" id="sendCodeBtn" class="btn-auth" style="width:100%; padding:12px; border-radius:10px; font-size:0.9rem;">获取验证码</button>
                    <div id="captchaBox" style="margin-top:10px; display:none; align-items:center; gap:8px;">
                        <span style="font-weight:600; font-size:0.85rem;">防人机验证：</span>
                        <span id="captchaQuestion" style="font-size:1rem; background:var(--bg); padding:4px 10px; border-radius:6px;"></span>
                        <input type="text" id="captchaAnswer" placeholder="答案" style="width:70px; padding:8px; border:1px solid var(--border); border-radius:6px; background:var(--bg); color:var(--text);">
                    </div>
                </div>

                <button type="submit" class="btn-auth" style="width:100%; justify-content:center; padding:14px; font-size:1rem; border-radius:12px; margin-top:8px;">注册</button>
            </form>

            <p style="text-align:center; margin-top:24px; color:var(--text-secondary);">
                已有账号？ <a href="<?php echo BASE_URL; ?>/modules/user/login.php" style="color:var(--mc-green); font-weight:600;">立即登录</a>
            </p>
        </div>
    </div>
</div>

<script>
// 验证码格子自动跳转
function focusNext(current, index) {
    const inputs = document.querySelectorAll('#codeInputs input');
    if (current.value.length === 1 && index < 6) {
        inputs[index].focus(); // index 从1开始，对应数组索引index
    }
    // 允许退格
    current.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && current.value === '' && index > 1) {
            inputs[index - 2].focus();
        }
    });
}

// 验证码发送逻辑
let captchaAnswer = '';
const sendBtn = document.getElementById('sendCodeBtn');
const captchaBox = document.getElementById('captchaBox');
const captchaQuestion = document.getElementById('captchaQuestion');
const captchaInput = document.getElementById('captchaAnswer');

sendBtn.addEventListener('click', async function() {
    const email = document.getElementById('emailInput').value.trim();
    if (!email) {
        alert('请先填写邮箱');
        return;
    }

    if (captchaBox.style.display === 'none' || captchaBox.style.display === '') {
        const a = Math.floor(Math.random() * 10) + 1;
        const b = Math.floor(Math.random() * 10) + 1;
        captchaAnswer = (a + b).toString();
        captchaQuestion.textContent = a + ' + ' + b + ' = ?';
        captchaBox.style.display = 'flex';
        captchaInput.value = '';
        captchaInput.focus();
        return;
    }

    if (captchaInput.value.trim() !== captchaAnswer) {
        alert('答案错误');
        captchaInput.value = '';
        return;
    }

    sendBtn.disabled = true;
    sendBtn.textContent = '发送中...';
    try {
        const resp = await fetch('send_code.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        });
        const data = await resp.json();
        if (data.success) {
            alert('验证码已发送，请检查邮箱（若未收到请查看垃圾箱）');
            captchaBox.style.display = 'none';
            let sec = 60;
            sendBtn.textContent = sec + 's';
            const timer = setInterval(() => {
                sec--;
                sendBtn.textContent = sec + 's';
                if (sec <= 0) {
                    clearInterval(timer);
                    sendBtn.textContent = '获取验证码';
                    sendBtn.disabled = false;
                }
            }, 1000);
        } else {
            alert('发送失败：' + data.message);
            sendBtn.disabled = false;
            sendBtn.textContent = '获取验证码';
        }
    } catch (e) {
        alert('网络错误');
        sendBtn.disabled = false;
        sendBtn.textContent = '获取验证码';
    }
});
</script>

<style>
    @media (max-width: 768px) {
        .auth-page { flex-direction: column !important; }
        .auth-left { flex: none !important; min-height: 200px !important; padding: 30px 20px !important; }
        .auth-left h1 { font-size: 1.8rem !important; }
        .auth-right { min-width: 0 !important; max-width: 100% !important; padding: 20px !important; }
        #codeInputs input { font-size: 1.2rem !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>