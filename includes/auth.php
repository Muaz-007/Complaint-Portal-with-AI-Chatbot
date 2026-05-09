<?php
/**
 * Session + role-based authentication helpers.
 * Implements TC-I-01 (RBAC) and TC-I-03 (30-min idle timeout).
 */

require_once __DIR__ . '/functions.php';

/**
 * Start a session with secure cookie flags. Call this on every page that uses sessions.
 */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    enforce_idle_timeout();
}

/**
 * Log the user out automatically after SESSION_LIFETIME seconds of inactivity.
 */
function enforce_idle_timeout(): void
{
    $now = time();
    if (isset($_SESSION['_last_activity']) && $now - $_SESSION['_last_activity'] > SESSION_LIFETIME) {
        logout_user();
        flash('info', 'Your session has expired. Please log in again.');
        redirect('public/login.php');
    }
    $_SESSION['_last_activity'] = $now;
}

/**
 * Mark a user as logged in.
 *
 * @param string $role  'student' | 'staff' | 'admin'
 * @param array  $user  Row from the corresponding table
 */
function login_user(string $role, array $user): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        start_secure_session();
    }
    session_regenerate_id(true);

    $idKey = match ($role) {
        'student' => 'student_id',
        'staff'   => 'staff_id',
        'admin'   => 'admin_id',
        default   => null,
    };

    if ($idKey === null || !isset($user[$idKey])) {
        throw new InvalidArgumentException("Invalid role or user payload for login: {$role}");
    }

    $_SESSION['user'] = [
        'role'  => $role,
        'id'    => (int) $user[$idKey],
        'name'  => $user['name']  ?? '',
        'email' => $user['email'] ?? '',
        // Staff carry their department for routing/filtering
        'department_id' => isset($user['department_id']) ? (int) $user['department_id'] : null,
    ];
}

/**
 * Department ID of the currently logged-in staff member, or null.
 */
function current_user_dept(): ?int
{
    return $_SESSION['user']['department_id'] ?? null;
}

/**
 * Destroy the current session.
 */
function logout_user(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_role(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function is_role(string $role): bool
{
    return current_role() === $role;
}

/**
 * Block access if not logged in (or not the required role). Redirects to login.
 *
 * @param string|null $role If provided, also enforce that the user has this role.
 */
function require_login(?string $role = null): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        start_secure_session();
    }

    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('public/login.php');
    }

    if ($role !== null && !is_role($role)) {
        http_response_code(403);
        flash('error', 'You do not have permission to access that page.');
        redirect('public/login.php');
    }
}
