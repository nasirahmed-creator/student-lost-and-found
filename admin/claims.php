<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid request.');
        redirect('admin/claims.php');
    }

    $claimId = (int) ($_POST['claim_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';

    if (in_array($decision, ['approved', 'rejected'], true)) {
        $stmt = $pdo->prepare(
            "SELECT claims.*, items.item_name, items.report_type, items.id AS item_id
             FROM claims JOIN items ON items.id = claims.item_id WHERE claims.id = :id"
        );
        $stmt->execute(['id' => $claimId]);
        $claim = $stmt->fetch();

        if ($claim) {
            $pdo->prepare('UPDATE claims SET claim_status = :s WHERE id = :id')
                ->execute(['s' => $decision, 'id' => $claimId]);

            if ($decision === 'approved') {
                // Mark the underlying item as returned once a claim is approved
                $pdo->prepare("UPDATE items SET status = 'returned' WHERE id = :id")
                    ->execute(['id' => $claim['item_id']]);
            }

            create_notification(
                $pdo,
                (int) $claim['claimant_id'],
                'Claim ' . ucfirst($decision),
                'Your claim on "' . $claim['item_name'] . '" was ' . $decision . '.',
                BASE_URL . '/item-details.php?id=' . $claim['item_id']
            );

            set_flash('success', 'Claim ' . $decision . '.');
        }
    }
    redirect('admin/claims.php');
}

$claims = $pdo->query(
    "SELECT claims.*, items.item_name, items.report_type, items.id AS item_id, users.full_name AS claimant_name
     FROM claims
     JOIN items ON items.id = claims.item_id
     JOIN users ON users.id = claims.claimant_id
     ORDER BY FIELD(claims.claim_status, 'pending', 'approved', 'rejected'), claims.created_at DESC"
)->fetchAll();

$pageTitle = 'Manage Claims | ' . APP_NAME;
$activeNav = 'claims';
require_once __DIR__ . '/includes/admin-header.php';
?>

<h4 class="fw-bold mb-1">Manage Claims</h4>
<p class="text-muted">Review and approve or reject claims submitted by students.</p>

<div class="table-responsive-card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Item</th><th>Claimant</th><th>Proof Details</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (empty($claims)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No claims submitted yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($claims as $c): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/item-details.php?id=<?= $c['item_id'] ?>" class="text-decoration-none"><?= e($c['item_name']) ?></a></td>
          <td><?= e($c['claimant_name']) ?></td>
          <td class="small" style="max-width:260px;"><?= e($c['proof_details']) ?></td>
          <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
          <td><?= status_badge($c['claim_status']) ?></td>
          <td>
            <?php if ($c['claim_status'] === 'pending'): ?>
              <form method="POST" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="claim_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="decision" value="approved">
                <button class="btn btn-sm btn-success">Approve</button>
              </form>
              <form method="POST" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="claim_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="decision" value="rejected">
                <button class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
