<?php
$page_title = 'Home';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 fade-up">
                <span class="hero-badge">
                    <i class="bi bi-stars"></i> AI-Powered Student Support
                </span>
                <h1 class="mb-3">
                    A smarter way to raise<br>
                    and resolve <span style="color:#fde047;">student complaints</span>
                </h1>
                <p class="lead mb-4 col-lg-10">
                    Submit complaints in seconds, get instant answers from our AI assistant 24/7,
                    and track every step from <em>Pending</em> to <em>Resolved</em> — all in one
                    transparent portal.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (!is_logged_in()): ?>
                        <a href="<?= e(url('public/register.php')) ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus me-1"></i> Get Started — It's Free
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

                <div class="d-flex flex-wrap gap-4 mt-5 small" style="color:rgba(255,255,255,0.85);">
                    <div><i class="bi bi-shield-check me-1 text-success"></i> Encrypted &amp; secure</div>
                    <div><i class="bi bi-clock-history me-1 text-info"></i> Real-time tracking</div>
                    <div><i class="bi bi-robot me-1" style="color:#fde047;"></i> 24/7 AI assistant</div>
                </div>
            </div>

            <div class="col-lg-5 d-none d-lg-block fade-up delay-2">
                <div class="hero-cards">
                    <!-- AI chat card -->
                    <div class="hero-card hero-card-ai">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="brand-mark" style="width:36px;height:36px;font-size:0.95rem;">
                                <i class="bi bi-robot text-white"></i>
                            </div>
                            <div>
                                <div class="fw-semibold" style="color:var(--text-900);font-size:0.92rem;">AI Assistant</div>
                                <small style="color:var(--success);font-size:0.72rem;display:flex;align-items:center;gap:0.25rem;">
                                    <span class="online-dot"></span> Online · replies in seconds
                                </small>
                            </div>
                        </div>

                        <div class="chat-bubble chat-bubble-user mb-2">
                            What are the library timings?
                        </div>
                        <div class="chat-bubble chat-bubble-bot">
                            The library is open <strong>8 AM – 10 PM</strong> on weekdays and
                            <strong>10 AM – 6 PM</strong> on weekends.
                        </div>
                    </div>

                    <!-- Complaint status card -->
                    <div class="hero-card hero-card-ticket">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <code style="color:var(--primary-600);font-weight:600;font-size:0.78rem;">CMP-2026-0042</code>
                            <span class="badge badge-status-resolved">Resolved</span>
                        </div>
                        <div class="fw-semibold mb-3" style="color:var(--text-900);">Hostel maintenance</div>
                        <div class="ticket-timeline">
                            <div class="ticket-step done">
                                <div class="dot"><i class="bi bi-check-lg"></i></div>
                                <small>Submitted</small>
                            </div>
                            <div class="ticket-step done">
                                <div class="dot"><i class="bi bi-check-lg"></i></div>
                                <small>In Progress</small>
                            </div>
                            <div class="ticket-step done">
                                <div class="dot"><i class="bi bi-check-lg"></i></div>
                                <small>Resolved</small>
                            </div>
                        </div>
                        <small class="d-block mt-3" style="color:var(--text-500);font-size:0.75rem;">
                            <i class="bi bi-stopwatch-fill me-1" style="color:var(--success);"></i>
                            Resolved in 2 days
                        </small>
                    </div>

                    <!-- Floating notification pill -->
                    <div class="hero-pill">
                        <i class="bi bi-bell-fill" style="color:var(--warning);font-size:0.95rem;"></i>
                        <small style="color:var(--text-700);font-weight:600;">3 new updates</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="container mb-5">
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="display-5 text-gradient fw-bold">5</div>
                <small class="text-muted">Departments</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="display-5 text-gradient fw-bold">24/7</div>
                <small class="text-muted">AI Assistance</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="display-5 text-gradient fw-bold">3</div>
                <small class="text-muted">User Roles</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="display-5 text-gradient fw-bold">100%</div>
                <small class="text-muted">Transparent</small>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="container py-5">
    <div class="text-center mb-5">
        <span class="section-eyebrow">Why choose us</span>
        <h2 class="display-6">Everything you need to resolve issues faster</h2>
        <p class="text-muted col-lg-7 mx-auto">
            From the first question to the final resolution, every step is designed to be fast,
            transparent, and student-friendly.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-chat-dots-fill"></i></div>
                <h5>AI Chatbot</h5>
                <p>Get instant, intelligent answers to routine queries — exam dates, library hours,
                   fee deadlines, and more — without waiting for office hours.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-clipboard-check-fill"></i></div>
                <h5>Real-Time Tracking</h5>
                <p>Watch your complaint move through Pending → In Progress → Resolved with a clear
                   timeline of every action and message.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-shield-lock-fill"></i></div>
                <h5>Secure &amp; Confidential</h5>
                <p>Encrypted communication, role-based access control, and a complete audit trail
                   protect every interaction.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-diagram-3-fill"></i></div>
                <h5>Smart Routing</h5>
                <p>Complaints are auto-categorized and routed to the right department — Academics,
                   Hostel, Finance, Examinations, or IT Support.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-chat-square-text-fill"></i></div>
                <h5>Two-Way Messaging</h5>
                <p>Talk directly with the staff handling your case — no missed emails, no phone
                   tag, all logged in one place.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                <h5>Insightful Reports</h5>
                <p>Administrators get visual dashboards on resolution times, department workload,
                   and student satisfaction trends.</p>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="container py-5">
    <div class="text-center mb-5">
        <span class="section-eyebrow">How it works</span>
        <h2 class="display-6">Three simple steps</h2>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-num">1</div>
                <h5>Ask the AI</h5>
                <p class="text-muted small mb-0">
                    Start a chat. The AI answers most routine questions in seconds — no ticket needed.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-num">2</div>
                <h5>Submit a Complaint</h5>
                <p class="text-muted small mb-0">
                    For complex issues, file a formal complaint with attachments. It's auto-routed
                    to the right department.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-num">3</div>
                <h5>Track &amp; Resolve</h5>
                <p class="text-muted small mb-0">
                    Get notifications, message staff, and rate the resolution — all from one
                    dashboard.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="container my-5">
    <div class="card border-0 shadow-lg" style="background:var(--grad-hero);color:#fff;border-radius:var(--r-xl);">
        <div class="card-body p-5 text-center">
            <h2 class="text-white mb-2">Ready to get started?</h2>
            <p class="lead mb-4" style="color:rgba(255,255,255,0.9);">
                Create your student account in under a minute and submit your first complaint today.
            </p>
            <?php if (!is_logged_in()): ?>
                <a href="<?= e(url('public/register.php')) ?>" class="btn btn-light btn-lg">
                    Create Free Account <i class="bi bi-arrow-right ms-1"></i>
                </a>
            <?php else: ?>
                <a href="<?= e(url(current_role() . '/dashboard.php')) ?>" class="btn btn-light btn-lg">
                    Go to Dashboard <i class="bi bi-arrow-right ms-1"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
