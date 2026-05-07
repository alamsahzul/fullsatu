<?php if (!isset($pageTitle)) $pageTitle = 'FullSatu Single Man League'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>
<header class="site-header">
  <div class="brand">
    <img src="assets/img/hero_logo.png" alt="FullSatu Logo" class="brand-logo-img">
    <div class="brand-text">
      <strong>FULLSATU</strong>
      <span>SINGLE MAN LEAGUE</span>
    </div>
  </div>
  <nav class="main-nav">
    <a href="index.php" class="active">HOME</a>
    <a href="standings.php">STANDINGS</a>
    <a href="matches.php">MATCHES</a>
    <a href="#">PLAYERS</a>
    <a href="#">RULES</a>
    <a href="#">ABOUT</a>
  </nav>
  <div class="header-actions">
    <a href="#" class="btn btn-primary">JOIN LEAGUE</a>
  </div>
</header>
<main class="main-content">
