<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

require_login('admin');
$pdo = db();
$me  = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $head = trim((string) ($_POST['head_of_dept'] ?? ''));
            if ($name === '') {
                flash('error', 'Department name is required.');
            } else {
                $pdo->prepare('INSERT INTO departments (name, description, head_of_dept) VALUES (?, ?, ?)')
                    ->execute([$name, $desc ?: null, $head ?: null]);
                $newId = (int) $pdo->lastInsertId();
                log_audit('admin', (int) $me['id'], 'department.create', 'departments', $newId, $name);
                flash('success', 'Department "' . $name . '" created.');
            }
        } elseif ($action === 'update') {
            $id   = (int) ($_POST['department_id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $head = trim((string) ($_POST['head_of_dept'] ?? ''));
            if ($id < 1 || $name === '') {
                flash('error', 'Invalid department or name.');
            } else {
                $pdo->prepare('UPDATE departments SET name = ?, description = ?, head_of_dept = ? WHERE department_id = ?')
                    ->execute([$name, $desc ?: null, $head ?: null, $id]);
                log_audit('admin', (int) $me['id'], 'department.update', 'departments', $id, $name);
                flash('success', 'Department updated.');
            }
        } elseif ($action === 'toggle') {
            $id    = (int) ($_POST['department_id'] ?? 0);
            $state = (int) ($_POST['new_state'] ?? 0);
            $pdo->prepare('UPDATE departments SET is_active = ? WHERE department_id = ?')
                ->execute([$state, $id]);
            log_audit('admin', (int) $me['id'], 'department.' . ($state ? 'activate' : 'deactivate'), 'departments', $id);
            flash('success', $state ? 'Department activated.' : 'Department deactivated.');
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['department_id'] ?? 0);
            // Block deletion when active complaints reference this department (per TC-A-04 hint)
            $count = (int) $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE department_id = ? AND status NOT IN ("resolved","closed")')
                                ->execute([$id])->fetch();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE department_id = ?');
            $stmt->execute([$id]);
            $linked = (int) $stmt->fetchColumn();
            if ($linked > 0) {
                flash('error', 'Cannot delete: this department has ' . $linked . ' linked complaint(s). Deactivate it instead.');
            } else {
                $pdo->prepare('DELETE FROM departments WHERE department_id = ?')->execute([$id]);
                log_audit('admin', (int) $me['id'], 'department.delete', 'departments', $id);
                flash('success', 'Department deleted.');
            }
        }
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Duplicate') || str_contains($msg, '1062')) {
            flash('error', 'A department with that name already exists.');
        } else {
            flash('error', APP_DEBUG ? $msg : 'Action failed.');
        }
    }
    redirect('admin/departments/list.php');
}

$departments = [];
try {
    $departments = $pdo->query(
        "SELECT d.*,
                (SELECT COUNT(*) FROM staff      WHERE department_id = d.department_id) AS staff_count,
                (SELECT COUNT(*) FROM complaints WHERE department_id = d.department_id) AS complaint_count
         FROM departments d ORDER BY d.name"
    )->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load departments.');
}

$page_title = 'Departments';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-diagram-3 me-2"></i>Department Management</h3>
        <small class="text-muted">Create complaint categories and configure routing.</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDeptModal">
        <i class="bi bi-plus-circle me-1"></i> Add Department
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($departments)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-diagram-3"></i></div>
                <h5>No departments yet</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="letter-spacing:0.05em;">
                        <tr>
                            <th>Name</th><th>Description</th><th>Head</th>
                            <th>Staff</th><th>Complaints</th><th>Status</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $d): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($d['name']) ?></td>
                                <td><small class="text-muted"><?= e($d['description'] ?? '—') ?></small></td>
                                <td><small><?= e($d['head_of_dept'] ?? '—') ?></small></td>
                                <td><?= (int) $d['staff_count'] ?></td>
                                <td><?= (int) $d['complaint_count'] ?></td>
                                <td>
                                    <?php if ((int) $d['is_active'] === 1): ?>
                                        <span class="badge badge-status-resolved">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-status-on-hold">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#editDept<?= (int) $d['department_id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="department_id" value="<?= (int) $d['department_id'] ?>">
                                            <input type="hidden" name="new_state" value="<?= (int) $d['is_active'] === 1 ? 0 : 1 ?>">
                                            <button type="submit"
                                                    class="btn btn-sm <?= (int) $d['is_active'] === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                    data-confirm="Are you sure?">
                                                <i class="bi <?= (int) $d['is_active'] === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="department_id" value="<?= (int) $d['department_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-confirm="Permanently delete this department? This cannot be undone.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Edit modals -->
            <?php foreach ($departments as $d): ?>
                <div class="modal fade" id="editDept<?= (int) $d['department_id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="post" class="modal-content">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="department_id" value="<?= (int) $d['department_id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input name="name" class="form-control" value="<?= e($d['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2"><?= e($d['description'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Head of Department</label>
                                    <input name="head_of_dept" class="form-control" value="<?= e($d['head_of_dept'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create modal -->
<div class="modal fade" id="newDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i>New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" placeholder="e.g. Library" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"
                              placeholder="What kind of complaints belong here?"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Head of Department</label>
                    <input name="head_of_dept" class="form-control" placeholder="Optional">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Create Department</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
