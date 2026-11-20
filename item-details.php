<?php
require_once __DIR__ . '/config/config.php';
$pdo = getDBConnection();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT items.*, users.full_name AS reporter_name, users.phone AS reporter_phone
     FROM items JOIN users ON users.id = items.user_id
     WHERE items.id = :id"
);
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item || (!$item['is_active'])) {
    set_flash('danger', 'That item report could not be found.');
    redirect('search.php');
}

// Only the owner/reporter or an admin may view a non-approved report
if ($item['status'] !== 'approved') {
    if (!is_logged_in() || (!is_admin() && (int)$_SESSION['user_id'] !== (int)$item['user_id'])) {
        set_flash('warning', 'This report is still pending review.');
        redirect('search.php');
    }
}

$isOwner = is_logged_in() && (int)$_SESSION['user_id'] === (int)$item['user_id'];

$pageTitle = e($item['item_name']) . ' | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <a href="<?= BASE_URL ?>/search.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Back to Items</a>

  <div class="row g-4 mt-1">
    <div class="col-lg-6">
      <img src="<?= $item['photo'] ? UPLOAD_URL . e($item['photo']) : 'https://placehold.co/600x450?text=No+Image' ?>"
           class="w-100 rounded-4 shadow-sm" style="object-fit:cover; max-height:450px;" alt="<?= e($item['item_name']) ?>">
    </div>
    <div class="col-lg-6">
      <div class="d-flex justify-content-between align-items-start">
        <h3 class="fw-bold mb-1"><?= e($item['item_name']) ?></h3>
        <span class="badge bg-<?= $item['report_type'] === 'lost' ? 'danger' : 'success' ?> fs-6"><?= e(ucfirst($item['report_type'])) ?></span>
      </div>
      <?= status_badge($item['status']) ?>

      <table class="table table-borderless mt-3">
        <tr><th class="text-muted" style="width:160px;">Category</th><td><?= e($item['category']) ?></td></tr>
        <?php if ($item['color']): ?><tr><th class="text-muted">Color</th><td><?= e($item['color']) ?></td></tr><?php endif; ?>
        <?php if ($item['brand']): ?><tr><th class="text-muted">Brand</th><td><?= e($item['brand']) ?></td></tr><?php endif; ?>
        <tr><th class="text-muted">Location <?= $item['report_type'] === 'lost' ? 'Lost' : 'Found' ?></th><td><?= e($item['location']) ?></td></tr>
        <tr><th class="text-muted">Date <?= $item['report_type'] === 'lost' ? 'Lost' : 'Found' ?></th><td><?= date('M j, Y', strtotime($item['item_date'])) ?></td></tr>
        <?php if ($item['reward']): ?><tr><th class="text-muted">Reward</th><td><?= e($item['reward']) ?></td></tr><?php endif; ?>
        <tr><th class="text-muted">Description</th><td><?= nl2br(e($item['description'])) ?></td></tr>
        <tr><th class="text-muted"><?= $item['report_type'] === 'found' ? 'Finder' : 'Reported by' ?></th><td><?= e($item['reporter_name']) ?></td></tr>
        <?php if ($item['contact_phone']): ?><tr><th class="text-muted">Contact</th><td><?= e($item['contact_phone']) ?></td></tr><?php endif; ?>
      </table>

      <?php if (!$isOwner): ?>
        <div class="d-flex gap-2 mt-3">
          <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>/claim.php?item_id=<?= $item['id'] ?>" class="btn btn-success px-4">
              <?= $item['report_type'] === 'found' ? 'This is My Item' : 'I Found This' ?>
            </a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-success px-4">Login to Claim</a>
          <?php endif; ?>
          <?php if ($item['contact_phone']): ?>
            <a href="tel:<?= e($item['contact_phone']) ?>" class="btn btn-outline-primary px-4">Contact <?= $item['report_type'] === 'found' ? 'Finder' : 'Reporter' ?></a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-info mt-3 mb-0">This is your own report. View its status in <a href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a>.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
