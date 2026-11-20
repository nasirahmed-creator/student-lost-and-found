<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pdo = getDBConnection();
$uid = (int) $_SESSION['user_id'];

$stats = $pdo->prepare(
    "SELECT
        SUM(report_type = 'lost') AS lost_count,
        SUM(report_type = 'found') AS found_count,
        SUM(status = 'returned') AS returned_count,
        SUM(status = 'pending') AS pending_count
     FROM items WHERE user_id = :uid AND is_active = 1"
);
$stats->execute(['uid' => $uid]);
$s = $stats->fetch();

$recent = $pdo->prepare("SELECT * FROM items WHERE user_id = :uid AND is_active = 1 ORDER BY created_at DESC LIMIT 5");
$recent->execute(['uid' => $uid]);
$recentItems = $recent->fetchAll();

$pageTitle = 'Dashboard | ' . APP_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-3">
      <div class="sidebar">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/search.php"><i class="bi bi-search me-2"></i>Browse Items</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/report-lost.php"><i class="bi bi-person-x me-2"></i>Report Lost</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/report-found.php"><i class="bi bi-person-check me-2"></i>Report Found</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/my-reports.php"><i class="bi bi-clipboard-data me-2"></i>My Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>

    <div class="col-lg-9">
      <h4 class="fw-bold">Dashboard</h4>
      <p class="text-muted">Welcome back, <?= e($_SESSION['full_name']) ?> 👋</p>

      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="stat-card text-center">
            <div class="stat-number text-primary"><?= (int)($s['lost_count'] ?? 0) ?></div>
            <div class="text-muted small">Lost Items Reported</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card text-center">
            <div class="stat-number text-success"><?= (int)($s['found_count'] ?? 0) ?></div>
            <div class="text-muted small">Found Items Reported</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card text-center">
            <div class="stat-number text-info"><?= (int)($s['returned_count'] ?? 0) ?></div>
            <div class="text-muted small">Returned Items</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card text-center">
            <div class="stat-number text-warning"><?= (int)($s['pending_count'] ?? 0) ?></div>
            <div class="text-muted small">Pending Reports</div>
          </div>
        </div>
      </div>

      <div class="table-responsive-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0">My Recent Reports</h6>
          <a href="<?= BASE_URL ?>/user/my-reports.php" class="small">View All Reports</a>
        </div>
        <?php if (empty($recentItems)): ?>
          <p class="text-muted small mb-0">You haven't reported any items yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Item</th><th>Type</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach ($recentItems as $it): ?>
                <tr>
                  <td><a href="<?= BASE_URL ?>/item-details.php?id=<?= $it['id'] ?>" class="text-decoration-none"><?= e($it['item_name']) ?></a></td>
                  <td><?= e(ucfirst($it['report_type'])) ?></td>
                  <td><?= date('M j, Y', strtotime($it['created_at'])) ?></td>
                  <td><?= status_badge($it['status']) ?></td>
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
