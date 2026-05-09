<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
start_secure_session();

if (is_logged_in()) {
    redirect(current_role() . '/dashboard.php');
}

$old_role = '';
$old_identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $role       = $_POST['role'] ?? '';
    $identifier = trim((string) ($_POST['identifier'] ?? ''));
    $password   = (string) ($_POST['password'] ?? '');

    $old_role = $role;
    $old_identifier = $identifier;

    if (!in_array($role, ['student', 'staff', 'admin'], true)) {
        flash('error', 'Please select a valid role.');
    } elseif ($identifier === '' || $password === '') {
        flash('error', 'Email/Roll No and password are required.');
    } else {
        try {
            $user = find_user_for_login($role, $identifier);

            if ($user && (int) $user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
                login_user($role, $user);
                flash('success', 'Welcome back, ' . $user['name'] . '!');

                // Update last_login_at for admins
                if ($role === 'admin') {
                    db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE admin_id = ?')
                        ->execute([$user['admin_id']]);
                }

                redirect($role . '/dashboard.php');
            } elseif ($user && (int) $user['is_active'] !== 1) {
                flash('error', 'Your account has been deactivated. Please contact support.');
            } else {
                flash('error', 'Invalid email/roll number or password.');
            }
        } catch (Throwable $e) {
            flash('error', APP_DEBUG ? 'Login error: ' . $e->getMessage() : 'Login failed. Please try again.');
        }
    }

    // Fall through to render the form with the flash
}

/**
 * Look up a user record by role + identifier (email, or roll_no for students).
 */
function find_user_for_login(string $role, string $identifier): ?array
{
    $pdo = db();
    if ($role === 'student') {
        $sql = 'SELECT student_id, name, email, password_hash, is_active
                FROM students WHERE email = :id OR roll_no = :id LIMIT 1';
    } elseif ($role === 'staff') {
        $sql = 'SELECT staff_id, name, email, password_hash, is_active, department_id
                FROM staff WHERE email = :id LIMIT 1';
    } else {
        $sql = 'SELECT admin_id, name, email, password_hash, is_active
                FROM admins WHERE email = :id LIMIT 1';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $identifier]);
    $row = $stmt->fetch();
    return $row ?: null;
}

$page_title = 'Login';
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

            <h2 class="mb-3">Welcome back!</h2>
            <p class="mb-4">
                Sign in to track your complaints, chat with our AI assistant, and get the support
                you deserve — all from one place.
            </p>

            <div class="mt-5">
                <div class="point">
                    <div class="point-ico"><i class="bi bi-lightning-charge-fill"></i></div>
                    <div>
                        <p class="point-title">Instant AI answers</p>
                        <p class="point-desc">Skip the queue for routine questions.</p>
                    </div>
                </div>
                <div class="point">
                    <div class="point-ico"><i class="bi bi-clipboard-check-fill"></i></div>
                    <div>
                        <p class="point-title">Real-time status updates</p>
                        <p class="point-desc">Know exactly where your complaint stands.</p>
                    </div>
                </div>
                <div class="point">
                    <div class="point-ico"><i class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <p class="point-title">Encrypted &amp; private</p>
                        <p class="point-desc">Your data is protected at every step.</p>
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
            <div class="auth-logo"><i class="bi bi-box-arrow-in-right"></i></div>

            <h2 class="mb-1">Sign in to your account</h2>
            <p class="text-muted mb-4">
                New here?
                <a href="<?= e(url('public/register.php')) ?>" class="fw-semibold">Create an account</a>
            </p>

            <?= render_flashes() ?>

            <form method="post" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="role" class="form-label">I am a</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="student" <?= $old_role === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="staff"   <?= $old_role === 'staff'   ? 'selected' : '' ?>>Department Staff</option>
                        <option value="admin"   <?= $old_role === 'admin'   ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="identifier" class="form-label">Email or Roll No.</label>
                    <input type="text" id="identifier" name="identifier" class="form-control"
                           placeholder="you@university.edu" value="<?= e($old_identifier) ?>"
                           required autocomplete="username">
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label">Password</label>
                        <a href="#" class="small text-decoration-none">Forgot?</a>
                    </div>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="••••••••" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    Sign In <i class="bi bi-arrow-right ms-1"></i>
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
