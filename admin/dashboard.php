<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/complaint_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_login('admin');

$user = current_user();
$pdo  = db();

$stats = ['students' => 0, 'staff' => 0, 'departments' => 0, 'complaints' => 0,
          'open' => 0, 'urgent' => 0, 'avg_resolution_hours' => 0, 'avg_rating' => null];
$recent = [];
try {
    $stats['students']    = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
    $stats['staff']       = (int) $pdo->query('SELECT COUNT(*) FROM staff')->fetchColumn();
    $stats['departments'] = (int) $pdo->query('SELECT COUNT(*) FROM departments WHERE is_active = 1')->fetchColumn();
    $stats['complaints']  = (int) $pdo->query('SELECT COUNT(*) FROM complaints')->fetchColumn();
    $stats['open']        = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('pending','in_progress','on_hold','reopened')")->fetchColumn();
    $stats['urgent']      = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE priority = 'urgent' AND status NOT IN ('resolved','closed')")->fetchColumn();

    $avgHours = $pdo->query(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) FROM complaints WHERE resolved_at IS NOT NULL"
    )->fetchColumn();
    $stats['avg_resolution_hours'] = $avgHours !== null && $avgHours !== false ? round((float) $avgHours, 1) : 0;

    $avgRating = $pdo->query('SELECT AVG(rating) FROM feedback')->fetchColumn();
    $stats['avg_rating'] = $avgRating !== null && $avgRating !== false ? round((float) $avgRating, 1) : null;

    $recent = $pdo->query(
        "SELECT c.complaint_id, c.reference_no, c.title, c.status, c.priority, c.created_at,
                s.name AS student_name, d.name AS department_name
         FROM complaints c
         JOIN students s ON s.student_id = c.student_id
         LEFT JOIN departments d ON d.department_id = c.department_id
         ORDER BY c.created_at DESC LIMIT 6"
    )->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load dashboard data.');
}

$page_title = 'Admin Dashboard';
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
            <a href="<?= e(url('admin/reports/index.php')) ?>" class="btn btn-light">
                <i class="bi bi-bar-chart me-1"></i> View Reports
            </a>
        </div>
    </div>
</div>

<!-- KPI Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3 fade-up">
        <a href="<?= e(url('admin/users/list.php?type=student')) ?>" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon bg-grad-primary"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="stat-label">Students</div>
                <div class="stat-value"><?= e((string) $stats['students']) ?></div>
                <div class="stat-trend">Registered</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-1">
        <a href="<?= e(url('admin/users/list.php?type=staff')) ?>" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon bg-grad-accent"><i class="bi bi-people-fill"></i></div>
                <div class="stat-label">Staff</div>
                <div class="stat-value"><?= e((string) $stats['staff']) ?></div>
                <div class="stat-trend">Across departments</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-2">
        <a href="<?= e(url('admin/departments/list.php')) ?>" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon bg-grad-warning"><i class="bi bi-diagram-3-fill"></i></div>
                <div class="stat-label">Departments</div>
                <div class="stat-value"><?= e((string) $stats['departments']) ?></div>
                <div class="stat-trend">Active</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-3">
        <div class="stat-card">
            <div class="stat-icon bg-grad-success"><i class="bi bi-clipboard-data-fill"></i></div>
            <div class="stat-label">Total Complaints</div>
            <div class="stat-value"><?= e((string) $stats['complaints']) ?></div>
            <div class="stat-trend up"><i class="bi bi-graph-up-arrow me-1"></i>All time</div>
        </div>
    </div>
</div>

<!-- Secondary KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                    <i class="bi bi-inbox"></i>
                </div>
                <div>
                    <div class="small text-muted">Open Complaints</div>
                    <div class="fw-bold fs-4"><?= e((string) $stats['open']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                    <i class="bi bi-stopwatch"></i>
                </div>
                <div>
                    <div class="small text-muted">Avg. Resolution Time</div>
                    <div class="fw-bold fs-4"><?= e((string) $stats['avg_resolution_hours']) ?> <small>hrs</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="small text-muted">Avg. Satisfaction</div>
                    <div class="fw-bold fs-4">
                        <?= $stats['avg_rating'] !== null ? e((string) $stats['avg_rating']) . ' <small>/ 5</small>' : '—' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Module shortcuts -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <h5 class="mb-3"><i class="bi bi-grid me-2"></i>Management Modules</h5>
    </div>
    <?php foreach ([
        ['Users',           'admin/users/list.php',         'bi-people',         'Manage student, staff, and admin accounts'],
        ['Departments',     'admin/departments/list.php',   'bi-diagram-3',      'Create and configure complaint categories'],
        ['Knowledge Base',  'admin/faqs/list.php',          'bi-robot',          'Manage AI chatbot FAQs and responses'],
        ['Reports',         'admin/reports/index.php',      'bi-bar-chart',      'Analytics, charts, and exports'],
        ['Audit Trail',     'admin/audit/index.php',        'bi-shield-lock',    'Complete log of system activity'],
    ] as [$title, $path, $icon, $desc]): ?>
        <div class="col-md-6 col-lg-4">
            <a href="<?= e(url($path)) ?>" class="card border-0 shadow-sm h-100 text-decoration-none">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin:0;">
                            <i class="bi <?= e($icon) ?>"></i>
                        </div>
                        <h6 class="mb-0"><?= e($title) ?></h6>
                    </div>
                    <p class="small text-muted mb-0"><?= e($desc) ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Recent complaints -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h5>
        </div>
        <?php if (empty($recent)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-clipboard"></i></div>
                <h5>No complaints yet</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="letter-spacing:0.05em;">
                        <tr>
                            <th>Reference</th><th>Title</th><th>Student</th>
                            <th>Department</th><th>Priority</th><th>Status</th><th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $c): ?>
                            <tr>
                                <td><code><?= e($c['reference_no']) ?></code></td>
                                <td class="fw-semibold"><?= e($c['title']) ?></td>
                                <td><small><?= e($c['student_name']) ?></small></td>
                                <td><small><?= e($c['department_name'] ?? '—') ?></small></td>
                                <td><?= priority_badge($c['priority']) ?></td>
                                <td><?= status_badge($c['status']) ?></td>
                                <td><small class="text-muted"><?= e(time_ago($c['created_at'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
