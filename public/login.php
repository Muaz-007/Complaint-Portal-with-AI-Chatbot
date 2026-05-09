<?php
require_once __DIR__ . '/../includes/auth.php';
start_secure_session();

// Already logged in? Send to the right dashboard.
if (is_logged_in()) {
    redirect(current_role() . '/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    // TODO Sprint 1: validate credentials against students/staff/admins tables.
    flash('info', 'Authentication will be implemented in Sprint 1.');
    redirect('public/login.php');
}

$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="mb-3 text-center"><i class="bi bi-box-arrow-in-right me-1"></i> Login</h4>

                <form method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="role" class="form-label">I am a</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="student">Student</option>
                            <option value="staff">Department Staff</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email or Roll No.</label>
                        <input type="text" id="email" name="identifier" class="form-control"
                               placeholder="you@university.edu" required autocomplete="username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                               required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <p class="text-center small text-muted mt-3 mb-0">
                    Don't have an account?
                    <a href="<?= e(url('public/register.php')) ?>">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
