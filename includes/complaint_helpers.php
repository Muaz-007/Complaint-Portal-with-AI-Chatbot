<?php
/**
 * Shared helpers for complaint rendering and validation.
 */

require_once __DIR__ . '/functions.php';

const COMPLAINT_CATEGORIES = ['Academics', 'Hostel', 'Finance', 'Examinations', 'IT Support'];
const COMPLAINT_PRIORITIES = ['low', 'medium', 'high', 'urgent'];
const COMPLAINT_STATUSES   = ['pending', 'in_progress', 'on_hold', 'resolved', 'closed', 'reopened'];

/**
 * Render a Bootstrap badge for a complaint status.
 */
function status_badge(string $status): string
{
    $labels = [
        'pending'     => 'Pending',
        'in_progress' => 'In Progress',
        'on_hold'     => 'On Hold',
        'resolved'    => 'Resolved',
        'closed'      => 'Closed',
        'reopened'    => 'Reopened',
    ];
    $label = $labels[$status] ?? ucfirst($status);
    $class = 'badge-status-' . str_replace('_', '-', $status);
    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}

/**
 * Render a Bootstrap badge for a complaint priority.
 */
function priority_badge(string $priority): string
{
    $class = 'badge-priority-' . $priority;
    return '<span class="badge ' . $class . '">' . e(ucfirst($priority)) . '</span>';
}

/**
 * Friendly relative time, e.g. "3 hours ago".
 */
function time_ago(string $datetime): string
{
    $ts   = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60)        return $diff . 's ago';
    if ($diff < 3600)      return floor($diff / 60) . 'm ago';
    if ($diff < 86400)     return floor($diff / 3600) . 'h ago';
    if ($diff < 604800)    return floor($diff / 86400) . 'd ago';
    return date('d M Y', $ts);
}

/**
 * Map a complaint category → department_id. Returns null if no match (route by admin later).
 */
function department_id_for_category(string $category): ?int
{
    $stmt = db()->prepare('SELECT department_id FROM departments WHERE name = ? LIMIT 1');
    $stmt->execute([$category]);
    $id = $stmt->fetchColumn();
    return $id ? (int) $id : null;
}

/**
 * Validate an uploaded file. Returns saved relative path on success, or throws on failure.
 */
function save_complaint_attachment(array $file, string $referenceNo): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed (error code ' . $file['error'] . ').');
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('Attachment exceeds the ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . ' MB limit.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_FILE_TYPES, true)) {
        throw new RuntimeException('File type ".' . $ext . '" is not allowed.');
    }

    $destDir = UPLOAD_PATH . '/complaints';
    if (!is_dir($destDir) && !@mkdir($destDir, 0775, true) && !is_dir($destDir)) {
        throw new RuntimeException('Could not create upload directory.');
    }

    $safe = sanitize_filename($referenceNo . '_' . pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $ext;
    $dest = $destDir . '/' . $safe;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save attachment.');
    }
    return 'uploads/complaints/' . $safe;
}

/**
 * Fetch complaint with student + department + staff info, only if the viewer is allowed.
 */
function load_complaint_for_viewer(int $complaintId, string $role, int $viewerId, ?int $viewerDeptId = null): ?array
{
    $sql = 'SELECT c.*, s.name AS student_name, s.email AS student_email,
                   d.name AS department_name,
                   st.name AS staff_name
            FROM complaints c
            JOIN students s    ON s.student_id = c.student_id
            LEFT JOIN departments d ON d.department_id = c.department_id
            LEFT JOIN staff st ON st.staff_id = c.assigned_staff_id
            WHERE c.complaint_id = ? LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([$complaintId]);
    $c = $stmt->fetch();
    if (!$c) return null;

    if ($role === 'student' && (int) $c['student_id'] !== $viewerId) return null;
    if ($role === 'staff'   && $viewerDeptId !== null && (int) $c['department_id'] !== $viewerDeptId) return null;

    return $c;
}

/**
 * Pull the message thread for a complaint.
 */
function load_complaint_messages(int $complaintId, bool $includeInternal = false): array
{
    $sql = 'SELECT * FROM complaint_messages
            WHERE complaint_id = ?'
         . ($includeInternal ? '' : ' AND is_internal_note = 0')
         . ' ORDER BY created_at ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute([$complaintId]);
    return $stmt->fetchAll();
}
