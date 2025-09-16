<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="container">
  <h2>Upload Thesis</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
      <?= htmlspecialchars($_SESSION['flash_message']) ?>
      <?php unset($_SESSION['flash_message']); ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" action="<?= route('thesis/create') ?>">
      <div class="form-group">
          <label for="title">Title <span style="color: red;">*</span></label>
          <input type="text" 
                 id="title" 
                 name="title" 
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                 required 
                 style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
      </div>

      <div class="form-group" style="margin-top: 15px;">
          <label for="author">Author(s) <span style="color: red;">*</span></label>
          <input type="text" 
                 id="author" 
                 name="author" 
                 value="<?= htmlspecialchars($_POST['author'] ?? current_user()['name'] ?? '') ?>"
                 required 
                 style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
      </div>

      <div class="form-group" style="margin-top: 15px;">
          <label for="abstract">Abstract <span style="color: red;">*</span></label>
          <textarea id="abstract" 
                    name="abstract" 
                    rows="5" 
                    required 
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?= htmlspecialchars($_POST['abstract'] ?? '') ?></textarea>
      </div>

      <div class="form-group" style="margin-top: 15px;">
          <label for="file">Upload File (PDF only, max 10MB) <span style="color: red;">*</span></label>
          <input type="file" 
                 id="file" 
                 name="file" 
                 accept="application/pdf" 
                 required 
                 style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
          <small style="color: #666;">Accepted format: PDF only. Maximum size: 10MB</small>
      </div>

      <button type="submit" 
              class="btn" 
              style="margin-top: 20px; background-color: #d32f2f; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
          Submit Thesis
      </button>
  </form>
</div>

<style>
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.btn:hover {
    background-color: #b71c1c !important;
}

.alert {
    margin: 1rem 0;
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>