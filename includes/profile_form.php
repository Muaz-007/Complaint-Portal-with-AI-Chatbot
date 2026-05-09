<?php
/**
 * Shared profile editor used by student/staff/admin profile pages.
 * Required vars before include:
 *   $role        — 'student' | 'staff' | 'admin'
 *   $table       — 'students' | 'staff' | 'admins'
 *   $id_col      — primary key column name
 *   $extra_cols  — array of extra editable columns for this role (optional)
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

require_login($role);
$me  = current_user();
$pdo = db();

// Load current row
$stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$id_col} = ?");
$stmt->execute([$me['id']]);
$row = $stmt->fetch();
if (!$row) {
    flash('error', 'Could not load your profile.');
    redirect('public/logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name  = trim((string) ($_POST['name']  ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        $errors = [];
        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

        if (empty($errors)) {
            try {
                if ($role === 'student') {
                    $phone = trim((string) ($_POST['phone'] ?? ''));
                    $pdo->prepare('UPDATE students SET name=?, email=?, phone=? WHERE student_id=?')
                        ->execute([$name, $email, $phone ?: null, $me['id']]);
                } else {
                    $pdo->prepare("UPDATE {$table} SET name=?, email=? WHERE {$id_col}=?")
                        ->execute([$name, $email, $me['id']]);
                }
                $_SESSION['user']['name']  = $name;
                $_SESSION['user']['email'] = $email;
                log_audit($role, (int) $me['id'], 'profile.update', $table, (int) $me['id']);
                flash('success', 'Profile updated.');
            } catch (Throwable $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'Duplicate') || str_contains($msg, '1062')) {
                    flash('error', 'Another account already uses this email.');
                } else {
                    flash('error', APP_DEBUG ? $msg : 'Update failed.');
                }
            }
        } else {
            foreach ($errors as $err) flash('error', $err);
        }
        redirect($role . '/profile.php');
    } elseif ($action === 'password') {
        $current = (string) ($_POST['current_password']  ?? '');
        $new     = (string) ($_POST['new_password']      ?? '');
        $confirm = (string) ($_POST['confirm_password']  ?? '');

        if (!password_verify($current, $row['password_hash'])) {
            flash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 8) {
            flash('error', 'New password must be at least 8 characters.');
        } elseif ($new !== $confirm) {
            flash('error', 'Passwords do not match.');
        } else {
            try {
                $pdo->prepare("UPDATE {$table} SET password_hash = ? WHERE {$id_col} = ?")
                    ->execute([password_hash($new, PASSWORD_BCRYPT), $me['id']]);
                log_audit($role, (int) $me['id'], 'password.change', $table, (int) $me['id']);
                flash('success', 'Password changed successfully.');
            } catch (Throwable $e) {
                flash('error', APP_DEBUG ? $e->getMessage() : 'Password change failed.');
            }
        }
        redirect($role . '/profile.php');
    }
}

$page_title = 'My Profile';
require_once __DIR__ . '/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-person-circle me-2"></i>My Profile</h3>
        <small class="text-muted">Manage your personal information and password.</small>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body p-4">
                <div class="mx-auto mb-3" style="
                    width:96px;height:96px;border-radius:50%;
                    background:var(--grad-primary);
                    display:flex;align-items:center;justify-content:center;
                    color:#fff;font-size:2rem;font-family:'Poppins',sans-serif;font-weight:700;
                    box-shadow:var(--shadow-md);">
                    <?= e(strtoupper(substr($row['name'], 0, 1))) ?>
                </div>
                <h5 class="mb-1"><?= e($row['name']) ?></h5>
                <div class="text-muted small mb-2"><?= e($row['email']) ?></div>
                <span class="badge bg-primary"><?= e(ucfirst($role)) ?></span>
                <hr>
                <div class="text-start small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Member since</span>
                        <span class="fw-semibold"><?= e(date('d M Y', strtotime($row['created_at']))) ?></span>
                    </div>
                    <?php if ($role === 'student' && !empty($row['roll_no'])): ?>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Roll No.</span>
                            <span class="fw-semibold"><code><?= e($row['roll_no']) ?></code></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($row['is_active'])): ?>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Status</span>
                            <span class="badge badge-status-resolved">Active</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Profile info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-pencil me-1"></i>Profile Information</h5>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="profile">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input name="name" class="form-control" value="<?= e($row['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e($row['email']) ?>" required>
                        </div>

                        <?php if ($role === 'student'): ?>
                            <div class="col-md-6">
                                <label class="form-label">Roll No. <small class="text-muted">(read-only)</small></label>
                                <input class="form-control" value="<?= e($row['roll_no']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?= e($row['phone'] ?? '') ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-shield-lock me-1"></i>Change Password</h5>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="password">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="8" required autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="8" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
