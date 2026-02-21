<?php
session_start();
require_once '../core/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 预处理防止 SQL 注入
    $stmt = $pdo->prepare("SELECT * FROM rb_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nickname'] = $user['nickname'];
        header("Location: index.php");
        exit;
    } else {
        $error = "账号或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rebirth - 登录</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="glass-panel">
            <h2 style="text-align:center; margin-bottom:20px;">Rebirth Admin</h2>
            <?php if ($error): ?>
                <div style="color: #d63031; background: #fab1a0; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <label style="font-weight:500; font-size:0.9rem;">用户名</label>
                <input type="text" name="username" class="form-input" required>
                
                <label style="font-weight:500; font-size:0.9rem;">密码</label>
                <input type="password" name="password" class="form-input" required>
                
                <button type="submit" class="btn" style="width:100%;">登 录</button>
            </form>
            <p style="text-align:center; margin-top:20px; font-size:0.8rem; color:#636e72;">
                Rebirth Blog System
            </p>
        </div>
    </div>
</body>
</html>