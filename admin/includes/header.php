<?php
if (!isset($pageTitle)) $pageTitle = 'Admin Panel - FullSatu';
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
  <style>
    /* Admin specific styles */
    body.admin-body {
      display: flex;
      min-height: 100vh;
      margin: 0;
      background-color: var(--color-bg-dark);
    }
    .admin-sidebar {
      width: 250px;
      background: #03150d;
      border-right: 1px solid var(--color-border);
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
      gap: 30px;
    }
    .admin-brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .admin-brand img { height: 40px; }
    .admin-brand strong { color: var(--color-primary); font-family: var(--font-heading); font-size: 20px; }
    
    .admin-nav {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .admin-nav a {
      padding: 12px 16px;
      border-radius: 8px;
      color: var(--color-text-muted);
      font-weight: 600;
      transition: all 0.3s;
    }
    .admin-nav a:hover, .admin-nav a.active {
      background: var(--color-bg-light);
      color: var(--color-primary);
    }
    
    .admin-content {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }
    
    .admin-header {
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--color-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .admin-header h1 {
      font-size: 32px;
      margin: 0;
    }
    
    .admin-card {
      background: var(--color-bg-light);
      border: 1px solid var(--color-border);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
    }
    
    .admin-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    
    .stat-card {
      background: var(--color-bg-dark);
      border: 1px solid var(--color-border);
      padding: 20px;
      border-radius: 12px;
      text-align: center;
    }
    .stat-card h3 { font-size: 36px; color: var(--color-primary); margin-bottom: 5px;}
    .stat-card p { color: var(--color-text-muted); font-size: 14px; }
    
    .mobile-admin-toggle { display: none; }
    
    @media (max-width: 768px) {
      body.admin-body { flex-direction: column; }
      .admin-sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--color-border); padding: 20px; }
      .admin-content { padding: 20px; }
      .admin-nav { flex-direction: row; flex-wrap: wrap; }
      .admin-nav a { flex: 1; text-align: center; padding: 10px; font-size: 14px; }
    }
  </style>
</head>
<body class="admin-body">

<aside class="admin-sidebar">
  <div class="admin-brand">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo">
    <div><strong>ADMIN</strong></div>
  </div>
  <nav class="admin-nav">
    <?php $uri = $_SERVER['REQUEST_URI']; ?>
    <a href="<?= base_url('admin/index') ?>" class="<?= strpos($uri, 'index') !== false || $uri == '/admin/' || $uri == '/fullsatu/admin/' ? 'active' : '' ?>">Dashboard</a>
    <a href="<?= base_url('admin/players') ?>" class="<?= strpos($uri, 'players') !== false ? 'active' : '' ?>">Master Pemain</a>
    <a href="<?= base_url('admin/seasons') ?>" class="<?= strpos($uri, 'seasons') !== false ? 'active' : '' ?>">Musim (Season)</a>
    <a href="<?= base_url('admin/season_players') ?>" class="<?= strpos($uri, 'season_players') !== false ? 'active' : '' ?>">Pemain Musim Ini</a>
    <a href="<?= base_url('admin/generate') ?>" class="<?= strpos($uri, 'generate') !== false ? 'active' : '' ?>">Jadwal</a>
    <a href="<?= base_url('admin/matches') ?>" class="<?= strpos($uri, 'matches') !== false ? 'active' : '' ?>">Input Skor</a>
    <a href="<?= base_url('') ?>" target="_blank" style="margin-top: 20px; color: var(--color-text-green);">Ke Website Utama</a>
    <a href="<?= base_url('admin/logout') ?>" style="color: #ef4444;">Logout</a>
  </nav>
</aside>

<main class="admin-content">
