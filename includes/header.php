<?php
/*
 * File: includes/header.php
 * Purpose: Render the shared document head, navigation, and opening content container.
 * Access: Included on guest, user, and admin pages.
 * Tables: None.
 */
// Use the session helper when available so the navigation reflects the signed-in account.
$nav_user = function_exists('current_user') ? current_user() : null;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Feedback System') ?></title>
  <?php if (function_exists('csrf_token')): ?><meta name="csrf-token" content="<?= e(csrf_token()) ?>"><?php endif; ?>
  <script>
    // Apply the saved preference before the page paints to avoid a theme flash.
    try { document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light'); } catch (e) {}
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark mb-4" data-bs-theme="dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">💬 Feedback & Comments</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php if ($nav_user): ?>
          <li class="nav-item"><a class="nav-link" href="index.php">All Feedback</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav align-items-md-center gap-md-2">
        <?php if ($nav_user): ?>
          <li class="nav-item text-white me-md-2"><?= e($nav_user['full_name']) ?> <?= role_badge($nav_user['role']) ?></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">☰ Menu</a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" id="themeToggle">🌙 Dark mode</button></li>
            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#aboutModal">ℹ️ About the website</button></li>
          </ul>
        </li>
        <?php if ($nav_user): ?>
          <li class="nav-item"><a class="btn btn-sm btn-primary" href="logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container pb-5">
