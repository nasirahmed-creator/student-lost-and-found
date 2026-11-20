<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pdo = getDBConnection();
$uid = (int) $_SESSION['user_id'];

$myItems = $pdo->prepare("SELECT * FROM items WHERE user_id = :uid AND is_active = 1 ORDER BY created_at DESC");
$myItems->execute(['uid' => $uid]);
$myItems = $myItems->fetchAll();

$myClaims = $pdo->prepare(
    "SELECT claims.*, items.item_name, items.report_type
     FROM claims JOIN items ON items.id = claims.item_id
     WHERE claims.claimant_id = :uid ORDER BY claims.created_at DESC"
);
$myClaims->execute(['uid' => $uid]);
$myClaims = $myClaims->fetchAll();

$pageTitle = 'My Reports | ' . APP_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-3">
      <div class="sidebar">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/user/my-reports.php"><i class="bi bi-clipboard-data me-2"></i>My Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>

    <div class="col-lg-9">
      <h4 class="fw-bold mb-3">My Reports</h4>
      <div class="table-responsive-card mb-4">
        <?php if (empty($myItems)): ?>
          <p class="text-muted small mb-0">You haven't submitted any reports yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Item</th><th>Type</th><th>Location</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach ($myItems as $it): ?>
                <tr>
                  <td><a href="<?= BASE_URL ?>/item-details.php?id=<?= $it['id'] ?>" class="text-decoration-none"><?= e($it['item_name']) ?></a></td>
                  <td><?= e(ucfirst($it['report_type'])) ?></td>
                  <td><?= e($it['location']) ?></td>
                  <td><?= date('M j, Y', strtotime($it['created_at'])) ?></td>
                  <td><?= status_badge($it['status']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <h4 class="fw-bold mb-3">My Claims</h4>
      <div class="table-responsive-card">
        <?php if (empty($myClaims)): ?>
          <p class="text-muted small mb-0">You haven't submitted any claims yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Item</th><th>Type</th><th>Submitted</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach ($myClaims as $c): ?>
                <tr>
                  <td><?= e($c['item_name']) ?></td>
                  <td><?= e(ucfirst($c['report_type'])) ?></td>
                  <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                  <td><?= status_badge($c['claim_status']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
