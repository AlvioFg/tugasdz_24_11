<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, nama_lengkap, password_hash FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nama'] = $user['nama_lengkap'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
<div class="login-card">
    <h1>Login Admin HR</h1>
    <p class="subtitle">Masuk untuk mengelola data karyawan & absensi</p>

    <?php if ($error): ?>
        <div class="msg msg-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required autofocus>

        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>

        <button type="submit" class="btn btn-full">Masuk</button>
    </form>
    <p class="hint">Default: <strong>admin</strong> / <strong>password</strong></p>
</div>
</body>
</html>