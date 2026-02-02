<?php require_once __DIR__ . '/../../helpers.php'; ?>

<footer class="site-footer">
  <div class="footer-content">
    <div class="footer-text">
      <p>© 2000 - Company, Inc. All rights reserved. Address Address</p>
    </div>
    <nav class="footer-nav">
      <a href="<?= route('home') ?>">Home</a>
      <span class="separator">|</span>
      <a href="<?= route('about') ?>">About</a>
      <span class="separator">|</span>
      <a href="<?= route('research') ?>">Research</a>
      <span class="separator">|</span>
      <a href="<?= route('thesis/create') ?>">Publish</a>
    </nav>
  </div>
</footer>
</body>
</html>