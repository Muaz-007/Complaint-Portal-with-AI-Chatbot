<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('staff');
$user    = current_user();
$deptId  = current_user_dept();
$pdo     = db();

if (!$deptId) {
    flash('error', 'Your account is not linked to a department. Please contact the administrator.');
    redirect('public/logout.php');
}

$filter = $_GET['status'] ?? 'open';
$valid_filters = ['open', 'all', 'pending', 'in_progress', 'resolved', 'urgent'];
if (!in_array($filter, $valid_filters, true)) $filter = 'open';

$sql = 'SELECT c.complaint_id, c.reference_no, c.title, c.category, c.status, c.priority,
               c.created_at, s.name AS student_name, s.roll_no
        FROM complaints c
        JOIN students s ON s.student_id = c.student_id
        WHERE c.department_id = ?';
$params = [$deptId];

switch ($filter) {
    case 'open':        $sql .= ' AND c.status IN ("pending","in_progress","on_hold","reopened")'; break;
    case 'pending':     $sql .= ' AND c.status = "pending"'; break;
    case 'in_progress': $sql .= ' AND c.status = "in_progress"'; break;
    case 'resolved':    $sql .= ' AND c.status IN ("resolved","closed")'; break;
    case 'urgent':      $sql .= ' AND c.priority = "urgent"'; break;
    // 'all' adds no extra clause
}
$sql .= ' ORDER BY
            FIELD(c.priority,"urgent","high","medium","low"),
            c.created_at DESC';

$complaints = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $complaints = $stmt->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load complaints.');
}

$page_title = 'Department Queue';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-clipboard-data me-2"></i>Department Queue</h3>
        <small class="text-muted">Complaints assigned to your department, sorted by priority.</small>
    </div>
    <a href="<?= e(url('staff/dashboard.php')) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-pills mb-3 gap-2 flex-wrap">
            <?php foreach ([
                'open'        => ['Open', 'bi-inbox'],
                'urgent'      => ['Urgent', 'bi-exclamation-triangle'],
                'pending'     => ['Pending', 'bi-hourglass-split'],
                'in_progress' => ['In Progress', 'bi-arrow-repeat'],
                'resolved'    => ['Resolved', 'bi-check-circle'],
                'all'         => ['All', 'bi-collection'],
            ] as $key => [$label, $icon]): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $filter === $key ? 'active' : '' ?>"
                       href="<?= e(url('staff/complaints/list.php?status=' . $key)) ?>">
                        <i class="bi <?= e($icon) ?> me-1"></i> <?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (empty($complaints)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <h5>No complaints in this view</h5>
                <p class="mb-0">Try a different filter or check back later.</p>
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
                        <?php foreach ($complaints as $c): ?>
                            <tr>
                                <td><code><?= e($c['reference_no']) ?></code></td>
                                <td>
                                    <div class="fw-semibold"><?= e($c['title']) ?></div>
                                    <small class="text-muted"><?= e($c['category']) ?></small>
                                </td>
                                <td>
                                    <div class="small"><?= e($c['student_name']) ?></div>
                                    <small class="text-muted"><?= e($c['roll_no']) ?></small>
                                </td>
                                <td><?= priority_badge($c['priority']) ?></td>
                                <td><?= status_badge($c['status']) ?></td>
                                <td><small class="text-muted"><?= e(time_ago($c['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <a href="<?= e(url('staff/complaints/view.php?id=' . $c['complaint_id'])) ?>"
                                       class="btn btn-sm btn-primary">
                                        Open <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
