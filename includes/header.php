<?php if (!isset($pageTitle)) $pageTitle = 'FullSatu Single Man League'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?= e($pageTitle) ?></title>
  
  <!-- Open Graph Meta Tags -->
  <?php 
    $fullBaseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $ogImageUrl = (isset($ogImage) && strpos($ogImage, 'http') === 0) ? $ogImage : $fullBaseUrl . (isset($ogImage) ? $ogImage : base_url('assets/img/og-default.jpg'));
  ?>
  <meta property="og:title" content="<?= e($ogTitle ?? $pageTitle) ?>">
  <meta property="og:description" content="<?= e($ogDescription ?? 'Pantau hasil pertandingan dan jadwal liga FullSatu Single Man League secara real-time.') ?>">
  <meta property="og:image" content="<?= e($ogImageUrl) ?>">
  <meta property="og:url" content="<?= $fullBaseUrl . $_SERVER['REQUEST_URI'] ?>">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=<?= time() ?>">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
  <div class="brand">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="FullSatu Logo" class="brand-logo-img">
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
      <?php $uri = $_SERVER['REQUEST_URI']; ?>
      <a href="<?= base_url('') ?>" class="<?= (strpos($uri, 'index') !== false || $uri == '/' || $uri == '/fullsatu/') ? 'active' : '' ?>">HOME</a>
      <a href="<?= base_url('liga') ?>" class="<?= strpos($uri, 'liga') !== false ? 'active' : '' ?>">LIGA</a>
      <a href="<?= base_url('knockout') ?>" class="<?= strpos($uri, 'knockout') !== false ? 'active' : '' ?>">KNOCKOUT</a>
      <a href="<?= base_url('hybrid') ?>" class="<?= strpos($uri, 'hybrid') !== false ? 'active' : '' ?>">HYBRID</a>
      <a href="<?= base_url('rank') ?>" class="<?= strpos($uri, 'rank') !== false ? 'active' : '' ?>">RANK</a>
      <a href="<?= base_url('rules') ?>" class="<?= strpos($uri, 'rules') !== false ? 'active' : '' ?>">RULES</a>
      <a href="<?= base_url('about') ?>" class="<?= strpos($uri, 'about') !== false ? 'active' : '' ?>">ABOUT</a>
    </nav>
    <div class="header-actions">
      <a href="#" class="btn btn-primary">JOIN LEAGUE</a>
    </div>
  </div>
  </div>
</header>
<main class="main-content">
