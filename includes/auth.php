<?php
/**
 * includes/auth.php
 * Login state helpers + role-based access control (RBAC).
 */

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Force login; redirect to login page otherwise */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to continue.');
        redirect('login.php');
    }
}

/** Force admin role; redirect non-admins away */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'You do not have permission to access that page.');
        redirect('index.php');
    }
}

/** Log a user in: regenerate session id to prevent session fixation */
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];
}

/** Fully destroy the session on logout */
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
