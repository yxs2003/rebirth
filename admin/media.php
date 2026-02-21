<?php
require_once '../core/db.php';
checkAuth();

// 支持单个和批量删除
if (isset($_GET['delete'])) {
    $file = $_GET['delete'];
    if (strpos($file, '..') === false && file_exists('../content/uploads/' . $file)) {
        unlink('../content/uploads/' . $file);
    }
    header("Location: media.php"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    foreach($_POST['files'] as $file) {
        if (strpos($file, '..') === false && file_exists('../content/uploads/' . $file)) {
            unlink('../content/uploads/' . $file);
        }
    }
    header("Location: media.php"); exit;
}

$images = [];
$dirs = array_filter(glob('../content/uploads/*/*'), 'is_dir');
foreach ($dirs as $dir) {
    $files = glob($dir . '/*.{jpg,jpeg,png,gif,webp,zip}', GLOB_BRACE);
    foreach ($files as $f) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $base_path = dirname(dirname($_SERVER['SCRIPT_NAME']));
        if ($base_path === '/' || $base_path === '\\') $base_path = '';
        
        $images[] = [
            'path' => $f,
            'rel_path' => str_replace('../content/uploads/', '', $f),
            'url' => $protocol . $_SERVER['HTTP_HOST'] . $base_path . substr($f, 2),
            'time' => filemtime($f)
        ];
    }
}
usort($images, function($a, $b) { return $b['time'] - $a['time']; });
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title>媒体库 - Rebirth</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; }
        .media-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; position:relative; }
        .media-img { width: 100%; height: 120px; object-fit: cover; }
        .media-actions { padding: 10px; display: flex; justify-content: space-between; }
        .bulk-check { position:absolute; top:10px; left:10px; transform:scale(1.5); cursor:pointer; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar_template.php'; ?>
    <main class="main-content">
        <?php renderTopBar('🖼️ 媒体库'); ?>
        <div class="card">
            <form method="POST" id="bulk-form">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <p style="color:#636e72; margin:0;">这里显示了所有你在编辑器中上传的文件。</p>
                    <button type="button" class="btn-danger" style="border:none;" onclick="if(confirm('确定删除选中的图片吗？')) document.getElementById('bulk-form').submit();"><i class="ri-delete-bin-line"></i> 批量删除</button>
                    <input type="hidden" name="bulk_delete" value="1">
                </div>
                <div class="media-grid">
                    <?php foreach($images as $img): ?>
                    <div class="media-card">
                        <input type="checkbox" name="files[]" value="<?= $img['rel_path'] ?>" class="bulk-check">
                        <img src="<?= $img['path'] ?>" class="media-img">
                        <div class="media-actions">
                            <button type="button" onclick="rbModal.prompt('图片绝对地址', [{value:'<?= $img['url'] ?>'}])" class="btn" style="padding:4px 8px; background:#0984e3; font-size:0.8rem;">链接</button>
                            <button type="button" onclick="rbModal.confirm('确定删除此图片？', () => location.href='?delete=<?= $img['rel_path'] ?>')" class="btn-danger" style="padding:4px 8px; border:none; border-radius:4px;">删除</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </main>
</div>
</body></html>