<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('admin');
$pdo = db();

// --- KPIs ---
$kpis = ['total' => 0, 'open' => 0, 'resolved' => 0, 'avg_hours' => 0, 'avg_rating' => null, 'total_chats' => 0];
try {
    $kpis['total']     = (int) $pdo->query('SELECT COUNT(*) FROM complaints')->fetchColumn();
    $kpis['open']      = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status NOT IN ('resolved','closed')")->fetchColumn();
    $kpis['resolved']  = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('resolved','closed')")->fetchColumn();
    $avgH = $pdo->query('SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) FROM complaints WHERE resolved_at IS NOT NULL')->fetchColumn();
    $kpis['avg_hours'] = $avgH ? round((float) $avgH, 1) : 0;
    $avgR = $pdo->query('SELECT AVG(rating) FROM feedback')->fetchColumn();
    $kpis['avg_rating'] = $avgR ? round((float) $avgR, 1) : null;
    $kpis['total_chats'] = (int) $pdo->query('SELECT COUNT(*) FROM chatbot_logs')->fetchColumn();
} catch (Throwable $e) { /* ignore */ }

// --- Status breakdown ---
$statusBreakdown = [];
try {
    $statusBreakdown = $pdo->query(
        'SELECT status, COUNT(*) AS n FROM complaints GROUP BY status'
    )->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// --- Department breakdown ---
$deptBreakdown = [];
try {
    $deptBreakdown = $pdo->query(
        "SELECT COALESCE(d.name, 'Unassigned') AS dept_name, COUNT(c.complaint_id) AS n
         FROM complaints c
         LEFT JOIN departments d ON d.department_id = c.department_id
         GROUP BY dept_name ORDER BY n DESC"
    )->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// --- Priority breakdown ---
$priorityBreakdown = [];
try {
    $priorityBreakdown = $pdo->query(
        "SELECT priority, COUNT(*) AS n FROM complaints
         GROUP BY priority
         ORDER BY FIELD(priority,'urgent','high','medium','low')"
    )->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// --- Submissions over the last 14 days ---
$daily = [];
try {
    $rows = $pdo->query(
        "SELECT DATE(created_at) AS d, COUNT(*) AS n
         FROM complaints
         WHERE created_at >= (NOW() - INTERVAL 14 DAY)
         GROUP BY DATE(created_at)
         ORDER BY d ASC"
    )->fetchAll();
    $byDate = [];
    foreach ($rows as $r) $byDate[$r['d']] = (int) $r['n'];
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $daily[] = ['date' => $d, 'n' => $byDate[$d] ?? 0];
    }
} catch (Throwable $e) { /* ignore */ }

// --- Satisfaction ratings distribution ---
$ratings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
try {
    $rows = $pdo->query('SELECT rating, COUNT(*) AS n FROM feedback GROUP BY rating')->fetchAll();
    foreach ($rows as $r) $ratings[(int) $r['rating']] = (int) $r['n'];
} catch (Throwable $e) { /* ignore */ }

$page_title = 'Reports & Analytics';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-bar-chart me-2"></i>Reports &amp; Analytics</h3>
        <small class="text-muted">Visual insights into complaint trends and system performance.</small>
    </div>
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Print / Save as PDF
    </button>
</div>

<!-- KPI row -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-label">Total</div>
            <div class="stat-value"><?= e((string) $kpis['total']) ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-label">Open</div>
            <div class="stat-value"><?= e((string) $kpis['open']) ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-label">Resolved</div>
            <div class="stat-value"><?= e((string) $kpis['resolved']) ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-label">Avg. Hours</div>
            <div class="stat-value"><?= e((string) $kpis['avg_hours']) ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-label">Avg. Rating</div>
            <div class="stat-value"><?= $kpis['avg_rating'] !== null ? e((string) $kpis['avg_rating']) : '—' ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-label">AI Chats</div>
            <div class="stat-value"><?= e((string) $kpis['total_chats']) ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-graph-up me-1"></i>Submissions — Last 14 Days</h6>
                <canvas id="dailyChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-pie-chart me-1"></i>By Status</h6>
                <canvas id="statusChart" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-diagram-3 me-1"></i>By Department</h6>
                <canvas id="deptChart" height="160"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-flag me-1"></i>By Priority</h6>
                <canvas id="priorityChart" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-star-fill text-warning me-1"></i>Student Satisfaction Ratings</h6>
        <canvas id="ratingChart" height="80"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    const palette = {
        primary: '#4f46e5', accent: '#06b6d4', success: '#10b981',
        warning: '#f59e0b', danger: '#ef4444', neutral: '#94a3b8', violet: '#7c3aed',
    };
    Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
    Chart.defaults.color = '#475569';

    // 14-day daily line
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(fn($d) => date('d M', strtotime($d['date'])), $daily)) ?>,
            datasets: [{
                label: 'Submissions',
                data: <?= json_encode(array_column($daily, 'n')) ?>,
                fill: true,
                borderColor: palette.primary,
                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                tension: 0.35,
                pointBackgroundColor: palette.primary,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Status doughnut
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map(fn($r) => ucfirst(str_replace('_', ' ', $r['status'])), $statusBreakdown)) ?>,
            datasets: [{
                data: <?= json_encode(array_column($statusBreakdown, 'n')) ?>,
                backgroundColor: [palette.warning, palette.accent, palette.neutral, palette.success, '#1e293b', palette.violet],
                borderWidth: 0,
            }]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { padding: 12 } } } }
    });

    // Department bar
    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($deptBreakdown, 'dept_name')) ?>,
            datasets: [{
                label: 'Complaints',
                data: <?= json_encode(array_column($deptBreakdown, 'n')) ?>,
                backgroundColor: palette.primary,
                borderRadius: 6,
            }]
        },
        options: { plugins: { legend: { display: false } }, indexAxis: 'y',
                   scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Priority bar
    new Chart(document.getElementById('priorityChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($r) => ucfirst($r['priority']), $priorityBreakdown)) ?>,
            datasets: [{
                label: 'Complaints',
                data: <?= json_encode(array_column($priorityBreakdown, 'n')) ?>,
                backgroundColor: [palette.danger, palette.warning, palette.accent, palette.neutral],
                borderRadius: 6,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Ratings bar
    new Chart(document.getElementById('ratingChart'), {
        type: 'bar',
        data: {
            labels: ['1 ★', '2 ★', '3 ★', '4 ★', '5 ★'],
            datasets: [{
                label: 'Responses',
                data: [<?= (int) $ratings[1] ?>, <?= (int) $ratings[2] ?>, <?= (int) $ratings[3] ?>, <?= (int) $ratings[4] ?>, <?= (int) $ratings[5] ?>],
                backgroundColor: [palette.danger, palette.warning, palette.neutral, palette.accent, palette.success],
                borderRadius: 6,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
