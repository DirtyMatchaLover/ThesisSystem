<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pasig Catholic College</title>

  <!-- ✅ Load CSS -->
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">

  <!-- Optional: Google Fonts for cleaner UI -->
  <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
