<?php
require_once '../core/db.php';
require_once '../core/functions.php';
checkAuth();

if (isset($_GET['gen'])) {
    $res = generatePostHtml(intval($_GET['gen']));
    generateHomeHtml();
    if ($res === true) echo "<script>alert('生成成功！');location.href='posts.php';</script>";
    else echo "<script>alert('生成失败：$res');</script>";
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM rb_posts WHERE id = ?")->execute([$id]);
    generateHomeHtml();
    header("Location: posts.php");
    exit;
}

// 修复 SQL 歧义：指明 p.slug 和 c.slug
$posts = $pdo->query("SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM rb_posts p LEFT JOIN rb_categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
$build_dir = getOption('build_dir', 'article');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title>文章管理 - Rebirth</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>table { width: 100%; border-collapse: collapse; margin-top:10px; } table th, table td { padding: 15px; border-bottom: 1px solid #eee; text-align: left;}</style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content">
        <?php renderTopBar('📝 文章管理'); ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <p style="color:#636e72; margin:0;">管理您的所有文章与草稿。</p>
                <a href="writer.php" class="btn"><i class="ri-add-line"></i> 创作新文章</a>
            </div>
            <table>
                <thead>
                    <tr><th width="60">ID</th><th>标题</th><th>分类</th><th>状态</th><th>发布日期</th><th>操作</th></tr>
                </thead>
                <tbody>
                    <?php foreach($posts as $p): ?>
                    <tr>
                        <td style="color:#b2bec3;">#<?= $p['id'] ?></td>
                        <td style="font-weight:bold; color:#2d3436;">
                            <?= htmlspecialchars($p['title']) ?>
                            <a href="../<?= $build_dir ?>/<?= $p['cat_slug']?:'uncategorized' ?>/<?= $p['slug'] ?>.html" target="_blank" style="margin-left:10px; font-size:0.85rem; font-weight:normal; color:#0984e3; text-decoration:underline;">前台预览</a>
                        </td>
                        <td><span style="background:#f1f2f6; padding:4px 8px; border-radius:6px; font-size:0.85rem; color:#636e72; font-weight:bold;"><?= $p['cat_name'] ?: '未分类' ?></span></td>
                        <td><?= $p['status'] == 1 ? '<span style="color:#00b894"><i class="ri-checkbox-circle-fill"></i> 已发布</span>' : '<span style="color:#f39c12"><i class="ri-draft-fill"></i> 草稿</span>' ?></td>
                        <td style="color:#888; font-size:0.9rem;"><i class="ri-time-line"></i> <?= substr($p['created_at'], 0, 10) ?></td>
                        <td>
                            <a href="writer.php?id=<?= $p['id'] ?>" class="btn" style="padding:5px 12px; background:#0984e3; font-size:0.85rem;"><i class="ri-edit-2-line"></i> 编辑</a>
                            <a href="?gen=<?= $p['id'] ?>" class="btn" style="padding:5px 12px; background:#00b894; font-size:0.85rem;"><i class="ri-html5-line"></i> 静态化</a>
                            <button onclick="rbModal.confirm('删除不可恢复，真的要删除吗？', () => location.href='?delete=<?= $p['id'] ?>')" class="btn-danger" style="padding:5px 12px; border:none; border-radius:6px; font-size:0.85rem; font-weight:bold;"><i class="ri-delete-bin-line"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>