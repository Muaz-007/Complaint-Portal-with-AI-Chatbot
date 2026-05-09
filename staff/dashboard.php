<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('staff');

$page_title = 'Staff Dashboard';
$user = current_user();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Department Staff — <?= e($user['name'] ?: 'Staff') ?></h3>
        <small class="text-muted">Review and resolve complaints assigned to your department.</small>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Assigned</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-danger">Urgent</small>
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
                <small class="text-success">Resolved (30d)</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-clipboard-data display-4 text-muted"></i>
        <h5 class="mt-3">Complaint queue</h5>
        <p class="text-muted small mb-0">
            Department workflow, status updates, and student messaging arrive in <strong>Sprint 2</strong>.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
