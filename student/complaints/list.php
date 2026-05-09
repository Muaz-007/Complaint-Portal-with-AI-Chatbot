<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('student');
$user = current_user();

$filter = $_GET['status'] ?? 'all';
$valid_filters = ['all', 'pending', 'in_progress', 'resolved'];
if (!in_array($filter, $valid_filters, true)) $filter = 'all';

$sql = 'SELECT c.complaint_id, c.reference_no, c.title, c.category, c.status, c.priority,
               c.created_at, d.name AS department_name
        FROM complaints c
        LEFT JOIN departments d ON d.department_id = c.department_id
        WHERE c.student_id = ?';
$params = [$user['id']];

if ($filter !== 'all') {
    $sql .= ' AND c.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY c.created_at DESC';

$complaints = [];
try {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $complaints = $stmt->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load complaints.');
}

$page_title = 'My Complaints';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-collection me-2"></i>My Complaints</h3>
        <small class="text-muted">All complaints you've submitted, with their current status.</small>
    </div>
    <a href="<?= e(url('student/complaints/new.php')) ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Complaint
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-pills mb-3 gap-2 flex-wrap">
            <?php foreach ([
                'all' => ['All', 'bi-inbox'],
                'pending' => ['Pending', 'bi-hourglass-split'],
                'in_progress' => ['In Progress', 'bi-arrow-repeat'],
                'resolved' => ['Resolved', 'bi-check-circle'],
            ] as $key => [$label, $icon]): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $filter === $key ? 'active' : '' ?>"
                       href="<?= e(url('student/complaints/list.php?status=' . $key)) ?>">
                        <i class="bi <?= e($icon) ?> me-1"></i> <?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (empty($complaints)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <h5>No complaints found</h5>
                <p class="mb-3">You haven't submitted any complaints<?= $filter !== 'all' ? ' with this status' : '' ?> yet.</p>
                <a href="<?= e(url('student/complaints/new.php')) ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Submit your first complaint
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="letter-spacing:0.05em;">
                        <tr>
                            <th>Reference</th>
                            <th>Title</th>
                            <th>Category</th>
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
                                    <small class="text-muted"><?= e($c['department_name'] ?? '—') ?></small>
                                </td>
                                <td><span class="text-muted small"><?= e($c['category']) ?></span></td>
                                <td><?= priority_badge($c['priority']) ?></td>
                                <td><?= status_badge($c['status']) ?></td>
                                <td><small class="text-muted"><?= e(time_ago($c['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <a href="<?= e(url('student/complaints/view.php?id=' . $c['complaint_id'])) ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        View <i class="bi bi-arrow-right ms-1"></i>
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
