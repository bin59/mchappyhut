<?php
require_once __DIR__ . '/../../config.php';
requireLogin();
if (!isAdmin()) {
    redirect(BASE_URL . '/modules/user/login.php');
}

$message = '';
$error = '';

// 处理赞助配置更新 (赞助码 + 说明)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $qr_image_url = $_POST['qr_image_url'] ?? '';
    // 将换行转为<br>，再保留<a>标签
    $note = strip_tags(nl2br($_POST['note']), '<a><br>');

    if (isset($_FILES['qr_image_file']) && $_FILES['qr_image_file']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['qr_image_file']);
        if ($upload['success']) {
            $qr_image_url = $upload['url'];
        } else {
            $error = '赞助码上传失败: ' . $upload['message'];
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE sponsor_config SET qr_image_url = ?, note = ? WHERE id = 1");
        $stmt->bind_param("ss", $qr_image_url, $note);
        $stmt->execute();
        $message = '赞助配置已更新';
    }
}

// 删除赞助码
if (isset($_GET['delete_qr'])) {
    $conn->query("UPDATE sponsor_config SET qr_image_url = NULL WHERE id = 1");
    $message = '赞助码已删除';
}

// 删除赞助人员
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM sponsors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = '赞助人员已删除';
}

// 获取编辑项
$editItem = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM sponsors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editItem = $stmt->get_result()->fetch_assoc();
}

// 保存赞助人员
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sponsor'])) {
    $edit_id = !empty($_POST['edit_id']) ? intval($_POST['edit_id']) : null;
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);

    $image_url = $_POST['image_url'] ?? '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['image_file']);
        if ($upload['success']) {
            $image_url = $upload['url'];
        } else {
            $error = '头像上传失败: ' . $upload['message'];
        }
    }

    if (empty($name)) $error = '名称不能为空';

    if (!$error) {
        if ($edit_id) {
            $stmt = $conn->prepare("UPDATE sponsors SET name=?, description=?, image_url=?, link_url=?, sort_order=? WHERE id=?");
            $stmt->bind_param("ssssii", $name, $description, $image_url, $link_url, $sort_order, $edit_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO sponsors (name, description, image_url, link_url, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $description, $image_url, $link_url, $sort_order);
        }
        $stmt->execute();
        $message = '赞助人员已保存';
        $editItem = null;
    }
}

