<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = '表单中心';
require_once __DIR__ . '/../../header.php';

$forms = $conn->query("SELECT f.*, u.username, u.avatar, (SELECT COUNT(*) FROM form_answers WHERE form_id = f.id) AS answer_count FROM forms f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC");
?>

<div style="max-width:1800px; margin:0 auto; padding:100px 20px 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <h1 style="font-size:2.8rem; font-weight:800; color:var(--text);">表单中心</h1>
            <p style="color:var(--text-secondary); font-size:1.1rem;">参与问卷调查与投票</p>
        </div>
        <?php if (isAdmin()): ?>
            <a href="form_edit.php" class="btn-auth" style="padding:14px 32px; background:var(--mc-green); color:#fff; border-radius:14px; font-size:1rem;">+ 创建表单</a>
        <?php endif; ?>
    </div>

    <?php if ($forms->num_rows === 0): ?>
        <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:24px; padding:80px; text-align:center; color:var(--text-secondary);">暂无表单</div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap:28px;">
            <?php while ($form = $forms->fetch_assoc()): ?>
                <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; overflow:hidden; border:1px solid var(--border-light); transition: all 0.25s; display:flex; flex-direction:column;">
                    <?php if ($form['cover']): ?>
                        <a href="form_detail.php?id=<?php echo $form['id']; ?>" style="display:block; height:180px; background:url('<?php echo htmlspecialchars($form['cover']); ?>') center/cover no-repeat;"></a>
                    <?php endif; ?>
                    <div style="padding:24px; flex:1; display:flex; flex-direction:column;">
                        <a href="form_detail.php?id=<?php echo $form['id']; ?>" style="text-decoration:none; color:inherit;">
                            <h3 style="font-size:1.4rem; font-weight:700; margin:0 0 6px;"><?php echo htmlspecialchars($form['title']); ?></h3>
                            <?php if ($form['description']): ?>
                                <p style="color:var(--text-secondary); font-size:0.95rem; margin-bottom:12px; line-height:1.5;"><?php echo htmlspecialchars($form['description']); ?></p>
                            <?php endif; ?>
                        </a>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <img src="<?php echo $form['avatar'] ?: 'assets/images/default-avatar.png'; ?>" style="width:28px; height:28px; border-radius:50%;">
                                <span style="font-size:0.9rem; color:var(--text-secondary);"><?php echo htmlspecialchars($form['username'] ?? '管理员'); ?></span>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <?php if ($form['require_login']): ?><span style="background:rgba(79,138,48,0.15); color:var(--mc-green); padding:2px 10px; border-radius:12px; font-size:0.8rem;">需登录</span><?php endif; ?>
                                <?php if (($form['is_voting'] ?? 0)): ?><span style="background:rgba(212,148,43,0.15); color:#b8860b; padding:2px 10px; border-radius:12px; font-size:0.8rem;">投票</span><?php endif; ?>
                            </div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; color:var(--text-tertiary); font-size:0.9rem;">
                            <span><?php echo $form['answer_count']; ?> 人填写</span>
                            <?php if (isAdmin()): ?>
                                <div style="display:flex; gap:12px;">
                                    <a href="form_edit.php?id=<?php echo $form['id']; ?>" style="color:var(--mc-green);">编辑</a>
                                    <a href="form_manage.php?id=<?php echo $form['id']; ?>" style="color:#3498db;">结果</a>
                                    <a href="form_delete.php?id=<?php echo $form['id']; ?>" style="color:#e74c3c;" onclick="return confirm('确定删除？')">删除</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
<style>.form-card:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(0,0,0,0.1);}</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>