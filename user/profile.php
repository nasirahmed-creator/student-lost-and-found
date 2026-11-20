<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pdo = getDBConnection();
$uid = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $uid]);
$user = $stmt->fetch();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');

        if (mb_strlen($fullName) < 3) {
            $errors[] = 'Please enter a valid full name.';
        }

        if (empty($errors)) {
            $update = $pdo->prepare('UPDATE users SET full_name = :name, phone = :phone WHERE id = :id');
            $update->execute(['name' => $fullName, 'phone' => $phone ?: null, 'id' => $uid]);
            $_SESSION['full_name'] = $fullName;
            $success = true;
            $user['full_name'] = $fullName;
            $user['phone'] = $phone;
        }
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $update->execute(['hash' => $hash, 'id' => $uid]);
            $success = true;
        }
    }
}

$pageTitle = 'My Profile | ' . APP_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-3">
      <div class="sidebar">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/my-reports.php"><i class="bi bi-clipboard-data me-2"></i>My Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/user/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>

    <div class="col-lg-9">
      <h4 class="fw-bold mb-4">My Profile</h4>

      <?php if ($success): ?><div class="alert alert-success">Changes saved successfully.</div><?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <div class="form-card mb-4">
        <div class="d-flex align-items-center gap-3 mb-4">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=3b5bdb&color=fff" class="avatar-circle" alt="Avatar">
          <div>
            <h5 class="fw-bold mb-0"><?= e($user['full_name']) ?></h5>
            <p class="text-muted small mb-0"><?= e($user['student_id']) ?> &middot; <?= e(ucfirst($user['role'])) ?></p>
          </div>
        </div>

        <form method="POST" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_profile">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control" required value="<?= e($user['full_name']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Student ID</label>
              <input type="text" class="form-control" value="<?= e($user['student_id']) ?>" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-primary px-4">Save Changes</button>
        </form>
      </div>

      <div class="form-card">
        <h6 class="fw-bold mb-3">Change Password</h6>
        <form method="POST" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="change_password">
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" minlength="8" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" minlength="8" required>
            </div>
          </div>
          <button type="submit" class="btn btn-outline-primary px-4">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
