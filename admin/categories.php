<?php
require_once '../core/db.php';
checkAuth();

// 添加或编辑
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = intval($_POST['id'] ?? 0);
    $parent_id = intval($_POST['parent_id']);
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $sort_order = intval($_POST['sort_order']);
    $show_in_nav = isset($_POST['show_in_nav']) ? 1 : 0;
    
    if ($name && $slug) {
        try {
            if ($_POST['action'] === 'edit' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE rb_categories SET parent_id=?, name=?, slug=?, sort_order=?, show_in_nav=? WHERE id=?");
                $stmt->execute([$parent_id, $name, $slug, $sort_order, $show_in_nav, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO rb_categories (parent_id, name, slug, sort_order, show_in_nav) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$parent_id, $name, $slug, $sort_order, $show_in_nav]);
            }
            header("Location: categories.php"); exit;
        } catch (Exception $e) { $error = "操作失败，别名可能重复或存在非法字符。"; }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($pdo->query("SELECT COUNT(*) FROM rb_posts WHERE category_id = $id")->fetchColumn() > 0) {
        $error = "无法删除：该分类下存在文章。";
    } elseif ($pdo->query("SELECT COUNT(*) FROM rb_categories WHERE parent_id = $id")->fetchColumn() > 0) {
        $error = "无法删除：存在子分类，请先删除子分类。";
    } else {
        $pdo->prepare("DELETE FROM rb_categories WHERE id = ?")->execute([$id]);
        header("Location: categories.php"); exit;
    }
}

// 递归获取分类树
function getCategoryTree($pdo, $parent_id = 0, $level = 0) {
    $stmt = $pdo->prepare("SELECT * FROM rb_categories WHERE parent_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$parent_id]);
    $cats = $stmt->fetchAll();
    $tree = [];
    foreach ($cats as $c) {
        $c['level'] = $level;
        $tree[] = $c;
        $tree = array_merge($tree, getCategoryTree($pdo, $c['id'], $level + 1));
    }
    return $tree;
}
$all_cats = getCategoryTree($pdo);

$edit_cat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM rb_categories WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_cat = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title>分类管理 - Rebirth</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }</style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content">
        <?php renderTopBar('📂 分类架构'); ?>
        <?php if(isset($error)): ?><div style="background:#fab1a0; color:#d63031; padding:15px; border-radius:8px; margin-bottom:20px;"><?= $error ?></div><?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 2.5fr; gap: 30px;">
            <div class="card" style="align-self: start;">
                <h3><?= $edit_cat ? '编辑分类' : '新建分类' ?></h3>
                <form method="POST" style="margin-top:20px;">
                    <input type="hidden" name="action" value="<?= $edit_cat ? 'edit' : 'add' ?>">
                    <?php if($edit_cat): ?><input type="hidden" name="id" value="<?= $edit_cat['id'] ?>"><?php endif; ?>
                    
                    <label style="display:block; margin-bottom:5px; font-weight:500;">父级分类</label>
                    <select name="parent_id" class="form-input">
                        <option value="0">顶级分类 (无父级)</option>
                        <?php foreach($all_cats as $c): if($edit_cat && $c['id'] == $edit_cat['id']) continue; ?>
                            <option value="<?= $c['id'] ?>" <?= ($edit_cat['parent_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                <?= str_repeat('&nbsp;&nbsp;', $c['level']) . '├ ' . htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label style="display:block; margin-bottom:5px; font-weight:500;">分类名称</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($edit_cat['name'] ?? '') ?>" required>
                    
                    <label style="display:block; margin-bottom:5px; font-weight:500;">URL 别名 (Slug)</label>
                    <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($edit_cat['slug'] ?? '') ?>" required>
                    
                    <label style="display:block; margin-bottom:5px; font-weight:500;">排序权重 (越小越靠前)</label>
                    <input type="number" name="sort_order" class="form-input" value="<?= htmlspecialchars($edit_cat['sort_order'] ?? '0') ?>">

                    <label style="display:block; margin-bottom:20px; cursor:pointer;">
                        <input type="checkbox" name="show_in_nav" value="1" <?= (!isset($edit_cat) || $edit_cat['show_in_nav']) ? 'checked' : '' ?>> 
                        在首页顶部导航栏显示
                    </label>
                    
                    <button type="submit" class="btn" style="width:100%"><i class="ri-save-line"></i> <?= $edit_cat ? '保存修改' : '确认添加' ?></button>
                    <?php if($edit_cat): ?><a href="categories.php" class="btn btn-cancel" style="width:100%; margin-top:10px; text-align:center;">取消编辑</a><?php endif; ?>
                </form>
            </div>

            <div class="card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="background:#f8f9fa;"><th>ID</th><th>层级与名称</th><th>URL别名</th><th>导航显示</th><th>排序</th><th>操作</th></tr>
                    <?php foreach($all_cats as $c): ?>
                    <tr>
                        <td style="color:#b2bec3;">#<?= $c['id'] ?></td>
                        <td style="font-weight:600;">
                            <?= str_repeat('<span style="color:#ccc; margin-right:10px;">|--</span>', $c['level']) ?>
                            <i class="ri-folder-3-fill" style="color:var(--primary); margin-right:5px;"></i> <?= htmlspecialchars($c['name']) ?>
                        </td>
                        <td style="color:#636e72;"><?= htmlspecialchars($c['slug']) ?></td>
                        <td><?= $c['show_in_nav'] ? '<i class="ri-eye-line" style="color:green;"></i>' : '<i class="ri-eye-off-line" style="color:#ccc;"></i>' ?></td>
                        <td><span style="background:#eee; padding:2px 8px; border-radius:4px; font-size:0.85rem;"><?= $c['sort_order'] ?></span></td>
                        <td>
                            <a href="?edit=<?= $c['id'] ?>" class="btn" style="padding:4px 10px; font-size:0.8rem; background:#0984e3;">编辑</a>
                            <button onclick="rbModal.confirm('确定删除该分类吗？', () => location.href='?delete=<?= $c['id'] ?>')" class="btn-danger" style="padding:4px 10px; font-size:0.8rem; border:none; border-radius:6px;">删除</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </main>
</div>
</body></html>