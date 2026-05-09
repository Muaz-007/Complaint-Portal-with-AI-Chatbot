<?php
require_once __DIR__ . '/auth.php';
start_secure_session();

$page_title = $page_title ?? APP_NAME;
$role = current_role();
$auth_layout = $auth_layout ?? false; // set true on login/register for split-screen
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <title><?= e($page_title) ?> · <?= e(APP_NAME) ?></title>

    <!-- Google Fonts: Inter (UI) + Poppins (display) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App styles -->
    <link href="<?= e(url('public/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<?php if (!$auth_layout): ?>
<nav class="navbar navbar-expand-lg sticky-top" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand" href="<?= e(url('public/index.php')) ?>">
            <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
            <span><?= e(APP_NAME) ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-3"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(url('public/index.php')) ?>">Home</a>
                </li>

                <?php if (!is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url('public/login.php')) ?>">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-light" href="<?= e(url('public/register.php')) ?>">
                            <i class="bi bi-person-plus me-1"></i> Get Started
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url($role . '/dashboard.php')) ?>">
                            <i class="bi bi-grid me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <span class="brand-mark me-2" style="width:32px;height:32px;font-size:0.85rem;">
                                <?= e(strtoupper(substr(current_user()['name'] ?? 'U', 0, 1))) ?>
                            </span>
                            <span class="d-none d-md-inline"><?= e(current_user()['name'] ?: 'Account') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2">
                                <div class="small text-muted">Signed in as</div>
                                <div class="fw-semibold text-truncate"><?= e(current_user()['email'] ?? '') ?></div>
                                <span class="badge bg-primary mt-1"><?= e(ucfirst($role)) ?></span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= e(url($role . '/dashboard.php')) ?>">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= e(url($role . '/profile.php')) ?>">
                                    <i class="bi bi-person me-2"></i>My Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= e(url('public/logout.php')) ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php if (!$auth_layout): ?>
<main class="container py-4">
    <?php $_flash = render_flashes(); if ($_flash): ?>
        <div class="mb-3"><?= $_flash ?></div>
    <?php endif; ?>
<?php endif; ?>
