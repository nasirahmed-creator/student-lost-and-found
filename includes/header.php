<?php
/**
 * includes/header.php
 * Shared <head> + top navbar for all public/user-facing pages.
 * Expects $pageTitle to optionally be set before including this file.
 */
$pdo = getDBConnection();
$unread = is_logged_in() ? unread_notification_count($pdo, (int)$_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? APP_NAME) ?></title>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top main-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">
      <i class="bi bi-mortarboard-fill text-primary"></i> Student Lost &amp; Found
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/search.php">Browse Items</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/report-lost.php">Report Lost</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/report-found.php">Report Found</a></li>
      </ul>
      <ul class="navbar-nav align-items-lg-center">
        <?php if (is_logged_in()): ?>
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?= BASE_URL ?>/user/notifications.php">
              <i class="bi bi-bell fs-5"></i>
              <?php if ($unread > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?= $unread ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?= e($_SESSION['full_name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/my-reports.php">My Reports</a></li>
              <?php if (is_admin()): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/index.php">Admin Dashboard</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
          <li class="nav-item">
            <a class="btn btn-primary btn-sm ms-lg-2 px-3" href="<?= BASE_URL ?>/register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main>
<?php foreach (get_flashes() as $flash): ?>
  <div class="container mt-3">
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
      <?= e($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endforeach; ?>
