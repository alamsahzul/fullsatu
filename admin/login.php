<?php
session_start();
require '../config/db.php';
require '../includes/functions.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}

$pageTitle = 'Login Admin - FullSatu';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=<?= time() ?>">
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--color-bg-dark); margin: 0; padding: 20px;">

  <div style="background: var(--color-bg-light); border: 1px solid var(--color-border); padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; text-align: center;">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" style="width: 80px; margin-bottom: 20px;">
    <h1 style="font-family: var(--font-heading); color: var(--color-primary); font-size: 28px; margin-bottom: 5px;">ADMIN LOGIN</h1>
    <p style="color: var(--color-text-muted); margin-bottom: 30px; font-size: 14px;">Masukkan kredensial untuk mengakses panel kontrol.</p>
    
    <?php if($error): ?>
      <div class="alert" style="background: #ef4444; color: white; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" style="text-align: left;">
      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 14px;">Username</label>
        <input type="text" name="username" required style="width: 100%; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px;" autofocus>
      </div>
      <div style="margin-bottom: 25px;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 14px;">Password</label>
        <input type="password" name="password" required style="width: 100%; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px;">
      </div>
      <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px;">LOGIN</button>
    </form>
    
    <div style="margin-top: 20px;">
      <a href="<?= base_url('') ?>" style="color: var(--color-text-muted); font-size: 13px; text-decoration: underline;">&larr; Kembali ke Website</a>
    </div>
  </div>

</body>
</html>
