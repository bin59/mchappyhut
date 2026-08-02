<?php
require_once __DIR__ . '/../../config.php';
if (isLoggedIn()) redirect(BASE_URL . '/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = '请填写邮箱和密码';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                redirect(BASE_URL . '/index.php');
            } else {
                $error = '密码错误';
            }
        } else {
            $error = '邮箱未注册';
        }
    }
}

$pageTitle = '登录';
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

    <!-- 右侧登录表单 -->
    <div class="auth-right" style="flex:1; min-width:380px; max-width:520px; display:flex; align-items:center; justify-content:center; padding:40px;">
        <div style="background:var(--surface-glass); backdrop-filter:blur(18px); border-radius:24px; padding:48px 40px; width:100%; max-width:420px; box-shadow:var(--shadow-lg); border:1px solid var(--border-light);">
            <h2 style="font-size:2rem; font-weight:700; margin-bottom:4px; color:var(--text);">欢迎回来</h2>
            <p style="color:var(--text-secondary); margin-bottom:28px;">登录你的账号</p>

            <?php if ($error): ?>
                <div style="background:#ffeaea; color:#c0392b; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom:16px;">
                    <label style="font-weight:600; font-size:0.9rem; color:var(--text-secondary);">邮箱</label>
                    <input type="email" name="email" required style="width:100%; padding:14px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
                <div style="margin-bottom:24px;">
                    <label style="font-weight:600; font-size:0.9rem; color:var(--text-secondary);">密码</label>
                    <input type="password" name="password" required style="width:100%; padding:14px; border:1px solid var(--border); border-radius:12px; background:var(--bg); color:var(--text); font-size:1rem;">
                </div>
                <button type="submit" class="btn-auth" style="width:100%; justify-content:center; padding:14px; font-size:1rem; border-radius:12px;">登录</button>
            </form>

            <p style="text-align:center; margin-top:24px; color:var(--text-secondary);">
                还没有账号？ <a href="<?php echo BASE_URL; ?>/modules/user/register.php" style="color:var(--mc-green); font-weight:600;">立即注册</a>
            </p>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .auth-page { flex-direction: column !important; }
        .auth-left { flex: none !important; min-height: 220px !important; padding: 40px 20px !important; }
        .auth-left h1 { font-size: 2rem !important; }
        .auth-right { min-width: 0 !important; max-width: 100% !important; padding: 20px !important; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>