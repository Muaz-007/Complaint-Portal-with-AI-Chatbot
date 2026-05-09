<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/complaint_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_login('staff');

$user   = current_user();
$deptId = current_user_dept();
$pdo    = db();

if (!$deptId) {
    flash('error', 'Your account is not linked to a department. Please contact the administrator.');
    redirect('public/logout.php');
}

// Department name
$dept_name = '';
try {
    $stmt = $pdo->prepare('SELECT name FROM departments WHERE department_id = ?');
    $stmt->execute([$deptId]);
    $dept_name = (string) $stmt->fetchColumn();
} catch (Throwable $e) { /* ignore */ }

// Stats for this department
$stats = ['assigned' => 0, 'urgent' => 0, 'in_progress' => 0, 'resolved_30d' => 0];
$recent = [];
try {
    $stmt = $pdo->prepare(
        'SELECT
            SUM(status IN ("pending","in_progress","on_hold","reopened")) AS assigned,
            SUM(priority = "urgent" AND status NOT IN ("resolved","closed")) AS urgent,
            SUM(status = "in_progress") AS in_progress,
            SUM(status IN ("resolved","closed") AND resolved_at >= (NOW() - INTERVAL 30 DAY)) AS resolved_30d
         FROM complaints WHERE department_id = ?'
    );
    $stmt->execute([$deptId]);
    $row = $stmt->fetch();
    if ($row) {
        $stats['assigned']     = (int) $row['assigned'];
        $stats['urgent']       = (int) $row['urgent'];
        $stats['in_progress']  = (int) $row['in_progress'];
        $stats['resolved_30d'] = (int) $row['resolved_30d'];
    }

    $stmt = $pdo->prepare(
        'SELECT c.complaint_id, c.reference_no, c.title, c.status, c.priority, c.created_at,
                s.name AS student_name
         FROM complaints c
         JOIN students s ON s.student_id = c.student_id
         WHERE c.department_id = ?
           AND c.status IN ("pending","in_progress","on_hold","reopened")
         ORDER BY FIELD(c.priority,"urgent","high","medium","low"), c.created_at DESC
         LIMIT 5'
    );
    $stmt->execute([$deptId]);
    $recent = $stmt->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load dashboard data.');
}

$page_title = 'Staff Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome banner -->
<div class="card border-0 shadow-sm mb-4 fade-up" style="background:var(--grad-hero);color:#fff;border-radius:var(--r-lg);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="hero-badge mb-2" style="margin-bottom:0.75rem;"><i class="bi bi-people-fill"></i> <?= e($dept_name ?: 'Department Staff') ?></span>
            <h3 class="text-white mb-1"><?= e($user['name'] ?: 'Staff') ?></h3>
            <p class="mb-0" style="color:rgba(255,255,255,0.9);">
                Review and resolve complaints assigned to your department.
            </p>
        </div>
        <a href="<?= e(url('staff/complaints/list.php')) ?>" class="btn btn-light btn-lg">
            <i class="bi bi-clipboard-data me-1"></i> Open Queue
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3 fade-up">
        <a href="<?= e(url('staff/complaints/list.php?status=open')) ?>" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon bg-grad-primary"><i class="bi bi-inbox-fill"></i></div>
                <div class="stat-label">Open</div>
                <div class="stat-value"><?= e((string) $stats['assigned']) ?></div>
                <div class="stat-trend">All open tickets</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-1">
        <a href="<?= e(url('staff/complaints/list.php?status=urgent')) ?>" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon bg-grad-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="stat-label">Urgent</div>
                <div class="stat-value"><?= e((string) $stats['urgent']) ?></div>
                <div class="stat-trend">High priority</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-2">
        <a href="<?= e(url('staff/complaints/list.php?status=in_progress')) ?>" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon bg-grad-accent"><i class="bi bi-arrow-repeat"></i></div>
                <div class="stat-label">In Progress</div>
                <div class="stat-value"><?= e((string) $stats['in_progress']) ?></div>
                <div class="stat-trend">Currently working</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3 fade-up delay-3">
        <a href="<?= e(url('staff/complaints/list.php?status=resolved')) ?>" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon bg-grad-success"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-label">Resolved (30d)</div>
                <div class="stat-value"><?= e((string) $stats['resolved_30d']) ?></div>
                <div class="stat-trend up"><i class="bi bi-graph-up-arrow me-1"></i>Past month</div>
            </div>
        </a>
    </div>
</div>

<!-- Priority queue preview -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Priority Queue</h5>
            <a href="<?= e(url('staff/complaints/list.php')) ?>" class="small text-decoration-none">
                View full queue <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($recent)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-clipboard-check"></i></div>
                <h5>All clear!</h5>
                <p class="mb-0">No open complaints assigned to your department right now.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="letter-spacing:0.05em;">
                        <tr>
                            <th>Reference</th>
                            <th>Title</th>
                            <th>Student</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $c): ?>
                            <tr>
                                <td><code><?= e($c['reference_no']) ?></code></td>
                                <td class="fw-semibold"><?= e($c['title']) ?></td>
                                <td><small class="text-muted"><?= e($c['student_name']) ?></small></td>
                                <td><?= priority_badge($c['priority']) ?></td>
                                <td><?= status_badge($c['status']) ?></td>
                                <td><small class="text-muted"><?= e(time_ago($c['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <a href="<?= e(url('staff/complaints/view.php?id=' . $c['complaint_id'])) ?>"
                                       class="btn btn-sm btn-primary">Open</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
