<?php
require_once __DIR__ . '/config/config.php';
require_login();
$pdo = getDBConnection();

$itemId = (int) ($_GET['item_id'] ?? $_POST['item_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id AND status = 'approved' AND is_active = 1");
$stmt->execute(['id' => $itemId]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('danger', 'That item is not available for claiming.');
    redirect('search.php');
}

if ((int)$item['user_id'] === (int)$_SESSION['user_id']) {
    set_flash('warning', 'You cannot claim your own report.');
    redirect('item-details.php?id=' . $itemId);
}

// Prevent duplicate pending claims by the same user on the same item
$dupCheck = $pdo->prepare("SELECT id FROM claims WHERE item_id = :iid AND claimant_id = :uid AND claim_status = 'pending'");
$dupCheck->execute(['iid' => $itemId, 'uid' => $_SESSION['user_id']]);
$alreadyClaimed = (bool) $dupCheck->fetch();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyClaimed) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    }
    $proof = trim($_POST['proof_details'] ?? '');
    if (mb_strlen($proof) < 15) {
        $errors[] = 'Please provide more detail to help verify your claim (at least 15 characters).';
    }

    if (empty($errors)) {
        $insert = $pdo->prepare(
            'INSERT INTO claims (item_id, claimant_id, proof_details, claim_status) VALUES (:iid, :uid, :proof, "pending")'
        );
        $insert->execute(['iid' => $itemId, 'uid' => $_SESSION['user_id'], 'proof' => $proof]);

        // notify the original reporter
        create_notification(
            $pdo,
            (int) $item['user_id'],
            'New Claim Submitted',
            'Someone submitted a claim on your ' . $item['report_type'] . ' report: "' . $item['item_name'] . '".',
            BASE_URL . '/item-details.php?id=' . $itemId
        );

        set_flash('success', 'Your claim was submitted and is pending admin review.');
        redirect('user/my-reports.php');
    }
}

$pageTitle = 'Submit a Claim | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="form-card">
        <h3 class="fw-bold mb-1">Submit a Claim</h3>
        <p class="text-muted mb-4">Claiming: <strong><?= e($item['item_name']) ?></strong></p>

        <?php if ($alreadyClaimed): ?>
          <div class="alert alert-info">You already have a pending claim on this item. Please wait for admin review.</div>
        <?php else: ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>

          <form method="POST" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= $itemId ?>">
            <div class="mb-3">
              <label class="form-label">Proof / Identifying Details</label>
              <textarea name="proof_details" rows="5" class="form-control" minlength="15" required
                placeholder="Describe unique details only the owner would know (e.g. contents, marks, serial number)..."></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100 py-2">Submit Claim</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
