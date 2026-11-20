<?php
/**
 * setup/create_admin.php
 *
 * Run this ONCE in your browser after importing database/student_lost_found.sql:
 *   http://localhost/student-lost-found/setup/create_admin.php
 *
 * It creates (or resets) the default admin account using PHP's own
 * password_hash(), so the stored hash is guaranteed to be valid -
 * unlike a hash pasted by hand into a .sql file.
 *
 * SECURITY: this script deletes itself after successfully running once.
 */

require_once __DIR__ . '/../config/database.php';

$adminEmail    = 'admin@miva.edu.ng';
$adminPassword = 'Admin@123';      // change after logging in!
$adminName     = 'System Administrator';
$adminStudentId = 'ADMIN-0001';

$pdo = getDBConnection();

$check = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$check->execute(['email' => $adminEmail]);
$existing = $check->fetch();

$hash = password_hash($adminPassword, PASSWORD_BCRYPT);

if ($existing) {
    $pdo->prepare('UPDATE users SET password_hash = :hash, role = "admin", status = "active" WHERE id = :id')
        ->execute(['hash' => $hash, 'id' => $existing['id']]);
    $message = 'Admin account already existed - password has been reset.';
} else {
    $pdo->prepare(
        'INSERT INTO users (full_name, student_id, email, phone, password_hash, role, status)
         VALUES (:name, :sid, :email, NULL, :hash, "admin", "active")'
    )->execute([
        'name'  => $adminName,
        'sid'   => $adminStudentId,
        'email' => $adminEmail,
        'hash'  => $hash,
    ]);
    $message = 'Admin account created successfully.';
}

// Self-delete for security once it has run
$selfDeleted = @unlink(__FILE__);

echo '<h2>' . htmlspecialchars($message) . '</h2>';
echo '<p>Email: <strong>' . htmlspecialchars($adminEmail) . '</strong><br>';
echo 'Password: <strong>' . htmlspecialchars($adminPassword) . '</strong></p>';
echo '<p style="color:red;">Please log in and change this password immediately.</p>';
echo $selfDeleted
    ? '<p>This setup script has deleted itself for security.</p>'
    : '<p style="color:red;">Could not auto-delete this file - please delete setup/create_admin.php manually now.</p>';
