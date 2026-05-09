<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$page_title = 'Admin Dashboard';
$user = current_user();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0"><i class="bi bi-shield-check me-1"></i> Admin Control Panel</h3>
        <small class="text-muted">System-wide oversight, configuration, and reporting.</small>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Students</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Staff</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Departments</small>
                <h3 class="mb-0">5</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Complaints</small>
                <h3 class="mb-0">0</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><i class="bi bi-people me-1"></i> User Management</h6>
                <p class="text-muted small">Add / edit / deactivate students, staff, and admins.</p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><i class="bi bi-diagram-3 me-1"></i> Department Management</h6>
                <p class="text-muted small">Create complaint categories and assign staff.</p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><i class="bi bi-robot me-1"></i> AI Chatbot Knowledge Base</h6>
                <p class="text-muted small">Manage FAQs, review chat logs, train responses.</p>
                <span class="badge bg-secondary">Sprint 3 / 4</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><i class="bi bi-bar-chart me-1"></i> Analytical Reports</h6>
                <p class="text-muted small">Department-wise trends, resolution times, exports.</p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
