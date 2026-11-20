<?php
require_once __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'user/dashboard.php');
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $oldEmail = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // ---- simple brute-force throttle ----
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
    $_SESSION['login_locked_until'] = $_SESSION['login_locked_until'] ?? 0;

    if (time() < $_SESSION['login_locked_until']) {
        $errors[] = 'Too many failed attempts. Please try again in a minute.';
    } elseif (empty($errors) && $oldEmail !== '' && $password !== '') {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $oldEmail]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            $_SESSION['login_attempts'] = 0;
            login_user($user);
            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($user['role'] === 'admin' ? 'admin/index.php' : 'user/dashboard.php');
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_locked_until'] = time() + 60;
                $_SESSION['login_attempts'] = 0;
            }
            $errors[] = ($user && $user['status'] !== 'active')
                ? 'Your account has been suspended. Contact support.'
                : 'Invalid email or password.';
        }
    } else {
        $errors[] = 'Please enter both email and password.';
    }
}

$pageTitle = 'Login | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-5">
      <div class="form-card">
        <h3 class="fw-bold mb-1">Welcome Back</h3>
        <p class="text-muted mb-4">Login to your account</p>

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
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required value="<?= e($oldEmail) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
          Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
