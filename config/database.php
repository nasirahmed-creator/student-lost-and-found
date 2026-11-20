<?php
/**
 * config/database.php
 * Creates a single shared PDO connection for the whole application.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'student_lost_found');
define('DB_USER', 'root');
define('DB_PASS', '');      // set your MySQL/XAMPP root password here if you have one
define('DB_CHARSET', 'utf8mb4');

function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements -> SQL injection protection
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak DB credentials or raw exception details to the browser
            error_log('Database connection error: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }

    return $pdo;
}
