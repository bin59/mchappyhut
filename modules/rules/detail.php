<?php
require_once __DIR__ . '/../../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 关联用户表获取发布者信息
$stmt = $conn->prepare("SELECT r.*, u.username, u.avatar, u.id AS author_id 
                        FROM rules r 
                        LEFT JOIN users u ON r.user_id = u.id 
                        WHERE r.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$rule = $stmt->get_result()->fetch_assoc();
if (!$rule) redirect(BASE_URL . '/modules/rules/');

// 解析正文标题生成目录（已修复弃用警告）
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML('<?xml encoding="UTF-8">' . $rule['content']);
libxml_clear_errors();
$xpath = new DOMXPath($dom);
$headings = $xpath->query('//h2 | //h3');
$toc = [];
$index = 0;
foreach ($headings as $heading) {
    $text = trim($heading->textContent);
    if (empty($text)) continue;
    $hid = 'section-' . $index;
    $heading->setAttribute('id', $hid);
    $toc[] = ['id' => $hid, 'text' => $text, 'tag' => $heading->tagName];
    $index++;
}
$modifiedContent = '';
foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $child) {
    $modifiedContent .= $dom->saveHTML($child);
}
$rule['content'] = $modifiedContent;

$pageTitle = htmlspecialchars($rule['title']) . ' - 行为准则';
require_once __DIR__ . '/../../header.php';
?>

<div style="background: var(--bg); padding: 120px 0 50px; animation: fadeInUp 0.6s ease;">
    <div style="max-width:1300px; margin:0 auto; padding:0 20px;">

        <!-- 面包屑 -->
        <div style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:20px;">
            <a href="index.php" style="color:var(--mc-green); text-decoration:none;">行为准则</a> &gt; 
            <span><?php echo htmlspecialchars($rule['title']); ?></span>
        </div>

        <!-- 文章头部：发布者信息 + 封面图 -->
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; 
                    padding:24px 32px; background:var(--surface-glass); backdrop-filter:blur(14px); 
                    border:1px solid var(--border-light); border-radius:12px; margin-bottom:20px;
                    animation: fadeInUp 0.6s ease 0.1s both;">
            <div style="display:flex; align-items:center; gap:14px; flex:1; min-width:0;">
                <!-- 真实发布者头像和名称，可点击跳转；若无则显示默认占位 -->
                <?php if (!empty($rule['user_id']) && !empty($rule['avatar'])): ?>
                    <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $rule['author_id']; ?>">
                        <img src="<?php echo htmlspecialchars($rule['avatar']); ?>" 
                             style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid var(--surface);">
                    </a>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/modules/user/profile.php?id=<?php echo $rule['author_id']; ?>" 
                           style="font-weight:600; color:var(--text); text-decoration:none;">
                            <?php echo htmlspecialchars($rule['username']); ?>
                        </a>
                        <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:2px;">
                            <?php echo date('Y年m月d日 H:i', strtotime($rule['created_at'])); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="width:42px; height:42px; border-radius:50%; background:var(--mc-green); 
                                display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold;">规</div>
                    <div>
                        <span style="font-weight:600; color:var(--text);">管理员</span>
                        <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:2px;">
                            <?php echo date('Y年m月d日 H:i', strtotime($rule['created_at'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- 封面图 -->
            <?php if (!empty($rule['cover'])): ?>
                <img src="<?php echo htmlspecialchars($rule['cover']); ?>" 
                     style="height:85px; width:auto; max-width:160px; object-fit:cover; border-radius:8px; 
                            box-shadow:var(--shadow-sm); flex-shrink:0;">
            <?php endif; ?>
        </div>

        <!-- 标题与副标题（居中） -->
        <div style="text-align:center; margin-bottom:30px; animation: fadeInUp 0.6s ease 0.2s both;">
            <h1 style="font-size:clamp(1.8rem, 3.5vw, 2.4rem); font-weight:800; margin-bottom:8px; text-align:center;">
                <?php echo htmlspecialchars($rule['title']); ?>
            </h1>
            <?php if (!empty($rule['subtitle'])): ?>
                <p style="font-size: clamp(0.8rem, 1.3vw, 0.9rem); color:var(--text-secondary); margin:0; text-align:center;">
                    <?php echo htmlspecialchars($rule['subtitle']); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- 正文与目录 -->
        <div style="display:flex; gap:30px; align-items:flex-start;">
            <!-- 左侧目录 -->
            <?php if (!empty($toc)): ?>
                <div class="rule-sidebar" style="flex-shrink:0; width:240px; position:sticky; top:100px; 
                            animation: fadeInLeft 0.5s ease 0.3s both;">
                    <div style="background:var(--surface-glass); backdrop-filter:blur(14px); 
                                border:1px solid var(--border-light); border-radius:12px; padding:20px;">
                        <h4 style="font-size:1.1rem; font-weight:700; margin:0 0 16px; border-bottom:2px solid var(--mc-green); 
                                   padding-bottom:10px;">📑 目录</h4>
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach ($toc as $item): ?>
                                <li style="margin-bottom:6px; padding-left: <?php echo $item['tag'] === 'h3' ? '16px' : '0'; ?>;">
                                    <a href="#<?php echo $item['id']; ?>" 
                                       style="color:var(--text-secondary); text-decoration:none; font-size:0.9rem; 
                                              line-height:1.6; transition:color 0.2s;">
                                        <?php echo htmlspecialchars($item['text']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 右侧正文 -->
            <div class="rule-body" style="flex:1; min-width:0; background:var(--surface-glass); backdrop-filter:blur(14px); 
                        border:1px solid var(--border-light); border-radius:12px; padding:40px;
                        animation: fadeInRight 0.5s ease 0.3s both;">
                <div style="font-size:clamp(0.85rem, 1.4vw, 0.95rem); line-height:2; color:var(--text); word-break:break-word;">
                    <?php echo $rule['content']; ?>
                </div>
            </div>
        </div>

        <?php if (isAdmin()): ?>
            <div style="margin-top:30px; display:flex; gap:12px; animation: fadeInUp 0.5s ease 0.4s both;">
                <a href="edit.php?id=<?php echo $rule['id']; ?>" class="btn-auth" style="text-decoration:none;">
                    <i class="fas fa-edit"></i> 编辑
                </a>
                <a href="delete.php?id=<?php echo $rule['id']; ?>" class="btn-auth" style="background:#e74c3c; text-decoration:none;" 
                   onclick="return confirm('确定删除？');">
                    <i class="fas fa-trash"></i> 删除
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* 进场动画 */
    @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeInLeft { from { opacity:0; transform:translateX(-20px); } to { opacity:1; transform:translateX(0); } }
    @keyframes fadeInRight { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
    
    .rule-body img { max-width:100%; border-radius:8px; margin:16px 0; }
    .rule-body h2, .rule-body h3 { margin-top:28px; margin-bottom:14px; font-weight:600; }
    .rule-sidebar a:hover { color: var(--mc-green) !important; }

    /* 手机端深度适配 */
    @media (max-width: 768px) {
        .rule-sidebar { display: none; }
        .rule-body { padding: 20px !important; }
        /* 标题和副标题居中且更小 */
        [style*="text-align:center"] h1 { font-size: 1.5rem !important; }
        [style*="text-align:center"] p { font-size: 0.8rem !important; }
        /* 发布者框上下排列，封面图自适应 */
        [style*="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between"] {
            flex-direction: column;
            align-items: flex-start !important;
        }
        img[style*="height:85px"] { height: 55px !important; max-width: 100% !important; margin-top: 10px; }
    }
</style>
<?php require_once __DIR__ . '/../../footer.php'; ?>