<?php
/**
 * Generic helper functions used across the app.
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Escape output to prevent XSS (TC-I-06).
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Build a URL relative to BASE_URL.
 */
function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirect to a path (relative to BASE_URL) and stop execution.
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Store a one-time flash message ('success', 'error', 'info', 'warning').
 */
function flash(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $_SESSION['_flash'][$type][] = $message;
}

/**
 * Pull all flash messages and clear them. Returns ['type' => ['msg', ...]].
 */
function get_flashes(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return [];
    }
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

/**
 * Render flash messages as Bootstrap alerts. Call once in header.
 */
function render_flashes(): string
{
    $map = [
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        'info'    => 'alert-info',
    ];
    $html = '';
    foreach (get_flashes() as $type => $messages) {
        $cls = $map[$type] ?? 'alert-secondary';
        foreach ($messages as $msg) {
            $html .= '<div class="alert ' . $cls . ' alert-dismissible fade show" role="alert">'
                  . e($msg)
                  . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                  . '</div>';
        }
    }
    return $html;
}

/**
 * Issue / fetch a CSRF token for the current session.
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * Hidden CSRF input — drop into every <form>.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Validate a posted CSRF token. Aborts with 419 on failure.
 */
function csrf_verify(): void
{
    $posted = $_POST['_csrf'] ?? '';
    if (!is_string($posted) || !hash_equals($_SESSION['_csrf'] ?? '', $posted)) {
        http_response_code(419);
        die('CSRF token mismatch. Please reload the page and try again.');
    }
}

/**
 * Sanitize an uploaded filename — strips paths and unsafe chars (TC-I-02).
 */
function sanitize_filename(string $name): string
{
    $name = basename($name);
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? '';
    return substr($name, 0, 100);
}

/**
 * Generate a human-readable complaint reference number, e.g. "CMP-2026-0042".
 */
function generate_reference_no(int $complaintId): string
{
    return sprintf('CMP-%s-%04d', date('Y'), $complaintId);
}

/**
 * Append an entry to the audit_log table. Failures are swallowed so audit
 * problems never break the user flow.
 */
function log_audit(string $actorType, ?int $actorId, string $action,
                   ?string $targetTable = null, ?int $targetId = null, ?string $details = null): void
{
    try {
        if (!function_exists('db')) {
            require_once __DIR__ . '/../config/database.php';
        }
        db()->prepare(
            'INSERT INTO audit_log (actor_type, actor_id, action, target_table, target_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $actorType, $actorId, $action, $targetTable, $targetId, $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        // Intentional: audit must never block the request
    }
}
