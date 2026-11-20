<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pdo = getDBConnection();
$uid = (int) $_SESSION['user_id'];

// Mark all as read when the page is visited
$pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid')->execute(['uid' => $uid]);

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50');
$stmt->execute(['uid' => $uid]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications | ' . APP_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-3">
      <div class="sidebar">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/my-reports.php"><i class="bi bi-clipboard-data me-2"></i>My Reports</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/user/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>

    <div class="col-lg-9">
      <h4 class="fw-bold mb-3">Notifications</h4>
      <div class="table-responsive-card">
        <?php if (empty($notifications)): ?>
          <p class="text-muted small mb-0">You have no notifications yet.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($notifications as $n): ?>
              <li class="list-group-item px-0">
                <div class="d-flex justify-content-between">
                  <strong><?= e($n['title']) ?></strong>
                  <small class="text-muted"><?= date('M j, Y g:ia', strtotime($n['created_at'])) ?></small>
                </div>
                <p class="mb-1 text-muted small"><?= e($n['message']) ?></p>
                <?php if ($n['link']): ?><a href="<?= e($n['link']) ?>" class="small">View details</a><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
