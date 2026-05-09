<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
start_secure_session();

if (is_logged_in()) {
    redirect(current_role() . '/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    // TODO Sprint 1: validate input + check uniqueness + hash with password_hash() + insert into students.
    flash('info', 'Registration will be implemented in Sprint 1.');
    redirect('public/register.php');
}

// Departments dropdown — pulled live so it stays in sync with the DB.
$departments = [];
try {
    $departments = db()->query('SELECT department_id, name FROM departments WHERE is_active = 1 ORDER BY name')
                       ->fetchAll();
} catch (Throwable $e) {
    // DB might not be imported yet; show form without dropdown options.
    flash('warning', 'Departments could not be loaded. Has the schema been imported?');
}

$page_title = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="mb-3 text-center"><i class="bi bi-person-plus me-1"></i> Student Registration</h4>

                <form method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="roll_no" class="form-label">Roll No.</label>
                            <input type="text" id="roll_no" name="roll_no" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">University Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone (optional)</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="department_id" class="form-label">Department</label>
                            <select id="department_id" name="department_id" class="form-select" required>
                                <option value="">— Select your department —</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= e((string) $d['department_id']) ?>">
                                        <?= e($d['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control"
                                   minlength="8" required autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input type="password" id="password_confirm" name="password_confirm"
                                   class="form-control" minlength="8" required autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4">Create Account</button>
                </form>

                <p class="text-center small text-muted mt-3 mb-0">
                    Already registered?
                    <a href="<?= e(url('public/login.php')) ?>">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
