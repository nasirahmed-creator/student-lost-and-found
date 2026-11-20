<?php
/**
 * admin/includes/items-table.php
 * Renders a table of $items (array of item rows joined with reporter name).
 * Include after fetching $items in the calling admin page.
 */
?>
<div class="table-responsive-card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Item</th><th>Category</th><th>Reported By</th><th>Location</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (empty($items)): ?>
        <tr><td colspan="7" class="text-muted text-center py-4">No reports found.</td></tr>
      <?php endif; ?>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/item-details.php?id=<?= $it['id'] ?>" class="text-decoration-none"><?= e($it['item_name']) ?></a></td>
          <td><?= e($it['category']) ?></td>
          <td><?= e($it['full_name']) ?></td>
          <td><?= e($it['location']) ?></td>
          <td><?= date('M j, Y', strtotime($it['created_at'])) ?></td>
          <td><?= status_badge($it['status']) ?></td>
          <td class="d-flex gap-1 flex-wrap">
            <?php if ($it['status'] === 'pending'): ?>
              <form method="POST" action="<?= BASE_URL ?>/admin/update-status.php">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                <input type="hidden" name="new_status" value="approved">
                <button class="btn btn-sm btn-success">Approve</button>
              </form>
              <form method="POST" action="<?= BASE_URL ?>/admin/update-status.php">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                <input type="hidden" name="new_status" value="rejected">
                <button class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            <?php elseif ($it['status'] === 'approved'): ?>
              <form method="POST" action="<?= BASE_URL ?>/admin/update-status.php">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                <input type="hidden" name="new_status" value="returned">
                <button class="btn btn-sm btn-info text-white">Mark Returned</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/delete-report.php" onsubmit="return confirm('Remove this report permanently?');">
              <?= csrf_field() ?>
              <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
              <button class="btn btn-sm btn-outline-secondary">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
