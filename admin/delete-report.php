<?php
/**
 * admin/delete-report.php
 * POST-only handler: soft-deletes (deactivates) an item report.
 */
require_once __DIR__ . '/../config/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('danger', 'Invalid request.');
    redirect('admin/index.php');
}

$pdo = getDBConnection();
$itemId = (int) ($_POST['item_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM items WHERE id = :id');
$stmt->execute(['id' => $itemId]);
$item = $stmt->fetch();

if ($item) {
    // Soft delete: keeps history/audit trail instead of a hard DELETE
    $pdo->prepare('UPDATE items SET is_active = 0 WHERE id = :id')->execute(['id' => $itemId]);

    create_notification(
        $pdo,
        (int) $item['user_id'],
        'Report Removed',
        'Your ' . $item['report_type'] . ' report "' . $item['item_name'] . '" was removed by an administrator for review.',
        null
    );

    set_flash('success', 'Report removed.');
} else {
    set_flash('danger', 'Report not found.');
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$safePages = ['admin/verify-reports.php', 'admin/lost-reports.php', 'admin/found-reports.php', 'admin/index.php'];
$target = 'admin/lost-reports.php';
foreach ($safePages as $page) {
    if (str_ends_with($referer, $page)) { $target = $page; break; }
}
redirect($target);
