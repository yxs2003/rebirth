<?php
require_once '../core/db.php';
checkAuth();

if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE rb_comments SET is_approved = 1 WHERE id = ?")->execute([intval($_GET['approve'])]);
    header("Location: comments.php"); exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM rb_comments WHERE id = ?")->execute([intval($_GET['delete'])]);
    header("Location: comments.php"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'])) {
    $pdo->prepare("UPDATE rb_comments SET reply_content = ? WHERE id = ?")->execute([$_POST['reply'], intval($_POST['reply_id'])]);
    header("Location: comments.php"); exit;
}

$comments = $pdo->query("SELECT c.*, p.title as post_title FROM rb_comments c LEFT JOIN rb_posts p ON c.post_id = p.id ORDER BY c.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title>评论管理 - Rebirth</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content">
        <?php renderTopBar('💬 评论管理'); ?>
        <div class="card">
            <table style="width:100%; border-collapse: collapse; text-align:left;">
                <tr style="border-bottom:2px solid #eee;"><th>ID</th><th>评论人</th><th>所属文章</th><th>内容</th><th>回复内容</th><th>状态</th><th>操作</th></tr>
                <?php foreach($comments as $c): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:15px 5px;">#<?= $c['id'] ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="<?= $c['avatar'] ?>" style="width:30px; border-radius:50%;">
                            <div><b><?= $c['nickname'] ?></b><br><small style="color:#999;"><?= $c['email'] ?></small></div>
                        </div>
                    </td>
                    <td><?= mb_substr($c['post_title'], 0, 10) ?>...</td>
                    <td style="max-width:200px; color:#636e72;"><?= $c['content'] ?></td>
                    <td style="max-width:150px; color:#0984e3;"><?= $c['reply_content'] ?: '未回复' ?></td>
                    <td><?= $c['is_approved'] ? '<span style="color:green;">已展示</span>' : '<span style="color:orange;">待审核</span>' ?></td>
                    <td>
                        <?php if(!$c['is_approved']): ?>
                            <a href="?approve=<?= $c['id'] ?>" class="btn" style="padding:5px 10px; background:#00b894;">通过</a>
                        <?php endif; ?>
                        <button onclick="document.getElementById('reply-box-<?= $c['id'] ?>').style.display='block'" class="btn" style="padding:5px 10px;">回复</button>
                        <a href="?delete=<?= $c['id'] ?>" class="btn-danger" style="padding:5px 10px;" onclick="return confirm('确定删除？')">删除</a>
                        
                        <form id="reply-box-<?= $c['id'] ?>" method="POST" style="display:none; margin-top:10px;">
                            <input type="hidden" name="reply_id" value="<?= $c['id'] ?>">
                            <textarea name="reply" style="width:100%; padding:5px;" rows="2" placeholder="输入回复内容"><?= $c['reply_content'] ?></textarea>
                            <button class="btn" style="padding:5px 10px; margin-top:5px;">保存回复</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>
</div>
</body></html>