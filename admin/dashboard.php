<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$page_title = 'Admin Dashboard';
$user = current_user();
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome banner -->
<div class="card border-0 shadow-sm mb-4 fade-up" style="background:var(--grad-hero);color:#fff;border-radius:var(--r-lg);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="hero-badge mb-2" style="margin-bottom:0.75rem;"><i class="bi bi-shield-check"></i> Administrator</span>
            <h3 class="text-white mb-1">Control Panel</h3>
            <p class="mb-0" style="color:rgba(255,255,255,0.9);">
                System-wide oversight, configuration, and analytics.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="#" class="btn btn-outline-primary disabled" aria-disabled="true" style="border-color:rgba(255,255,255,0.5);color:#fff;">
                <i class="bi bi-download me-1"></i> Export
            </a>
            <a href="#" class="btn btn-light disabled" aria-disabled="true">
                <i class="bi bi-bar-chart me-1"></i> Generate Report
            </a>
        </div>
    </div>
</div>

<!-- KPI Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3 fade-up">
        <div class="stat-card">
            <div class="stat-icon bg-grad-primary"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="stat-label">Students</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">Registered</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-1">
        <div class="stat-card">
            <div class="stat-icon bg-grad-accent"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Staff</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">Across departments</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-2">
        <div class="stat-card">
            <div class="stat-icon bg-grad-warning"><i class="bi bi-diagram-3-fill"></i></div>
            <div class="stat-label">Departments</div>
            <div class="stat-value">5</div>
            <div class="stat-trend">Active</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-3">
        <div class="stat-card">
            <div class="stat-icon bg-grad-success"><i class="bi bi-clipboard-data-fill"></i></div>
            <div class="stat-label">Total Complaints</div>
            <div class="stat-value">0</div>
            <div class="stat-trend up"><i class="bi bi-graph-up-arrow me-1"></i>All time</div>
        </div>
    </div>
</div>

<!-- Module cards -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <h5 class="mb-3"><i class="bi bi-grid me-2"></i>Management Modules</h5>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">User Management</h6>
                        <small class="text-muted">Students, staff, admins</small>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    Add, edit, deactivate, or delete user accounts. Bulk import via CSV supported.
                </p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Department Management</h6>
                        <small class="text-muted">Categories &amp; routing</small>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    Create complaint categories, assign staff, configure department-specific
                    routing rules.
                </p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">AI Knowledge Base</h6>
                        <small class="text-muted">Train the chatbot</small>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    Manage FAQs, review chat logs, add new query/response pairs to improve
                    accuracy.
                </p>
                <span class="badge bg-secondary">Sprint 3 / 4</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Analytical Reports</h6>
                        <small class="text-muted">Trends &amp; insights</small>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    Department-wise reports, resolution times, satisfaction trends. Export to
                    PDF/Excel.
                </p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Audit Trail</h6>
                        <small class="text-muted">All system activity</small>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    Complete log of every action — logins, edits, status changes, deletions —
                    for compliance.
                </p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">System Configuration</h6>
                        <small class="text-muted">App-wide settings</small>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    Manage complaint categories, priority rules, email templates, and
                    notification preferences.
                </p>
                <span class="badge bg-secondary">Sprint 4</span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
