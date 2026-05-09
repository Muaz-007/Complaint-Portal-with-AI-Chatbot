<?php
$page_title = 'Home';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="text-center py-5">
    <h1 class="display-5 fw-bold mb-3">Smart University Complaint Portal</h1>
    <p class="lead text-muted col-lg-8 mx-auto mb-4">
        A faster, fairer way to raise and resolve student concerns. Submit complaints online,
        track progress in real time, and get instant answers from our AI assistant — 24/7.
    </p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
        <?php if (!is_logged_in()): ?>
            <a href="<?= e(url('public/register.php')) ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-person-plus me-1"></i> Register
            </a>
            <a href="<?= e(url('public/login.php')) ?>" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </a>
        <?php else: ?>
            <a href="<?= e(url(current_role() . '/dashboard.php')) ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-speedometer2 me-1"></i> Go to Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="row g-4 py-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-chat-dots-fill text-primary fs-1"></i>
                <h5 class="mt-3">AI Chatbot</h5>
                <p class="text-muted small mb-0">
                    Ask routine questions and get instant answers — exam dates, library hours,
                    fee deadlines, and more.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-clipboard-check-fill text-primary fs-1"></i>
                <h5 class="mt-3">Track Complaints</h5>
                <p class="text-muted small mb-0">
                    File a complaint in seconds, attach evidence, and watch its status update
                    from Pending to Resolved.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-shield-lock-fill text-primary fs-1"></i>
                <h5 class="mt-3">Secure &amp; Transparent</h5>
                <p class="text-muted small mb-0">
                    Encrypted communication, role-based access, and a full audit trail for
                    every action taken on your ticket.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
