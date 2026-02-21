<?php
require_once '../core/db.php';
checkAuth();

$post_count = $pdo->query("SELECT COUNT(*) FROM rb_posts")->fetchColumn();
$cat_count = $pdo->query("SELECT COUNT(*) FROM rb_categories")->fetchColumn();
$comment_count = $pdo->query("SELECT COUNT(*) FROM rb_comments")->fetchColumn();
$db_version = $pdo->query("SELECT VERSION()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Rebirth 控制台</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content">
        <?php renderTopBar('Dashboard'); ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card">
                <h3 style="color:#636e72; font-size:0.9rem;">文章总数</h3>
                <div style="font-size:2.5rem; font-weight:800; color:var(--primary);"><?= $post_count ?></div>
            </div>
            <div class="card">
                <h3 style="color:#636e72; font-size:0.9rem;">分类目录</h3>
                <div style="font-size:2.5rem; font-weight:800; color:#0984e3;"><?= $cat_count ?></div>
            </div>
            <div class="card">
                <h3 style="color:#636e72; font-size:0.9rem;">评论总数</h3>
                <div style="font-size:2.5rem; font-weight:800; color:#00b894;"><?= $comment_count ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="card">
                <h3>快速开始</h3>
                <p style="color:#636e72; margin: 15px 0;">欢迎回到 Rebirth。这是一个纯净的创作空间。</p>
                <a href="writer.php" class="btn"><i class="ri-pen-nib-line"></i> 开始创作新文章</a>
            </div>

            <div class="card" style="font-size: 0.9rem; color: #2d3436;">
                <h3 style="margin-bottom: 15px;"><i class="ri-server-line"></i> 系统信息</h3>
                <div style="display:flex; justify-content:space-between; padding: 8px 0; border-bottom: 1px dashed #eee;">
                    <span style="color:#636e72;">系统版本</span>
                    <strong>Rebirth V<?= getOption('site_version', '1.1.0') ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 8px 0; border-bottom: 1px dashed #eee;">
                    <span style="color:#636e72;">PHP 版本</span>
                    <strong><?= PHP_VERSION ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 8px 0; border-bottom: 1px dashed #eee;">
                    <span style="color:#636e72;">服务器环境</span>
                    <strong><?= explode(' ', $_SERVER['SERVER_SOFTWARE'])[0] ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 8px 0;">
                    <span style="color:#636e72;">数据库</span>
                    <strong>MySQL <?= explode('-', $db_version)[0] ?></strong>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>