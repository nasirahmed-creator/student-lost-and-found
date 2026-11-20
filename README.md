# Student Lost & Found Management System — Miva Open University

A complete PHP + MySQL (PDO) + Bootstrap 5 lost-and-found platform. No frameworks used.

## 1. Requirements
- XAMPP (Apache + MySQL + PHP 8.x)

## 2. Installation

1. Copy the whole `student-lost-found` folder into `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. Click **Import** → choose `database/student_lost_found.sql` → **Go**.
   This creates the `student_lost_found` database and all 4 tables (`users`, `items`, `claims`, `notifications`).
5. In your browser, visit:
   ```
   http://localhost/student-lost-found/setup/create_admin.php
   ```
   This creates the default admin account:
   - Email: `admin@miva.edu.ng`
   - Password: `Admin@123`

   The script deletes itself automatically after it runs, for security.
   **Change this password immediately** after your first admin login (Profile → Change Password area is on the user side; admins can also just re-run the SQL `UPDATE` if needed).
6. Visit the site:
   ```
   http://localhost/student-lost-found/index.php
   ```

## 3. Database Config

If your MySQL root user has a password, edit `config/database.php`:
```php
define('DB_PASS', 'your_password_here');
```

## 4. Folder Structure

```
student-lost-found/
├── config/                  -> database.php, config.php (blocked from direct web access)
├── admin/                   -> admin dashboard + management pages
│   └── includes/            -> shared admin header/footer/sidebar
├── user/                    -> user dashboard, profile, my-reports, notifications
├── assets/
│   ├── css/style.css
│   └── js/script.js
├── uploads/items/           -> uploaded item photos (script execution blocked here)
├── includes/                -> shared header/footer/functions/auth (blocked from direct web access)
├── database/
│   └── student_lost_found.sql
├── setup/create_admin.php   -> run once, then it deletes itself
├── index.php, login.php, register.php, logout.php
├── report-lost.php, report-found.php
├── search.php, item-details.php, claim.php
```

## 5. Roles

- **User**: register, log in, report lost/found items, browse & search, submit claims, view notifications, manage profile.
- **Admin**: log in with the admin account through the SAME `login.php` (role is checked automatically and routes admins to `/admin/index.php`). Manage users, verify/approve/reject reports, review claims, mark items returned, remove invalid reports.

## 6. Security Features Implemented

- Passwords hashed with `password_hash()` / verified with `password_verify()` (bcrypt).
- All SQL uses **PDO prepared statements** with bound parameters (`PDO::ATTR_EMULATE_PREPARES => false`) — no string-concatenated queries anywhere.
- CSRF tokens on every state-changing form (`csrf_field()` / `verify_csrf()`).
- Session hardening: `httponly`, `SameSite=Lax` cookies, session regeneration on login, inactivity timeout, full session destruction on logout.
- Role-based access control via `require_login()` / `require_admin()` guards on every protected page.
- Server-side file-upload validation: real MIME-type check (`finfo`), size limit (5MB), extension allow-list, randomized filenames, and `.htaccess` blocking script execution inside `uploads/`.
- All output escaped with `e()` (`htmlspecialchars`) to prevent XSS.
- Basic login throttling (5 failed attempts → 60s lockout).
- `config/` and `includes/` blocked from direct browser access via `.htaccess`.

## 7. Notes

- Reports go through an admin **Verify Reports** queue before appearing publicly (`status = pending → approved`).
- Approving a claim automatically marks the related item as `returned` and notifies the claimant.
- Deleting a report from the admin panel is a **soft delete** (`is_active = 0`) so history is preserved.
