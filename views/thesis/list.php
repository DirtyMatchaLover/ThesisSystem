<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/navigation.php'; ?>

<div class="container">
  <h2 class="section-title">My Submissions</h2>
  
  <?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
      <?= htmlspecialchars($_SESSION['flash_message']) ?>
      <?php unset($_SESSION['flash_message']); ?>
    </div>
  <?php endif; ?>
  
  <?php if (empty($items)): ?>
    <div class="card empty" style="padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
      No submissions yet. 
      <a href="<?= route('thesis/create') ?>" style="color: #d32f2f; text-decoration: underline;">Submit your first thesis →</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="background: #f8f9fa;">
            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Title</th>
            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Status</th>
            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Submitted</th>
            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $t): ?>
          <tr>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">
              <?= htmlspecialchars($t['title']) ?>
            </td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">
              <span class="badge badge-<?= htmlspecialchars($t['status']) ?>" 
                    style="padding: 4px 8px; border-radius: 4px; font-size: 12px; 
                           background-color: <?php 
                             echo $t['status'] === 'approved' ? '#28a745' : 
                                  ($t['status'] === 'submitted' ? '#ffc107' : 
                                  ($t['status'] === 'under_review' ? '#17a2b8' : '#6c757d')); 
                           ?>; color: white;">
                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $t['status']))) ?>
              </span>
            </td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">
              <?= htmlspecialchars(date('Y-m-d', strtotime($t['created_at']))) ?>
            </td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">
              <a class="btn btn-light" 
                 href="<?= route('thesis/show') ?>&id=<?= $t['id'] ?>" 
                 style="padding: 6px 12px; background: #f8f9fa; border: 1px solid #dee2e6; 
                        border-radius: 4px; text-decoration: none; color: #333;">
                View
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
  
  <div style="margin-top: 20px;">
    <a href="<?= route('thesis/create') ?>" 
       class="btn" 
       style="padding: 10px 20px; background-color: #d32f2f; color: white; 
              border: none; border-radius: 4px; text-decoration: none; 
              display: inline-block;">
      Upload New Thesis
    </a>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>