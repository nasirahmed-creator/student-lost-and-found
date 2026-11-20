<?php
/**
 * includes/functions.php
 * Reusable helper functions used across the app.
 */

/** Escape output to prevent XSS */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect helper */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

/** Generate / return a CSRF token for the current session */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Validate a submitted CSRF token */
function verify_csrf(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/** Print a hidden CSRF field for a <form> */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Flash message helpers (stored in session, shown once) */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/** Render a Bootstrap status badge for item/claim status */
function status_badge(string $status): string
{
    $map = [
        'pending'   => 'warning text-dark',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'returned'  => 'info text-dark',
        'lost'      => 'danger',
        'found'     => 'success',
        'active'    => 'success',
        'suspended' => 'secondary',
    ];
    $class = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . ' status-badge">' . e(ucfirst($status)) . '</span>';
}

/**
 * Validate and move an uploaded image.
 * Returns the stored filename on success, or null if no file was uploaded.
 * Throws RuntimeException on validation failure.
 */
function handle_image_upload(array $file, ?string $fieldName = 'photo'): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // optional field, nothing uploaded
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed. Please try again.');
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Image is too large. Maximum size is 5MB.');
    }

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
    }

    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $ext = $extMap[$mimeType];

    $newName = uniqid('item_', true) . '.' . $ext;
    $destination = UPLOAD_DIR . $newName;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save uploaded image.');
    }

    return $newName;
}

/** Create a notification row for a user */
function create_notification(PDO $pdo, int $userId, string $title, string $message, ?string $link = null): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, link) VALUES (:user_id, :title, :message, :link)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'title'   => $title,
        'message' => $message,
        'link'    => $link,
    ]);
}

/** Count unread notifications for the logged-in user */
function unread_notification_count(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0');
    $stmt->execute(['uid' => $userId]);
    return (int) $stmt->fetchColumn();
}

/** Simple pagination helper: returns [offset, limit, currentPage] */
function paginate(int $totalItems, int $perPage = 8): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    return [$offset, $perPage, $page, $totalPages];
}

/** List of allowed item categories (kept in one place for forms + filters) */
function item_categories(): array
{
    return ['Electronics', 'Bag', 'ID Card', 'Keys', 'Wallet/Purse', 'Clothing', 'Books', 'Jewelry', 'Documents', 'Other'];
}
