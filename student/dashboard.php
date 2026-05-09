<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('student');

$page_title = 'Student Dashboard';
$user = current_user();
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome banner -->
<div class="card border-0 shadow-sm mb-4 fade-up" style="background:var(--grad-hero);color:#fff;border-radius:var(--r-lg);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="hero-badge mb-2" style="margin-bottom:0.75rem;"><i class="bi bi-sun"></i> Good day</span>
            <h3 class="text-white mb-1">Welcome back, <?= e($user['name'] ?: 'Student') ?>!</h3>
            <p class="mb-0" style="color:rgba(255,255,255,0.9);">
                Here's what's happening with your complaints today.
            </p>
        </div>
        <a href="#" class="btn btn-light btn-lg disabled" aria-disabled="true">
            <i class="bi bi-plus-circle me-1"></i> Submit Complaint
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3 fade-up">
        <div class="stat-card">
            <div class="stat-icon bg-grad-primary"><i class="bi bi-collection"></i></div>
            <div class="stat-label">Total</div>
            <div class="stat-value">0</div>
            <div class="stat-trend"><i class="bi bi-dash-circle me-1"></i>No complaints yet</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-1">
        <div class="stat-card">
            <div class="stat-icon bg-grad-warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-label">Pending</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">Awaiting review</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-2">
        <div class="stat-card">
            <div class="stat-icon bg-grad-accent"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-label">In Progress</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">Being handled</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-3">
        <div class="stat-card">
            <div class="stat-icon bg-grad-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-label">Resolved</div>
            <div class="stat-value">0</div>
            <div class="stat-trend up"><i class="bi bi-check2 me-1"></i>Closed cases</div>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="#" class="card border-0 shadow-sm h-100 text-decoration-none">
            <div class="card-body text-center py-4">
                <div class="feature-icon mx-auto mb-3" style="width:56px;height:56px;font-size:1.4rem;">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <h6 class="mb-1">New Complaint</h6>
                <small class="text-muted">Submit a formal ticket</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="#" class="card border-0 shadow-sm h-100 text-decoration-none">
            <div class="card-body text-center py-4">
                <div class="feature-icon mx-auto mb-3" style="width:56px;height:56px;font-size:1.4rem;">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <h6 class="mb-1">Ask AI Assistant</h6>
                <small class="text-muted">Get instant answers</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="#" class="card border-0 shadow-sm h-100 text-decoration-none">
            <div class="card-body text-center py-4">
                <div class="feature-icon mx-auto mb-3" style="width:56px;height:56px;font-size:1.4rem;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h6 class="mb-1">My History</h6>
                <small class="text-muted">View past complaints</small>
            </div>
        </a>
    </div>
</div>

<!-- Empty state -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Recent Complaints</h5>
            <a href="#" class="small text-decoration-none">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
            <h5>No complaints yet</h5>
            <p class="mb-3">When you submit a complaint, it will appear here.</p>
            <span class="badge bg-secondary">Submission arrives in Sprint 1</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
