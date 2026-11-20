<?php
/**
 * admin/lost-reports.php
 * All lost-item reports.
 */
require_once __DIR__ . '/../config/config.php';
require_admin();
$pdo = getDBConnection();

$items = $pdo->query(
    "SELECT items.*, users.full_name FROM items
     JOIN users ON users.id = items.user_id
     WHERE items.report_type = 'lost' AND items.is_active = 1
     ORDER BY items.created_at DESC"
)->fetchAll();

$pageTitle = 'Lost Reports | ' . APP_NAME;
$activeNav = 'lost';
require_once __DIR__ . '/includes/admin-header.php';
?>
<h4 class="fw-bold mb-3">Lost Item Reports</h4>
<?php require_once __DIR__ . '/includes/items-table.php'; ?>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
