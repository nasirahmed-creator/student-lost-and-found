<?php
/**
 * includes/report-form.php
 * Shared logic + markup for reporting a lost OR found item.
 * The including page must set $reportType to 'lost' or 'found' before requiring this file.
 */
require_login();

$errors = [];
$old = ['item_name' => '', 'category' => '', 'color' => '', 'brand' => '', 'description' => '',
        'location' => '', 'item_date' => '', 'reward' => '', 'contact_phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    foreach ($old as $key => $_) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if ($old['item_name'] === '') $errors[] = 'Item name is required.';
    if ($old['category'] === '') $errors[] = 'Please select a category.';
    if ($old['description'] === '') $errors[] = 'Please provide a description.';
    if ($old['location'] === '') $errors[] = 'Location is required.';
    if ($old['item_date'] === '' || !strtotime($old['item_date'])) $errors[] = 'Please provide a valid date.';
    elseif (strtotime($old['item_date']) > time()) $errors[] = 'Date cannot be in the future.';

    $photoName = null;
    if (empty($errors)) {
        try {
            $photoName = handle_image_upload($_FILES['photo'] ?? []);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO items (user_id, report_type, item_name, category, color, brand, description,
                                 location, item_date, reward, contact_phone, photo, status)
             VALUES (:uid, :type, :name, :cat, :color, :brand, :desc, :loc, :date, :reward, :phone, :photo, "pending")'
        );
        $stmt->execute([
            'uid'   => $_SESSION['user_id'],
            'type'  => $reportType,
            'name'  => $old['item_name'],
            'cat'   => $old['category'],
            'color' => $old['color'] ?: null,
            'brand' => $old['brand'] ?: null,
            'desc'  => $old['description'],
            'loc'   => $old['location'],
            'date'  => date('Y-m-d', strtotime($old['item_date'])),
            'reward'=> $reportType === 'lost' ? ($old['reward'] ?: null) : null,
            'phone' => $old['contact_phone'] ?: null,
            'photo' => $photoName,
        ]);

        set_flash('success', 'Your ' . $reportType . ' item report was submitted and is pending review.');
        redirect('user/my-reports.php');
    }
}

$pageTitle = ucfirst($reportType) . ' Item Report | ' . APP_NAME;
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <a href="<?= BASE_URL ?>/search.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Back to Items</a>
      <div class="form-card mt-2">
        <h3 class="fw-bold mb-1">Report <?= ucfirst($reportType) ?> Item</h3>
        <p class="text-muted mb-4">Please provide details about the item you <?= $reportType === 'lost' ? 'lost' : 'found' ?>.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Item Name</label>
              <input type="text" name="item_name" class="form-control" required value="<?= e($old['item_name']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Category</label>
              <select name="category" class="form-select" required>
                <option value="">Select category</option>
                <?php foreach (item_categories() as $cat): ?>
                  <option value="<?= e($cat) ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Color</label>
              <input type="text" name="color" class="form-control" value="<?= e($old['color']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Brand</label>
              <input type="text" name="brand" class="form-control" value="<?= e($old['brand']) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control" required placeholder="Provide more details about the item..."><?= e($old['description']) ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Location <?= $reportType === 'lost' ? 'Lost' : 'Found' ?></label>
              <input type="text" name="location" class="form-control" required placeholder="Where did this happen?" value="<?= e($old['location']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Date <?= $reportType === 'lost' ? 'Lost' : 'Found' ?></label>
              <input type="date" name="item_date" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= e($old['item_date']) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Upload Image</label>
            <div class="upload-drop">
              <input type="file" name="photo" id="photoInput" class="js-photo-input d-none" data-preview="photoPreview" accept="image/png,image/jpeg,image/webp">
              <i class="bi bi-cloud-arrow-up fs-3 text-primary"></i>
              <p class="mb-0 small text-muted">Click to upload or drag and drop<br>PNG, JPG, WEBP up to 5MB</p>
            </div>
            <img id="photoPreview" class="img-preview" alt="Preview">
          </div>

          <div class="row">
            <?php if ($reportType === 'lost'): ?>
              <div class="col-md-6 mb-3">
                <label class="form-label">Reward (Optional)</label>
                <input type="text" name="reward" class="form-control" placeholder="e.g. ₦5,000" value="<?= e($old['reward']) ?>">
              </div>
            <?php endif; ?>
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Phone</label>
              <input type="text" name="contact_phone" class="form-control" placeholder="Enter contact phone number" value="<?= e($old['contact_phone']) ?>">
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Submit Report</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