$sponsors = $conn->query("SELECT * FROM sponsors ORDER BY sort_order ASC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
$config = $conn->query("SELECT * FROM sponsor_config WHERE id = 1")->fetch_assoc();

$pageTitle = '赞助管理';
$isHomePage = false;
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:1400px; margin:0 auto; padding: calc(var(--nav-height) + 40px) 30px 40px;">
  <h1 style="font-size:2rem; font-weight:800; margin-bottom:30px;">赞助管理</h1>

  <?php if ($message): ?>
    <div style="background:#d4edda; color:#155724; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo $message; ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:8px; margin-bottom:20px;"><?php echo $error; ?></div>
  <?php endif; ?>

  <!-- 赞助码 + 说明 -->
  <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:30px; margin-bottom:30px; border:1px solid var(--border-light);">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:20px;">赞助码 & 说明</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="save_config" value="1">
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
        <div>
          <label style="font-weight:600; display:block; margin-bottom:6px;">赞助码图片（上传或填写URL）</label>
          <input type="file" name="qr_image_file" accept="image/*" style="width:100%; margin-bottom:8px;">
          <input type="text" name="qr_image_url" value="<?php echo htmlspecialchars($config['qr_image_url'] ?? ''); ?>" placeholder="或输入图片URL" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
          <div style="margin-top:12px; display:flex; align-items:center; gap:16px;">
            <?php if (!empty($config['qr_image_url'])): ?>
              <img src="<?php echo htmlspecialchars($config['qr_image_url']); ?>" style="height:80px; width:80px; object-fit:contain; border-radius:8px; border:1px solid var(--border);">
              <a href="?delete_qr=1" onclick="return confirm('确定删除赞助码？')" style="color:#e74c3c; font-size:0.9rem; cursor:pointer;">删除赞助码</a>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <label style="font-weight:600; display:block; margin-bottom:6px;">赞助说明 (支持链接：<a href="...">文字</a>)</label>
          <textarea name="note" id="noteTextarea" rows="5" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><?php echo htmlspecialchars(preg_replace('/<br\s*\/?>/i', "\n", $config['note'] ?? '')); ?></textarea>
          <div style="margin-top:8px;">
            <button type="button" onclick="insertLink()" style="background:var(--mc-gold); color:#fff; border:none; padding:6px 14px; border-radius:20px; cursor:pointer; font-weight:600;">插入超链接</button>
            <span style="font-size:0.8rem; color:var(--text-secondary); margin-left:8px;">选中文字后点击可包裹链接，或直接输入代码</span>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-auth" style="margin-top:20px; justify-content:center; width:auto;">保存配置</button>
    </form>
  </div>

  <!-- 赞助人员编辑 -->
  <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:30px; margin-bottom:30px; border:1px solid var(--border-light);">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:20px;"><?php echo $editItem ? '编辑赞助人员' : '添加赞助人员'; ?></h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="save_sponsor" value="1">
      <input type="hidden" name="edit_id" value="<?php echo $editItem['id'] ?? ''; ?>">
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <div>
          <label style="font-weight:600; display:block; margin-bottom:6px;">名称 *</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($editItem['name'] ?? ''); ?>" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <div>
          <label style="font-weight:600; display:block; margin-bottom:6px;">排序</label>
          <input type="number" name="sort_order" value="<?php echo htmlspecialchars($editItem['sort_order'] ?? '0'); ?>" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
        <div style="grid-column: span 2;">
          <label style="font-weight:600; display:block; margin-bottom:6px;">副标题/描述</label>
          <textarea name="description" rows="3" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);"><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
        </div>
        <div style="grid-column: span 2;">
          <label style="font-weight:600; display:block; margin-bottom:6px;">头像（上传或填写URL）</label>
          <div style="display:flex; gap:16px; align-items:flex-end;">
            <div style="flex:1;">
              <input type="file" name="image_file" accept="image/*" style="width:100%; margin-bottom:8px;">
              <input type="text" name="image_url" value="<?php echo htmlspecialchars($editItem['image_url'] ?? ''); ?>" placeholder="或输入头像URL" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
            </div>
            <?php if (!empty($editItem['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($editItem['image_url']); ?>" style="height:80px; width:80px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
            <?php endif; ?>
          </div>
        </div>
        <div style="grid-column: span 2;">
          <label style="font-weight:600; display:block; margin-bottom:6px;">链接</label>
          <input type="url" name="link_url" value="<?php echo htmlspecialchars($editItem['link_url'] ?? ''); ?>" placeholder="https://" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); color:var(--text);">
        </div>
      </div>
      <div style="display:flex; gap:12px; margin-top:20px;">
        <button type="submit" class="btn-auth" style="flex:1; justify-content:center;"><?php echo $editItem ? '更新' : '添加'; ?></button>
        <?php if ($editItem): ?>
          <a href="admin.php" class="btn-auth" style="background:var(--text-tertiary); flex:1; justify-content:center; text-decoration:none;">取消</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- 赞助人员列表 -->
  <div style="background:var(--surface-glass); backdrop-filter:blur(14px); border-radius:20px; padding:30px; border:1px solid var(--border-light);">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:20px;">现有赞助人员 (<?php echo count($sponsors); ?>)</h2>
    <?php if (empty($sponsors)): ?>
      <p style="color:var(--text-secondary); text-align:center; padding:40px;">暂无赞助人员，请添加</p>
    <?php else: ?>
      <div style="display:grid; gap:12px;">
        <?php foreach ($sponsors as $s): ?>
          <div style="display:flex; align-items:center; gap:20px; padding:16px; background:var(--surface-alt); border-radius:12px;">
            <?php if ($s['image_url']): ?>
              <img src="<?php echo htmlspecialchars($s['image_url']); ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
            <?php else: ?>
              <div style="width:50px; height:50px; border-radius:50%; background:linear-gradient(135deg, var(--mc-green), var(--mc-gold)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;"><?php echo mb_substr($s['name'], 0, 1); ?></div>
            <?php endif; ?>
            <div style="flex:1;">
              <strong><?php echo htmlspecialchars($s['name']); ?></strong>
              <div style="font-size:0.85rem; color:var(--text-secondary);">排序: <?php echo $s['sort_order']; ?> | 添加于 <?php echo date('Y-m-d', strtotime($s['created_at'])); ?></div>
            </div>
            <div style="display:flex; gap:8px;">
              <a href="?edit=<?php echo $s['id']; ?>" class="btn-auth" style="background:var(--mc-gold); text-decoration:none; padding:8px 16px;">编辑</a>
              <a href="?delete=<?php echo $s['id']; ?>" onclick="return confirm('确定删除？')" class="btn-auth" style="background:#e74c3c; text-decoration:none; padding:8px 16px;">删除</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function insertLink() {
    var textarea = document.getElementById('noteTextarea');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var selectedText = textarea.value.substring(start, end);
    
    var url = prompt('请输入链接地址 (https://...)', 'https://');
    if (!url) return;
    
    var displayText = selectedText || prompt('请输入显示文字', '点击查看');
    if (!displayText) return;
    
    var linkHtml = '<a href="' + url + '">' + displayText + '</a>';
    textarea.setRangeText(linkHtml, start, end, 'end');
}
</script>

<?php require_once __DIR__ . '/../../footer.php'; ?>