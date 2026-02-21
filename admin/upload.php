<?php
require_once '../core/db.php';
checkAuth();

header('Content-Type: application/json');

if (!isset($_FILES['file'])) {
    echo json_encode(['error' => '没有文件被上传']);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'pdf'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['error' => '不支持的文件格式']);
    exit;
}

$sub_dir = date('Y') . '/' . date('m');
$upload_dir = '../content/uploads/' . $sub_dir;
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$filename = uniqid() . '.' . $ext;
$target = $upload_dir . '/' . $filename;

if (move_uploaded_file($file['tmp_name'], $target)) {
    // 【修改】获取系统运行目录的绝对路径，彻底告别 404
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $base_path = dirname(dirname($_SERVER['SCRIPT_NAME']));
    if ($base_path === '/' || $base_path === '\\') $base_path = '';
    
    // 生成形如 http://xxx.com/rebirth/content/uploads/2026/02/xxx.jpg 的绝对网址
    $url = $protocol . $_SERVER['HTTP_HOST'] . $base_path . '/content/uploads/' . $sub_dir . '/' . $filename;
    echo json_encode(['location' => $url]); 
} else {
    echo json_encode(['error' => '上传失败，目录权限不足']);
}
?>