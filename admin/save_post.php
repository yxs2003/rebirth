<?php
/**
 * 保存文章并静态触发 V4.5
 */
require_once '../core/db.php';
require_once '../core/functions.php';
checkAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = $_POST['content'];
    $category_id = intval($_POST['category_id']);
    $summary = trim($_POST['summary']);
    $cover_image = trim($_POST['cover_image']);
    $custom_css = $_POST['custom_css'];
    
    // V4.5 接收新字段
    $tags = trim($_POST['tags'] ?? '');
    $created_at = $_POST['created_at'] ?: date('Y-m-d H:i:s');
    $show_meta = isset($_POST['show_meta']) ? 1 : 0;

    if (empty($title) || empty($slug)) {
        echo json_encode(['success' => false, 'message' => '标题和 URL 别名不能为空']);
        exit;
    }

    if (empty($summary)) {
        $summary = mb_substr(trim(strip_tags($content)), 0, 120) . '...';
    }

    try {
        if ($post_id > 0) {
            // 【关键修复】明确 p.slug 防止歧义
            $old = $pdo->prepare("SELECT p.slug, c.slug as cat_slug FROM rb_posts p LEFT JOIN rb_categories c ON p.category_id=c.id WHERE p.id=?");
            $old->execute([$post_id]);
            $oldData = $old->fetch();

            $stmt = $pdo->prepare("UPDATE rb_posts SET title=?, slug=?, content=?, category_id=?, tags=?, summary=?, cover_image=?, custom_css=?, created_at=?, show_meta=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $category_id, $tags, $summary, $cover_image, $custom_css, $created_at, $show_meta, $post_id]);
            
            if ($oldData && ($oldData['slug'] !== $slug || $_POST['category_id'] != $category_id)) {
                $build_dir = getOption('build_dir', 'article');
                $old_dir = dirname(__DIR__) . '/' . $build_dir . '/' . ($oldData['cat_slug'] ?: 'uncategorized');
                $old_file = $old_dir . '/' . $oldData['slug'] . '.html';
                if (file_exists($old_file)) @unlink($old_file);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO rb_posts (title, slug, content, category_id, tags, summary, cover_image, custom_css, created_at, show_meta, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$title, $slug, $content, $category_id, $tags, $summary, $cover_image, $custom_css, $created_at, $show_meta]);
            $post_id = $pdo->lastInsertId();
        }

        // 调用生成前，通过 try-catch 包裹，防止它打印致命报错破坏 JSON
        try {
            $result = generatePostHtml($post_id);
            generateHomeHtml(); 
            if ($result === true) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => '保存成功，但生成失败: ' . $result]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '保存成功，但在生成静态文件时发生了致命错误: ' . $e->getMessage()]);
        }

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => '此 URL 别名已被占用，请修改。']);
        } else {
            echo json_encode(['success' => false, 'message' => '数据库异常: ' . $e->getMessage()]);
        }
    }
}
?>