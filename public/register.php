<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
start_secure_session();

if (is_logged_in()) {
    redirect(current_role() . '/dashboard.php');
}

$old = ['name' => '', 'roll_no' => '', 'email' => '', 'phone' => '', 'department_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old['name']          = trim((string) ($_POST['name'] ?? ''));
    $old['roll_no']       = trim((string) ($_POST['roll_no'] ?? ''));
    $old['email']         = trim((string) ($_POST['email'] ?? ''));
    $old['phone']         = trim((string) ($_POST['phone'] ?? ''));
    $old['department_id'] = $_POST['department_id'] ?? '';
    $password             = (string) ($_POST['password'] ?? '');
    $password_confirm     = (string) ($_POST['password_confirm'] ?? '');

    $errors = [];
    if ($old['name'] === '')                           $errors[] = 'Name is required.';
    if ($old['roll_no'] === '')                        $errors[] = 'Roll number is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($old['department_id'] === '')                  $errors[] = 'Please select your department.';
    if (strlen($password) < 8)                         $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $password_confirm)               $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        try {
            $pdo = db();

            // Uniqueness checks (per TC-S-01 reg flow)
            $stmt = $pdo->prepare('SELECT 1 FROM students WHERE email = ? OR roll_no = ? LIMIT 1');
            $stmt->execute([$old['email'], $old['roll_no']]);
            if ($stmt->fetchColumn()) {
                $errors[] = 'An account with this email or roll number already exists.';
            }
        } catch (Throwable $e) {
            $errors[] = APP_DEBUG ? 'DB error: ' . $e->getMessage() : 'Registration failed. Please try again.';
        }
    }

    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                'INSERT INTO students (roll_no, name, email, password_hash, phone, department_id)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $old['roll_no'],
                $old['name'],
                $old['email'],
                $hash,
                $old['phone'] ?: null,
                (int) $old['department_id'],
            ]);

            $student_id = (int) $pdo->lastInsertId();
            $newUser = [
                'student_id' => $student_id,
                'name'       => $old['name'],
                'email'      => $old['email'],
            ];

            login_user('student', $newUser);
            flash('success', 'Welcome aboard, ' . $old['name'] . '! Your account has been created.');
            redirect('student/dashboard.php');
        } catch (Throwable $e) {
            $errors[] = APP_DEBUG ? 'DB error: ' . $e->getMessage() : 'Registration failed. Please try again.';
        }
    }

    foreach ($errors as $err) flash('error', $err);
}

// Departments dropdown — pulled live so it stays in sync with the DB.
$departments = [];
try {
    $departments = db()->query('SELECT department_id, name FROM departments WHERE is_active = 1 ORDER BY name')
                       ->fetchAll();
} catch (Throwable $e) {
    flash('warning', 'Departments could not be loaded. Has the schema been imported?');
}

$page_title = 'Register';
$auth_layout = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-shell">
    <!-- Left panel — branding -->
    <aside class="auth-aside">
        <div>
            <a href="<?= e(url('public/index.php')) ?>" class="d-flex align-items-center gap-2 text-white text-decoration-none mb-5">
                <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
                <span style="font-family:'Poppins',sans-serif;font-weight:700;"><?= e(APP_NAME) ?></span>
            </a>

            <h2 class="mb-3">Join your campus portal.</h2>
            <p class="mb-4">
                Create your free student account and unlock instant AI support, transparent
                complaint tracking, and direct communication with your university departments.
            </p>

            <div class="mt-5">
                <div class="point">
                    <div class="point-ico"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <p class="point-title">Free for all students</p>
                        <p class="point-desc">No fees, no ads, just a better experience.</p>
                    </div>
                </div>
                <div class="point">
                    <div class="point-ico"><i class="bi bi-stopwatch-fill"></i></div>
                    <div>
                        <p class="point-title">Setup in under a minute</p>
                        <p class="point-desc">Just a few details and you're in.</p>
                    </div>
                </div>
                <div class="point">
                    <div class="point-ico"><i class="bi bi-lock-fill"></i></div>
                    <div>
                        <p class="point-title">Your data, your privacy</p>
                        <p class="point-desc">Encrypted, never shared, never sold.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="small" style="color:rgba(255,255,255,0.65);">
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>
        </div>
    </aside>

    <!-- Right panel — form -->
    <main class="auth-main">
        <div class="auth-card fade-up">
            <div class="auth-logo"><i class="bi bi-person-plus-fill"></i></div>

            <h2 class="mb-1">Create your account</h2>
            <p class="text-muted mb-4">
                Already a member?
                <a href="<?= e(url('public/login.php')) ?>" class="fw-semibold">Sign in</a>
            </p>

            <?= render_flashes() ?>

            <form method="post" novalidate>
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               placeholder="Ahmed Khan" value="<?= e($old['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="roll_no" class="form-label">Roll No.</label>
                        <input type="text" id="roll_no" name="roll_no" class="form-control"
                               placeholder="BCS-21-001" value="<?= e($old['roll_no']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">University Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="you@university.edu" value="<?= e($old['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone <small class="text-muted">(optional)</small></label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               placeholder="03XX-XXXXXXX" value="<?= e($old['phone']) ?>">
                    </div>
                    <div class="col-12">
                        <label for="department_id" class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-select" required>
                            <option value="">— Select your department —</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= e((string) $d['department_id']) ?>"
                                    <?= $old['department_id'] == $d['department_id'] ? 'selected' : '' ?>>
                                    <?= e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="At least 8 characters" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm"
                               class="form-control" placeholder="Re-enter password" minlength="8" required autocomplete="new-password">
                    </div>
                </div>

                <div class="form-check mt-3 mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label small" for="terms">
                        I agree to the <a href="#">terms of service</a> and <a href="#">privacy policy</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    Create Account <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="<?= e(url('public/index.php')) ?>" class="small text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Back to home
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
