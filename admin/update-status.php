<?php
/**
 * admin/update-status.php
 * POST-only handler: updates an item's status and notifies the reporter.
 */
require_once __DIR__ . '/../config/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('danger', 'Invalid request.');
    redirect('admin/index.php');
}

$pdo = getDBConnection();
$itemId = (int) ($_POST['item_id'] ?? 0);
$newStatus = $_POST['new_status'] ?? '';

$allowed = ['approved', 'rejected', 'returned'];
if (!in_array($newStatus, $allowed, true)) {
    set_flash('danger', 'Invalid status.');
    redirect('admin/verify-reports.php');
}

$stmt = $pdo->prepare('SELECT * FROM items WHERE id = :id');
$stmt->execute(['id' => $itemId]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('danger', 'Report not found.');
    redirect('admin/verify-reports.php');
}

$pdo->prepare('UPDATE items SET status = :status WHERE id = :id')
    ->execute(['status' => $newStatus, 'id' => $itemId]);

create_notification(
    $pdo,
    (int) $item['user_id'],
    'Report ' . ucfirst($newStatus),
    'Your ' . $item['report_type'] . ' report "' . $item['item_name'] . '" has been ' . $newStatus . '.',
    BASE_URL . '/item-details.php?id=' . $itemId
);

set_flash('success', 'Report marked as ' . $newStatus . '.');

// Only redirect back to a known admin page on this same site (avoid open-redirect via a spoofed Referer header)
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$safePages = ['admin/verify-reports.php', 'admin/lost-reports.php', 'admin/found-reports.php', 'admin/index.php'];
$target = 'admin/verify-reports.php';
foreach ($safePages as $page) {
    if (str_ends_with($referer, $page)) { $target = $page; break; }
}
redirect($target);
