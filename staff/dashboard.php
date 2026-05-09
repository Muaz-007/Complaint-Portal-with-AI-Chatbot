<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('staff');

$page_title = 'Staff Dashboard';
$user = current_user();
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome banner -->
<div class="card border-0 shadow-sm mb-4 fade-up" style="background:var(--grad-hero);color:#fff;border-radius:var(--r-lg);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="hero-badge mb-2" style="margin-bottom:0.75rem;"><i class="bi bi-people-fill"></i> Department Staff</span>
            <h3 class="text-white mb-1"><?= e($user['name'] ?: 'Staff') ?></h3>
            <p class="mb-0" style="color:rgba(255,255,255,0.9);">
                Review and resolve complaints assigned to your department.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="#" class="btn btn-outline-primary disabled" aria-disabled="true" style="border-color:rgba(255,255,255,0.5);color:#fff;">
                <i class="bi bi-funnel me-1"></i> Filter
            </a>
            <a href="#" class="btn btn-light disabled" aria-disabled="true">
                <i class="bi bi-file-earmark-text me-1"></i> Generate Report
            </a>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3 fade-up">
        <div class="stat-card">
            <div class="stat-icon bg-grad-primary"><i class="bi bi-inbox-fill"></i></div>
            <div class="stat-label">Assigned</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">All open tickets</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-1">
        <div class="stat-card">
            <div class="stat-icon bg-grad-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-label">Urgent</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">High priority</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-2">
        <div class="stat-card">
            <div class="stat-icon bg-grad-accent"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-label">In Progress</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">Currently working</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-3">
        <div class="stat-card">
            <div class="stat-icon bg-grad-success"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-label">Resolved (30d)</div>
            <div class="stat-value">0</div>
            <div class="stat-trend up"><i class="bi bi-graph-up-arrow me-1"></i>Past month</div>
        </div>
    </div>
</div>

<!-- Tabs / queue -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-pills mb-3 gap-2">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-inbox me-1"></i> All</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-flag-fill me-1"></i> Urgent</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-hourglass me-1"></i> Pending</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-arrow-repeat me-1"></i> In Progress</a></li>
        </ul>

        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-clipboard-data"></i></div>
            <h5>No complaints assigned yet</h5>
            <p class="mb-3">When students submit complaints to your department, they'll show up here.</p>
            <span class="badge bg-secondary">Workflow arrives in Sprint 2</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
