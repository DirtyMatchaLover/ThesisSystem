<?php require __DIR__ . '/../layout/header.php'; ?>
<section class="auth">
  <div class="card">
    <h2>Create Account</h2>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('auth/register') ?>">
      <?php csrf_field(); ?>
      <label>Name</label>
      <input type="text" name="name" required>
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button class="btn w-100" type="submit">Register</button>
    </form>
    <p class="muted mt-2">Have an account? <a href="<?= url('auth/login') ?>">Login</a></p>
  </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
