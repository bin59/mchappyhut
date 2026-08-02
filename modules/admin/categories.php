<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $name = $_POST['name'];
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name=?, sort_order=? WHERE id=?");
            $stmt->bind_param("sii", $name, $sort_order, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, sort_order) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $sort_order);
        }
        $stmt->execute();
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM categories WHERE id = $id");
    }
    redirect(BASE_URL . '/modules/admin/categories.php');
}

$categories = $conn->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = '分类管理';
require_once __DIR__ . '/../../header.php';
?>

<div style="max-width:600px; margin:0 auto; padding:100px 20px 40px;">
    <h2>分类管理</h2>
    <form method="POST" style="display:flex; gap:10px; margin-bottom:20px; align-items:center;">
        <input type="hidden" name="action" value="save">
        <input type="text" name="name" placeholder="新分类名称" required style="flex:1; padding:10px; border:1px solid var(--border); border-radius:8px;">
        <input type="number" name="sort_order" value="0" style="width:60px; padding:10px; border:1px solid var(--border); border-radius:8px;" placeholder="排序">
        <button type="submit" class="btn-auth" style="padding:10px 18px;">添加</button>
    </form>
    <?php foreach ($categories as $cat): ?>
        <form method="POST" style="display:flex; gap:10px; align-items:center; padding:12px; border-bottom:1px solid var(--border-light);">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
            <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" style="flex:1; padding:8px; border:1px solid var(--border); border-radius:8px;">
            <input type="number" name="sort_order" value="<?php echo $cat['sort_order']; ?>" style="width:60px; padding:8px; border:1px solid var(--border); border-radius:8px;">
            <button type="submit" class="btn-auth" style="background:var(--mc-green); padding:8px 12px;">保存</button>
            <button type="submit" name="action" value="delete" class="btn-auth" style="background:#e74c3c; padding:8px 12px;" onclick="return confirm('确定删除？')">删除</button>
        </form>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>