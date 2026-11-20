<?php
require_once __DIR__ . '/config/config.php';
$pdo = getDBConnection();

// Latest 8 approved items for the homepage grid
$stmt = $pdo->query(
    "SELECT items.*, users.full_name AS reporter_name
     FROM items
     JOIN users ON users.id = items.user_id
     WHERE items.status = 'approved' AND items.is_active = 1
     ORDER BY items.created_at DESC
     LIMIT 8"
);
$recentItems = $stmt->fetchAll();

$pageTitle = 'Home | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <div class="hero-section">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="hero-title">Lost Something? <br><span class="text-primary">Found</span> Something?</h1>
        <p class="text-muted fs-5 mt-3">Help return lost items to their rightful owners, or report items you've found around Miva Open University.</p>
        <div class="d-flex flex-wrap gap-2 mt-4">
          <a href="<?= BASE_URL ?>/report-lost.php" class="btn btn-primary btn-lg px-4">Report Lost Item</a>
          <a href="<?= BASE_URL ?>/report-found.php" class="btn btn-outline-primary btn-lg px-4">Report Found Item</a>
        </div>
      </div>
      <div class="col-lg-5 text-center d-none d-lg-block">
        <i class="bi bi-search-heart" style="font-size: 10rem; color: var(--primary); opacity:.15;"></i>
      </div>
    </div>
  </div>

  <div class="row text-center my-5 g-4">
    <div class="col-md-3">
      <i class="bi bi-clipboard-plus fs-1 text-primary"></i>
      <h6 class="fw-bold mt-2">Report Lost or Found</h6>
      <p class="text-muted small">Fill out a simple form with item details</p>
    </div>
    <div class="col-md-3">
      <i class="bi bi-megaphone fs-1 text-primary"></i>
      <h6 class="fw-bold mt-2">We Publish It</h6>
      <p class="text-muted small">Your report is reviewed and published</p>
    </div>
    <div class="col-md-3">
      <i class="bi bi-check2-circle fs-1 text-primary"></i>
      <h6 class="fw-bold mt-2">Match &amp; Verify</h6>
      <p class="text-muted small">We help match items with the right owner</p>
    </div>
    <div class="col-md-3">
      <i class="bi bi-box-seam fs-1 text-primary"></i>
      <h6 class="fw-bold mt-2">Return Item</h6>
      <p class="text-muted small">Items are returned safely and securely</p>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Recently Reported Items</h4>
    <a href="<?= BASE_URL ?>/search.php" class="btn btn-sm btn-outline-primary">Browse All</a>
  </div>

  <div class="row g-4">
    <?php if (empty($recentItems)): ?>
      <p class="text-muted">No items reported yet. Be the first to report a lost or found item.</p>
    <?php endif; ?>

    <?php foreach ($recentItems as $item): ?>
      <div class="col-sm-6 col-lg-3">
        <a href="<?= BASE_URL ?>/item-details.php?id=<?= (int)$item['id'] ?>" class="text-decoration-none text-dark">
          <div class="item-card position-relative">
            <span class="badge type-badge bg-<?= $item['report_type'] === 'lost' ? 'danger' : 'success' ?>">
              <?= e(ucfirst($item['report_type'])) ?>
            </span>
            <img class="item-thumb" src="<?= $item['photo'] ? UPLOAD_URL . e($item['photo']) : 'https://placehold.co/400x300?text=No+Image' ?>" alt="<?= e($item['item_name']) ?>">
            <div class="card-body">
              <h6 class="fw-bold mb-1 text-truncate"><?= e($item['item_name']) ?></h6>
              <p class="text-muted small mb-1"><?= e($item['category']) ?></p>
              <p class="text-muted small mb-0"><i class="bi bi-geo-alt"></i> <?= e($item['location']) ?></p>
              <p class="text-muted small mb-0"><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($item['item_date'])) ?></p>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
