<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pdo = getDBConnection();

$counts = $pdo->query(
    "SELECT
       (SELECT COUNT(*) FROM users WHERE role='user') AS total_users,
       (SELECT COUNT(*) FROM items WHERE report_type='lost' AND is_active=1) AS total_lost,
       (SELECT COUNT(*) FROM items WHERE report_type='found' AND is_active=1) AS total_found,
       (SELECT COUNT(*) FROM items WHERE status='pending' AND is_active=1) AS pending_reports,
       (SELECT COUNT(*) FROM claims WHERE claim_status='pending') AS pending_claims,
       (SELECT COUNT(*) FROM items WHERE status='returned') AS returned_items
    "
)->fetch();

$recentActivity = $pdo->query(
    "SELECT items.item_name, items.report_type, items.status, items.created_at, users.full_name
     FROM items JOIN users ON users.id = items.user_id
     ORDER BY items.created_at DESC LIMIT 8"
)->fetchAll();

$pageTitle = 'Admin Dashboard | ' . APP_NAME;
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/admin-header.php';
?>

<h4 class="fw-bold">Dashboard</h4>
<p class="text-muted">System-wide overview</p>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card text-center">
      <div class="stat-number text-primary"><?= (int)$counts['total_users'] ?></div>
      <div class="text-muted small">Registered Users</div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card text-center">
      <div class="stat-number text-danger"><?= (int)$counts['total_lost'] ?></div>
      <div class="text-muted small">Lost Items</div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card text-center">
      <div class="stat-number text-success"><?= (int)$counts['total_found'] ?></div>
      <div class="text-muted small">Found Items</div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card text-center">
      <div class="stat-number text-warning"><?= (int)$counts['pending_reports'] ?></div>
      <div class="text-muted small">Pending Reports</div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card text-center">
      <div class="stat-number text-info"><?= (int)$counts['pending_claims'] ?></div>
      <div class="text-muted small">Pending Claims</div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="stat-card text-center">
      <div class="stat-number text-secondary"><?= (int)$counts['returned_items'] ?></div>
      <div class="text-muted small">Returned Items</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <a href="<?= BASE_URL ?>/admin/verify-reports.php" class="btn btn-primary w-100 py-3"><i class="bi bi-patch-check me-2"></i>Verify Reports</a>
  </div>
  <div class="col-md-4">
    <a href="<?= BASE_URL ?>/admin/claims.php" class="btn btn-success w-100 py-3"><i class="bi bi-clipboard-check me-2"></i>Review Claims</a>
  </div>
  <div class="col-md-4">
    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-primary w-100 py-3"><i class="bi bi-people me-2"></i>Manage Users</a>
  </div>
</div>

<div class="table-responsive-card">
  <h6 class="fw-bold mb-3">Recent Activity</h6>
  <?php if (empty($recentActivity)): ?>
    <p class="text-muted small mb-0">No activity yet.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Item</th><th>Type</th><th>Reported By</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentActivity as $a): ?>
          <tr>
            <td><?= e($a['item_name']) ?></td>
            <td><?= e(ucfirst($a['report_type'])) ?></td>
            <td><?= e($a['full_name']) ?></td>
            <td><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
            <td><?= status_badge($a['status']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
