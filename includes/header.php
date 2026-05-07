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
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=<?= time() ?>">
</head>
<body>
<header class="site-header">
  <div class="brand">
    <img src="<?= base_url('assets/img/hero_logo.png') ?>" alt="FullSatu Logo" class="brand-logo-img">
    <div class="brand-text">
      <strong>FULLSATU</strong>
      <span>SINGLE MAN LEAGUE</span>
    </div>
  </div>
  <button class="mobile-toggle" aria-label="Toggle Menu">
    <span class="hamburger"></span>
  </button>

  <div class="header-menu">
    <nav class="main-nav">
      <a href="<?= base_url('index.php') ?>" class="active">HOME</a>
      <a href="<?= base_url('standings.php') ?>">STANDINGS</a>
      <a href="<?= base_url('matches.php') ?>">MATCHES</a>
      <a href="#">PLAYERS</a>
      <a href="#">RULES</a>
      <a href="#">ABOUT</a>
    </nav>
    <div class="header-actions">
      <a href="#" class="btn btn-primary">JOIN LEAGUE</a>
    </div>
  </div>
</header>
<main class="main-content">
