<?php
require_once __DIR__ . '/config/config.php';
$pdo = getDBConnection();

$q        = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$location = trim($_GET['location'] ?? '');
$status   = trim($_GET['status'] ?? ''); // maps to report_type: lost/found, or blank = both

$where  = ["items.status = 'approved'", "items.is_active = 1"];
$params = [];

if ($q !== '') {
    $where[] = '(items.item_name LIKE :q OR items.description LIKE :q2 OR items.brand LIKE :q3)';
    $params['q']  = "%$q%";
    $params['q2'] = "%$q%";
    $params['q3'] = "%$q%";
}
if ($category !== '') {
    $where[] = 'items.category = :category';
    $params['category'] = $category;
}
if ($location !== '') {
    $where[] = 'items.location LIKE :location';
    $params['location'] = "%$location%";
}
if ($status === 'lost' || $status === 'found') {
    $where[] = 'items.report_type = :rtype';
    $params['rtype'] = $status;
}

$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

[$offset, $perPage, $page, $totalPages] = paginate($total, 8);

$sql = "SELECT items.*, users.full_name AS reporter_name
        FROM items JOIN users ON users.id = items.user_id
        WHERE $whereSql
        ORDER BY items.created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// distinct locations for the filter dropdown
$locations = $pdo->query("SELECT DISTINCT location FROM items WHERE status='approved' ORDER BY location")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Browse Lost & Found Items | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';

function qs(array $overrides = [])
{
    $params = array_merge($_GET, $overrides);
    return '?' . http_build_query($params);
}
?>

<div class="container py-4">
  <h3 class="fw-bold">Browse Lost &amp; Found Items</h3>
  <p class="text-muted">Search and find lost or found items</p>

  <form method="GET" class="row g-2 align-items-center mb-4">
    <div class="col-lg-4 col-md-6">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Search items..." value="<?= e($q) ?>">
      </div>
    </div>
    <div class="col-lg-2 col-md-6">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <?php foreach (item_categories() as $cat): ?>
          <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-2 col-md-6">
      <select name="location" class="form-select">
        <option value="">All Locations</option>
        <?php foreach ($locations as $loc): ?>
          <option value="<?= e($loc) ?>" <?= $location === $loc ? 'selected' : '' ?>><?= e($loc) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-2 col-md-6">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="lost" <?= $status === 'lost' ? 'selected' : '' ?>>Lost</option>
        <option value="found" <?= $status === 'found' ? 'selected' : '' ?>>Found</option>
      </select>
    </div>
    <div class="col-lg-2 col-md-6">
      <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
    </div>
  </form>

  <div class="row g-4">
    <?php if (empty($items)): ?>
      <div class="col-12">
        <div class="alert alert-secondary">No items match your search. Try different filters.</div>
      </div>
    <?php endif; ?>

    <?php foreach ($items as $item): ?>
      <div class="col-sm-6 col-lg-3">
        <a href="<?= BASE_URL ?>/item-details.php?id=<?= (int)$item['id'] ?>" class="text-decoration-none text-dark">
          <div class="item-card position-relative">
            <span class="badge type-badge bg-<?= $item['report_type'] === 'lost' ? 'danger' : 'success' ?>">
              <?= e(ucfirst($item['report_type'])) ?>
            </span>
            <img class="item-thumb" src="<?= $item['photo'] ? UPLOAD_URL . e($item['photo']) : 'https://placehold.co/400x300?text=No+Image' ?>" alt="<?= e($item['item_name']) ?>">
            <div class="card-body">
              <h6 class="fw-bold mb-1 text-truncate"><?= e($item['item_name']) ?></h6>
              <p class="text-muted small mb-1"><?= e($item['category']) ?></p>
              <p class="text-muted small mb-0"><i class="bi bi-geo-alt"></i> <?= e($item['location']) ?></p>
              <p class="text-muted small mb-0"><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($item['item_date'])) ?></p>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= qs(['page' => $page - 1]) ?>">&lsaquo;</a>
        </li>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= qs(['page' => $p]) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= qs(['page' => $page + 1]) ?>">&rsaquo;</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
