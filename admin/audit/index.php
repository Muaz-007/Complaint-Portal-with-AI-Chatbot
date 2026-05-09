<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('admin');
$pdo = db();

$page = max(1, (int) ($_GET['page'] ?? 1));
$per  = 25;
$offset = ($page - 1) * $per;

$actor_filter = $_GET['actor'] ?? 'all';
$valid_actors = ['all', 'student', 'staff', 'admin', 'system'];
if (!in_array($actor_filter, $valid_actors, true)) $actor_filter = 'all';

$where  = '';
$params = [];
if ($actor_filter !== 'all') {
    $where = ' WHERE actor_type = ?';
    $params[] = $actor_filter;
}

$total = 0;
$entries = [];
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM audit_log' . $where);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $sql = 'SELECT * FROM audit_log' . $where . ' ORDER BY created_at DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $entries = $stmt->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load audit log.');
}

$pages = max(1, (int) ceil($total / $per));

$page_title = 'Audit Trail';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-shield-lock me-2"></i>Audit Trail</h3>
        <small class="text-muted">Complete log of every recorded action — total <?= e((string) $total) ?> entries.</small>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-pills mb-3 gap-2">
            <?php foreach (['all' => 'All', 'admin' => 'Admin', 'staff' => 'Staff', 'student' => 'Student', 'system' => 'System'] as $k => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $actor_filter === $k ? 'active' : '' ?>"
                       href="<?= e(url('admin/audit/index.php?actor=' . $k)) ?>"><?= e($label) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (empty($entries)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-shield"></i></div>
                <h5>No audit entries</h5>
                <p class="mb-0">Actions like user creation, status changes, and FAQ edits will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle small">
                    <thead class="text-muted text-uppercase" style="letter-spacing:0.05em;font-size:0.75rem;">
                        <tr>
                            <th>When</th>
                            <th>Actor</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>Details</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $a): ?>
                            <tr>
                                <td>
                                    <div><?= e(date('d M Y, H:i', strtotime($a['created_at']))) ?></div>
                                    <small class="text-muted"><?= e(time_ago($a['created_at'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-priority-medium"><?= e(ucfirst($a['actor_type'])) ?></span>
                                    <?php if ($a['actor_id']): ?>
                                        <small class="text-muted">#<?= e((string) $a['actor_id']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= e($a['action']) ?></code></td>
                                <td>
                                    <?php if ($a['target_table']): ?>
                                        <?= e($a['target_table']) ?>
                                        <?php if ($a['target_id']): ?>
                                            <small class="text-muted">#<?= e((string) $a['target_id']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted text-truncate-2" style="max-width:300px;display:inline-block;"><?= e($a['details'] ?? '') ?></small></td>
                                <td><small class="text-muted"><?= e($a['ip_address'] ?? '—') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&actor=<?= e($actor_filter) ?>">Previous</a>
                        </li>
                        <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $p ?>&actor=<?= e($actor_filter) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&actor=<?= e($actor_filter) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
