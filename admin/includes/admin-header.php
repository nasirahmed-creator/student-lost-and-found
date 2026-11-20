<?php
/**
 * admin/includes/admin-header.php
 * Shared <head> + top bar + sidebar for all admin pages.
 * Requires require_admin() to already have been called by the including page.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admin | ' . APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top main-navbar">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/admin/index.php">
      <i class="bi bi-mortarboard-fill text-primary"></i> Student Lost &amp; Found <span class="badge bg-dark ms-1">Admin</span>
    </a>
    <div class="d-flex align-items-center gap-3">
      <a href="<?= BASE_URL ?>/index.php" class="small text-decoration-none"><i class="bi bi-box-arrow-up-right"></i> View Site</a>
      <div class="dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i> <?= e($_SESSION['full_name']) ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid px-4 py-4">
<?php foreach (get_flashes() as $flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endforeach; ?>

<div class="row g-4">
  <div class="col-lg-2">
    <div class="sidebar">
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeNav === 'users' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users.php"><i class="bi bi-people me-2"></i>Users</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeNav === 'lost' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/lost-reports.php"><i class="bi bi-person-x me-2"></i>Lost Reports</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeNav === 'found' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/found-reports.php"><i class="bi bi-person-check me-2"></i>Found Reports</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeNav === 'verify' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/verify-reports.php"><i class="bi bi-patch-check me-2"></i>Verify Reports</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeNav === 'claims' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/claims.php"><i class="bi bi-clipboard-check me-2"></i>Claims</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
  <div class="col-lg-10">
