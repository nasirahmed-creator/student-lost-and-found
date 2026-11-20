-- ============================================================
-- Student Lost & Found Management System - Miva Open University
-- Database: student_lost_found
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_lost_found
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE student_lost_found;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_student_id (student_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: items  (lost + found reports)
-- ------------------------------------------------------------
CREATE TABLE items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    report_type ENUM('lost','found') NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    category VARCHAR(80) NOT NULL,
    color VARCHAR(60) DEFAULT NULL,
    brand VARCHAR(80) DEFAULT NULL,
    description TEXT NOT NULL,
    location VARCHAR(150) NOT NULL,
    item_date DATE NOT NULL,
    reward VARCHAR(50) DEFAULT NULL,
    contact_phone VARCHAR(30) DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','approved','rejected','returned') NOT NULL DEFAULT 'pending',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_items_type (report_type),
    INDEX idx_items_category (category),
    INDEX idx_items_status (status),
    INDEX idx_items_location (location),
    INDEX idx_items_date (item_date)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: claims
-- ------------------------------------------------------------
CREATE TABLE claims (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    claimant_id INT UNSIGNED NOT NULL,
    proof_details TEXT NOT NULL,
    claim_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_claims_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    CONSTRAINT fk_claims_user FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_claims_status (claim_status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: notifications
-- ------------------------------------------------------------
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id, is_read)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Default admin account
-- ------------------------------------------------------------
-- Do NOT hardcode a password hash here (a hash typed into SQL by hand
-- cannot be verified without running PHP's own password_hash/password_verify).
-- Instead, after importing this file, run setup/create_admin.php ONCE in
-- your browser (e.g. http://localhost/student-lost-found/setup/create_admin.php).
-- It safely creates/re-hashes the admin account using PHP's password_hash(),
-- and it deletes itself after running for security.
