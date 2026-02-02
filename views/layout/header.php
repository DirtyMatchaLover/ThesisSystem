<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load helpers if available, but don't crash if not
if (file_exists(__DIR__ . '/../../helpers.php')) {
    require_once __DIR__ . '/../../helpers.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ResearchHub - Academic Research Management</title>

  <!-- Bootstrap CSS from CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  
  <!-- Custom CSS Files (Split into modules) - FIXED PATHS -->
  <?php $cssVersion = '2.2.13'; // Update this version when CSS changes ?>
  <link rel="stylesheet" href="<?= asset('assets/css/base.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/header.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/dropdown.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/homepage.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/components.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/research.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/footer.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/mobile.css') ?>?v=<?= $cssVersion ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/dark-theme.css') ?>?v=<?= $cssVersion ?>">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<!-- Add Bootstrap JS before closing body tag -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>