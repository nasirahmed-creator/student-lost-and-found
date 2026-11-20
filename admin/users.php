<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid form submission.');
        redirect('admin/users.php');
    }

    $userId = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($userId === (int) $_SESSION['user_id']) {
        set_flash('warning', 'You cannot modify your own account here.');
        redirect('admin/users.php');
    }

    if ($action === 'toggle_status') {
        $stmt = $pdo->prepare('SELECT status FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $current = $stmt->fetchColumn();
        if ($current) {
            $new = $current === 'active' ? 'suspended' : 'active';
            $pdo->prepare('UPDATE users SET status = :s WHERE id = :id')->execute(['s' => $new, 'id' => $userId]);
            set_flash('success', 'User status updated.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM users WHERE id = :id AND role != "admin"')->execute(['id' => $userId]);
        set_flash('success', 'User removed.');
    }

    redirect('admin/users.php');
}

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM users WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (full_name LIKE :q OR email LIKE :q2 OR student_id LIKE :q3)';
    $params = ['q' => "%$search%", 'q2' => "%$search%", 'q3' => "%$search%"];
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users | ' . APP_NAME;
$activeNav = 'users';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Manage Users</h4>
  <form method="GET" class="d-flex gap-2">
    <input type="text" name="q" class="form-control" placeholder="Search users..." value="<?= e($search) ?>">
    <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
  </form>
</div>

<div class="table-responsive-card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Name</th><th>Student ID</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['full_name']) ?></td>
          <td><?= e($u['student_id']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><?= e(ucfirst($u['role'])) ?></td>
          <td><?= status_badge($u['status']) ?></td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['role'] !== 'admin'): ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Toggle status for this user?');">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="toggle_status">
                <button class="btn btn-sm btn-outline-secondary"><?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?></button>
              </form>
              <form method="POST" class="d-inline" onsubmit="return confirm('Permanently remove this user?');">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
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
