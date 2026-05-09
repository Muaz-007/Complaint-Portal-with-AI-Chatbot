<?php
require_once __DIR__ . '/auth.php';
start_secure_session();

$page_title = $page_title ?? APP_NAME;
$role = current_role();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> · <?= e(APP_NAME) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App styles -->
    <link href="<?= e(url('public/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(url('public/index.php')) ?>">
            <i class="bi bi-mortarboard-fill me-1"></i> <?= e(APP_NAME) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(url('public/index.php')) ?>">Home</a>
                </li>

                <?php if (!is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url('public/login.php')) ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm ms-lg-2" href="<?= e(url('public/register.php')) ?>">Register</a>
                    </li>
                <?php else: ?>
                    <?php if ($role === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= e(url('student/dashboard.php')) ?>">Dashboard</a>
                        </li>
                    <?php elseif ($role === 'staff'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= e(url('staff/dashboard.php')) ?>">Dashboard</a>
                        </li>
                    <?php elseif ($role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= e(url('admin/dashboard.php')) ?>">Dashboard</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= e(current_user()['name'] ?: 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted">
                                Signed in as <strong><?= e(ucfirst($role)) ?></strong>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= e(url('public/logout.php')) ?>">
                                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?= render_flashes() ?>
