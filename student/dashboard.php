<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('student');

$page_title = 'Student Dashboard';
$user = current_user();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Welcome back, <?= e($user['name'] ?: 'Student') ?>!</h3>
        <small class="text-muted">Manage your complaints and track their status here.</small>
    </div>
    <a href="#" class="btn btn-primary disabled" aria-disabled="true">
        <i class="bi bi-plus-circle me-1"></i> Submit Complaint
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-warning">Pending</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-info">In Progress</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-success">Resolved</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox display-4 text-muted"></i>
        <h5 class="mt-3">No complaints yet</h5>
        <p class="text-muted small mb-0">
            Complaint submission and tracking arrive in <strong>Sprint 1</strong>.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
