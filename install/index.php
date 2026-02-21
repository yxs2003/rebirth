<?php
/**
 * Rebirth Blog System Installer V4.5
 * Author: fuhua
 */
error_reporting(0);
session_start();

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$msg = '';
$lock_file = '../core/install.lock';

if (file_exists($lock_file)) {
    die('<body style="background:#f0f2f5;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;">
            <div style="background:#fff;padding:40px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.1);text-align:center;">
                <h2 style="color:#333;">Rebirth 已安装</h2>
                <p style="color:#666;">如需重装，请删除 core/install.lock 文件。</p>
            </div>
         </body>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $db_host = $_POST['db_host']; $db_user = $_POST['db_user']; $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name']; $admin_user = $_POST['admin_user']; $admin_pass = $_POST['admin_pass'];
    $build_dir = isset($_POST['build_dir']) && !empty($_POST['build_dir']) ? $_POST['build_dir'] : 'article';

    try {
        $dsn = "mysql:host=$db_host;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");

        $sqlContent = file_get_contents('install.sql');
        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
        foreach ($statements as $stmt) { if (!empty($stmt)) $pdo->exec($stmt); }

        // 覆盖自定义的构建目录
        $pdo->exec("UPDATE rb_options SET option_value = '$build_dir' WHERE option_name = 'build_dir'");

        $password_hash = password_hash($admin_pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO rb_users (username, password, nickname) VALUES (?, ?, ?)");
        $stmt->execute([$admin_user, $password_hash, '超级管理员']);

        $config_content = "<?php\ndefine('DB_HOST', '$db_host');\ndefine('DB_NAME', '$db_name');\ndefine('DB_USER', '$db_user');\ndefine('DB_PASS', '$db_pass');\ndefine('RB_ROOT', dirname(__DIR__));\n";
        file_put_contents('../core/config.php', $config_content);

        // 【重磅修复】安装时自动创建所有必须环境目录
        $required_dirs = ['../assets/css', '../assets/js', '../content/uploads', '../' . $build_dir];
        foreach ($required_dirs as $dir) { if (!is_dir($dir)) mkdir($dir, 0777, true); }

        file_put_contents($lock_file, 'Rebirth Installed on ' . date('Y-m-d H:i:s'));
        $step = 3; 

    } catch (PDOException $e) { $msg = "数据库错误: " . $e->getMessage(); $step = 1;
    } catch (Exception $e) { $msg = "系统错误: " . $e->getMessage(); $step = 1; }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"><title>安装 Rebirth V4.5</title>
    <style>
        :root { --primary: #6c5ce7; --bg: #dfe6e9; --glass: rgba(255, 255, 255, 0.7); }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: linear-gradient(135deg, #a8c0ff 0%, #3f2b96 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px;}
        .container { width: 500px; background: var(--glass); backdrop-filter: blur(20px); border-radius: 20px; padding: 40px; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37); border: 1px solid rgba(255, 255, 255, 0.18); }
        .logo { text-align: center; margin-bottom: 20px; font-size: 2em; font-weight: 800; color: #2d3436; } .logo span { color: var(--primary); }
        h3 { margin-bottom: 20px; color: #636e72; font-weight: 600; text-align: center; }
        .form-group { margin-bottom: 15px; } .form-group label { display: block; margin-bottom: 8px; font-size: 0.9em; color: #2d3436; font-weight: bold; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.5); border-radius: 8px; outline: none; transition: 0.3s; }
        .form-group input:focus { border-color: var(--primary); background: #fff; }
        .btn { width: 100%; padding: 14px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-size: 1.1em; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #5649c0; transform: translateY(-2px); }
        .error { background: rgba(255, 118, 117, 0.2); color: #d63031; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">Re<span>birth</span> V4.5</div>
    <?php if (!empty($msg)) echo "<div class='error'>$msg</div>"; ?>
    <?php if ($step === 1): ?>
    <h3>环境配置与账号初始化</h3>
    <form method="POST" action="?step=2">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="form-group"><label>数据库地址</label><input type="text" name="db_host" value="localhost" required></div>
            <div class="form-group"><label>数据库名</label><input type="text" name="db_name" value="rebirth_blog" required></div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="form-group"><label>数据库用户</label><input type="text" name="db_user" value="root" required></div>
            <div class="form-group"><label>数据库密码</label><input type="password" name="db_pass"></div>
        </div>
        <hr style="border:0; border-top:1px solid rgba(0,0,0,0.1); margin: 15px 0;">
        <div class="form-group">
            <label>文章生成自定义目录 (默认 article)</label>
            <input type="text" name="build_dir" value="article" placeholder="例如 html、posts">
            <small style="color:#666;">系统会自动在根目录创建此文件夹用于存放静态网页。</small>
        </div>
        <hr style="border:0; border-top:1px solid rgba(0,0,0,0.1); margin: 15px 0;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="form-group"><label>管理员账号</label><input type="text" name="admin_user" placeholder="admin" required></div>
            <div class="form-group"><label>管理员密码</label><input type="password" name="admin_pass" required></div>
        </div>
        <button type="submit" class="btn">🚀 开始安装系统</button>
    </form>
    <?php elseif ($step === 3): ?>
    <div style="text-align: center;">
        <h1 style="font-size: 4em; margin:0; color:#00b894;">✔</h1>
        <h3 style="color: #2d3436;">史诗级部署完成！</h3>
        <p style="color: #636e72; margin-bottom: 20px;">必须的依赖目录已自动为您创建完成。</p>
        <p style="font-size: 0.9em; background: rgba(255,255,255,0.5); padding: 10px; border-radius: 5px; margin-bottom: 20px;">为了安全，请手动删除 /install 目录</p>
        <a href="../admin/login.php" class="btn" style="text-decoration:none; display:inline-block;">登入控制台</a>
    </div>
    <?php endif; ?>
</div>
</body></html>