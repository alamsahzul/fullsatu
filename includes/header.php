<?php if (!isset($pageTitle)) $pageTitle = 'FullSatu Single Man League'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="brand">
    <div class="logo">FS</div>
    <div>
      <strong>FullSatu</strong>
      <span>Single Man League</span>
    </div>
  </div>
  <nav>
    <a href="index.php">Klasemen</a>
    <a href="matches.php">Jadwal</a>
    <a href="admin/players.php">Admin</a>
  </nav>
</header>
<main class="container">
