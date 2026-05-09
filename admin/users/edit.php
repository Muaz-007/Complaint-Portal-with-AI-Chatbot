<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

require_login('admin');
$pdo = db();

$type = $_GET['type'] ?? 'student';
if (!in_array($type, ['student', 'staff', 'admin'], true)) $type = 'student';

$id = (int) ($_GET['id'] ?? 0);
$is_edit = $id > 0;

$tableMap = ['student' => 'students', 'staff' => 'staff', 'admin' => 'admins'];
$idCol    = ['student' => 'student_id', 'staff' => 'staff_id', 'admin' => 'admin_id'];

$old = ['name' => '', 'email' => '', 'roll_no' => '', 'phone' => '', 'department_id' => '', 'role' => 'staff'];

// Load existing record on edit
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM {$tableMap[$type]} WHERE {$idCol[$type]} = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        flash('error', 'User not found.');
        redirect('admin/users/list.php?type=' . $type);
    }
    $old = array_merge($old, $row);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old['name']  = trim((string) ($_POST['name'] ?? ''));
    $old['email'] = trim((string) ($_POST['email'] ?? ''));
    $password     = (string) ($_POST['password'] ?? '');
    $errors = [];

    if ($old['name'] === '') $errors[] = 'Name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (!$is_edit && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($is_edit && $password !== '' && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    if ($type === 'student') {
        $old['roll_no']       = trim((string) ($_POST['roll_no'] ?? ''));
        $old['phone']         = trim((string) ($_POST['phone'] ?? ''));
        $old['department_id'] = $_POST['department_id'] ?? '';
        if ($old['roll_no'] === '') $errors[] = 'Roll number is required.';
        if ($old['department_id'] === '') $errors[] = 'Department is required.';
    } elseif ($type === 'staff') {
        $old['department_id'] = $_POST['department_id'] ?? '';
        $old['role']          = trim((string) ($_POST['role'] ?? 'staff'));
        if ($old['department_id'] === '') $errors[] = 'Department is required.';
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                if ($type === 'student') {
                    $sql = 'UPDATE students SET name=?, email=?, roll_no=?, phone=?, department_id=?'
                         . ($password ? ', password_hash=?' : '')
                         . ' WHERE student_id=?';
                    $params = [$old['name'], $old['email'], $old['roll_no'], $old['phone'] ?: null,
                               (int) $old['department_id']];
                    if ($password) $params[] = password_hash($password, PASSWORD_BCRYPT);
                    $params[] = $id;
                } elseif ($type === 'staff') {
                    $sql = 'UPDATE staff SET name=?, email=?, role=?, department_id=?'
                         . ($password ? ', password_hash=?' : '')
                         . ' WHERE staff_id=?';
                    $params = [$old['name'], $old['email'], $old['role'], (int) $old['department_id']];
                    if ($password) $params[] = password_hash($password, PASSWORD_BCRYPT);
                    $params[] = $id;
                } else {
                    $sql = 'UPDATE admins SET name=?, email=?'
                         . ($password ? ', password_hash=?' : '')
                         . ' WHERE admin_id=?';
                    $params = [$old['name'], $old['email']];
                    if ($password) $params[] = password_hash($password, PASSWORD_BCRYPT);
                    $params[] = $id;
                }
                $pdo->prepare($sql)->execute($params);
                $admin = current_user();
                log_audit('admin', (int) $admin['id'], 'user.update.' . $type, $tableMap[$type], $id, $old['email']);
                flash('success', ucfirst($type) . ' account updated.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                if ($type === 'student') {
                    $pdo->prepare(
                        'INSERT INTO students (name, email, roll_no, phone, department_id, password_hash)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    )->execute([$old['name'], $old['email'], $old['roll_no'], $old['phone'] ?: null,
                                (int) $old['department_id'], $hash]);
                } elseif ($type === 'staff') {
                    $pdo->prepare(
                        'INSERT INTO staff (name, email, role, department_id, password_hash)
                         VALUES (?, ?, ?, ?, ?)'
                    )->execute([$old['name'], $old['email'], $old['role'],
                                (int) $old['department_id'], $hash]);
                } else {
                    $pdo->prepare(
                        'INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)'
                    )->execute([$old['name'], $old['email'], $hash]);
                }
                $admin = current_user();
                log_audit('admin', (int) $admin['id'], 'user.create.' . $type, $tableMap[$type], (int) $pdo->lastInsertId(), $old['email']);
                flash('success', ucfirst($type) . ' account created.');
            }
            redirect('admin/users/list.php?type=' . $type);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Duplicate') || str_contains($msg, '1062')) {
                $errors[] = 'An account with this email or roll number already exists.';
            } else {
                $errors[] = APP_DEBUG ? 'DB error: ' . $msg : 'Could not save the account.';
            }
        }
    }

    foreach ($errors as $err) flash('error', $err);
}

// Departments dropdown
$departments = [];
if ($type !== 'admin') {
    try {
        $departments = $pdo->query('SELECT department_id, name FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll();
    } catch (Throwable $e) { /* ignore */ }
}

$page_title = ($is_edit ? 'Edit ' : 'Add ') . ucfirst($type);
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= e(url('admin/users/list.php?type=' . $type)) ?>" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Back to <?= e($type) ?> list
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-1">
                    <i class="bi bi-<?= $is_edit ? 'pencil' : 'person-plus' ?> me-2"></i>
                    <?= $is_edit ? 'Edit' : 'Add' ?> <?= e(ucfirst($type)) ?>
                </h4>
                <p class="text-muted small mb-4">
                    <?= $is_edit ? 'Update the account details below.' : 'Create a new ' . e($type) . ' account.' ?>
                </p>

                <form method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="<?= e($old['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= e($old['email']) ?>" required>
                        </div>

                        <?php if ($type === 'student'): ?>
                            <div class="col-md-6">
                                <label for="roll_no" class="form-label">Roll No.</label>
                                <input type="text" id="roll_no" name="roll_no" class="form-control"
                                       value="<?= e($old['roll_no']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <small class="text-muted">(optional)</small></label>
                                <input type="tel" id="phone" name="phone" class="form-control"
                                       value="<?= e($old['phone']) ?>">
                            </div>
                            <div class="col-12">
                                <label for="department_id" class="form-label">Department</label>
                                <select id="department_id" name="department_id" class="form-select" required>
                                    <option value="">— Select —</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?= e((string) $d['department_id']) ?>"
                                                <?= $old['department_id'] == $d['department_id'] ? 'selected' : '' ?>>
                                            <?= e($d['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php elseif ($type === 'staff'): ?>
                            <div class="col-md-6">
                                <label for="department_id" class="form-label">Department</label>
                                <select id="department_id" name="department_id" class="form-select" required>
                                    <option value="">— Select —</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?= e((string) $d['department_id']) ?>"
                                                <?= $old['department_id'] == $d['department_id'] ? 'selected' : '' ?>>
                                            <?= e($d['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role / Title</label>
                                <input type="text" id="role" name="role" class="form-control"
                                       value="<?= e($old['role'] ?? 'staff') ?>" placeholder="e.g. Hostel Manager" required>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Password <?= $is_edit ? '<small class="text-muted">(leave blank to keep current)</small>' : '' ?>
                            </label>
                            <input type="password" id="password" name="password" class="form-control"
                                   minlength="8" <?= $is_edit ? '' : 'required' ?> autocomplete="new-password">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1"></i> <?= $is_edit ? 'Save Changes' : 'Create Account' ?>
                        </button>
                        <a href="<?= e(url('admin/users/list.php?type=' . $type)) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
