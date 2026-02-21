<?php
/**
 * Rebirth Database Connector
 */

if (!file_exists(__DIR__ . '/config.php') && strpos($_SERVER['PHP_SELF'], 'install') === false) {
    die('<meta charset="utf-8"><div style="text-align:center;margin-top:20vh;font-family:sans-serif;"><h2>系统未初始化</h2><p>请访问 <a href="/install/index.php">/install/index.php</a> 完成安装。</p></div>');
}
require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

function checkAuth() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function getOption($name, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT option_value FROM rb_options WHERE option_name = ?");
        $stmt->execute([$name]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}
?>