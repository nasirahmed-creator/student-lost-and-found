<?php
require_once __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect('user/dashboard.php');
}

$errors = [];
$old = ['full_name' => '', 'student_id' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $old['full_name']  = trim($_POST['full_name'] ?? '');
    $old['student_id'] = trim($_POST['student_id'] ?? '');
    $old['email']      = trim($_POST['email'] ?? '');
    $old['phone']      = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '' || mb_strlen($old['full_name']) < 3) {
        $errors[] = 'Please enter your full name.';
    }
    if ($old['student_id'] === '') {
        $errors[] = 'Please enter your Student ID.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $pdo = getDBConnection();

        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email OR student_id = :sid');
        $check->execute(['email' => $old['email'], 'sid' => $old['student_id']]);
        if ($check->fetch()) {
            $errors[] = 'An account with that email or Student ID already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, student_id, email, phone, password_hash, role, status)
             VALUES (:full_name, :student_id, :email, :phone, :hash, "user", "active")'
        );
        $stmt->execute([
            'full_name'  => $old['full_name'],
            'student_id' => $old['student_id'],
            'email'      => $old['email'],
            'phone'      => $old['phone'] ?: null,
            'hash'       => $hash,
        ]);

        set_flash('success', 'Registration successful! You can now log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="form-card">
        <h3 class="fw-bold mb-1">Create an Account</h3>
        <p class="text-muted mb-4">Join Student Lost &amp; Found to report and track items.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required value="<?= e($old['full_name']) ?>">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Student ID</label>
              <input type="text" name="student_id" class="form-control" required value="<?= e($old['student_id']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= e($old['phone']) ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required value="<?= e($old['email']) ?>">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" minlength="8" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" minlength="8" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
          Already have an account? <a href="<?= BASE_URL ?>/login.php">Login here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
