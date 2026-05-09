<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('admin');
$pdo = db();

$type = $_GET['type'] ?? 'student';
if (!in_array($type, ['student', 'staff', 'admin'], true)) $type = 'student';

// Handle: deactivate/activate via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    csrf_verify();
    $userId = (int) ($_POST['user_id'] ?? 0);
    $newState = (int) ($_POST['new_state'] ?? 0);
    $tableMap = ['student' => 'students', 'staff' => 'staff', 'admin' => 'admins'];
    $idCol    = ['student' => 'student_id', 'staff' => 'staff_id', 'admin' => 'admin_id'];

    try {
        $pdo->prepare("UPDATE {$tableMap[$type]} SET is_active = ? WHERE {$idCol[$type]} = ?")
            ->execute([$newState, $userId]);
        flash('success', $newState ? 'Account activated.' : 'Account deactivated.');
    } catch (Throwable $e) {
        flash('error', APP_DEBUG ? $e->getMessage() : 'Action failed.');
    }
    redirect('admin/users/list.php?type=' . $type);
}

// Fetch users
$users = [];
try {
    if ($type === 'student') {
        $sql = 'SELECT s.student_id AS id, s.name, s.email, s.roll_no, s.is_active, s.created_at,
                       d.name AS department_name
                FROM students s
                LEFT JOIN departments d ON d.department_id = s.department_id
                ORDER BY s.created_at DESC';
    } elseif ($type === 'staff') {
        $sql = 'SELECT st.staff_id AS id, st.name, st.email, st.role, st.is_active, st.created_at,
                       d.name AS department_name
                FROM staff st
                LEFT JOIN departments d ON d.department_id = st.department_id
                ORDER BY st.created_at DESC';
    } else {
        $sql = 'SELECT admin_id AS id, name, email, is_active, created_at, last_login_at
                FROM admins
                ORDER BY created_at DESC';
    }
    $users = $pdo->query($sql)->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load users.');
}

$page_title = 'User Management';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-people me-2"></i>User Management</h3>
        <small class="text-muted">Add, edit, deactivate, or delete user accounts.</small>
    </div>
    <a href="<?= e(url('admin/users/edit.php?type=' . $type)) ?>" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Add <?= e(ucfirst($type)) ?>
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-pills mb-3 gap-2">
            <?php foreach (['student' => 'Students', 'staff' => 'Staff', 'admin' => 'Admins'] as $k => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $type === $k ? 'active' : '' ?>"
                       href="<?= e(url('admin/users/list.php?type=' . $k)) ?>">
                        <?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-people"></i></div>
                <h5>No <?= e($type) ?> accounts yet</h5>
                <a href="<?= e(url('admin/users/edit.php?type=' . $type)) ?>" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Add the first <?= e($type) ?>
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="letter-spacing:0.05em;">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <?php if ($type === 'student'): ?><th>Roll No.</th><th>Department</th><?php endif; ?>
                            <?php if ($type === 'staff'): ?><th>Department</th><th>Role</th><?php endif; ?>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($u['name']) ?></td>
                                <td><small class="text-muted"><?= e($u['email']) ?></small></td>
                                <?php if ($type === 'student'): ?>
                                    <td><code><?= e($u['roll_no']) ?></code></td>
                                    <td><small><?= e($u['department_name'] ?? '—') ?></small></td>
                                <?php endif; ?>
                                <?php if ($type === 'staff'): ?>
                                    <td><small><?= e($u['department_name'] ?? '—') ?></small></td>
                                    <td><small><?= e($u['role']) ?></small></td>
                                <?php endif; ?>
                                <td>
                                    <?php if ((int) $u['is_active'] === 1): ?>
                                        <span class="badge badge-status-resolved">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-status-on-hold">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= e(date('d M Y', strtotime($u['created_at']))) ?></small></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= e(url('admin/users/edit.php?type=' . $type . '&id=' . $u['id'])) ?>"
                                           class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="user_id" value="<?= e((string) $u['id']) ?>">
                                            <input type="hidden" name="new_state" value="<?= (int) $u['is_active'] === 1 ? 0 : 1 ?>">
                                            <button type="submit"
                                                    class="btn btn-sm <?= (int) $u['is_active'] === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                    data-confirm="Are you sure?">
                                                <i class="bi <?= (int) $u['is_active'] === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
