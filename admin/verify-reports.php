<?php
/**
 * admin/verify-reports.php
 * Only PENDING reports awaiting admin verification (lost + found).
 */
require_once __DIR__ . '/../config/config.php';
require_admin();
$pdo = getDBConnection();

$items = $pdo->query(
    "SELECT items.*, users.full_name FROM items
     JOIN users ON users.id = items.user_id
     WHERE items.status = 'pending' AND items.is_active = 1
     ORDER BY items.created_at ASC"
)->fetchAll();

$pageTitle = 'Verify Reports | ' . APP_NAME;
$activeNav = 'verify';
require_once __DIR__ . '/includes/admin-header.php';
?>
<h4 class="fw-bold mb-1">Verify Reports</h4>
<p class="text-muted">Review and approve or reject newly submitted reports.</p>
<?php require_once __DIR__ . '/includes/items-table.php'; ?>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
