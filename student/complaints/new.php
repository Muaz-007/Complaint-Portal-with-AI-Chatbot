<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('student');
$user = current_user();

$old = ['title' => '', 'category' => '', 'priority' => 'medium', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old['title']       = trim((string) ($_POST['title'] ?? ''));
    $old['category']    = (string) ($_POST['category'] ?? '');
    $old['priority']    = (string) ($_POST['priority'] ?? 'medium');
    $old['description'] = trim((string) ($_POST['description'] ?? ''));

    $errors = [];
    if ($old['title'] === '' || strlen($old['title']) > 200)
        $errors[] = 'Title is required (max 200 characters).';
    if (!in_array($old['category'], COMPLAINT_CATEGORIES, true))
        $errors[] = 'Please select a valid category.';
    if (!in_array($old['priority'], COMPLAINT_PRIORITIES, true))
        $errors[] = 'Please select a valid priority.';
    if ($old['description'] === '')
        $errors[] = 'Description is required.';

    if (empty($errors)) {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            $deptId = department_id_for_category($old['category']);

            $stmt = $pdo->prepare(
                'INSERT INTO complaints (reference_no, title, description, category, priority, student_id, department_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            // Insert with a placeholder reference, then update with the real one based on the row id.
            $tempRef = 'TMP-' . bin2hex(random_bytes(4));
            $stmt->execute([
                $tempRef, $old['title'], $old['description'], $old['category'],
                $old['priority'], $user['id'], $deptId,
            ]);
            $complaintId = (int) $pdo->lastInsertId();
            $referenceNo = generate_reference_no($complaintId);

            // Save attachment now that we have the reference number.
            $attachmentPath = null;
            if (!empty($_FILES['attachment']['name'])) {
                $attachmentPath = save_complaint_attachment($_FILES['attachment'], $referenceNo);
            }

            $pdo->prepare('UPDATE complaints SET reference_no = ?, attachment_path = ? WHERE complaint_id = ?')
                ->execute([$referenceNo, $attachmentPath, $complaintId]);

            $pdo->commit();
            flash('success', 'Your complaint has been submitted. Reference: ' . $referenceNo);
            redirect('student/complaints/view.php?id=' . $complaintId);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = APP_DEBUG ? 'Error: ' . $e->getMessage() : 'Could not submit your complaint. Please try again.';
        }
    }

    foreach ($errors as $err) flash('error', $err);
}

$page_title = 'Submit Complaint';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= e(url('student/dashboard.php')) ?>" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Back to dashboard
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-1"><i class="bi bi-pencil-square me-2"></i>Submit a New Complaint</h4>
                <p class="text-muted small mb-4">
                    Provide as much detail as possible. Your complaint will be auto-routed to the
                    correct department.
                </p>

                <form method="post" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" class="form-control"
                               placeholder="A short summary of the issue"
                               value="<?= e($old['title']) ?>" maxlength="200" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-select" required>
                                <option value="">— Select category —</option>
                                <?php foreach (COMPLAINT_CATEGORIES as $cat): ?>
                                    <option value="<?= e($cat) ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>>
                                        <?= e($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label">Priority</label>
                            <select id="priority" name="priority" class="form-select" required>
                                <?php foreach (COMPLAINT_PRIORITIES as $p): ?>
                                    <option value="<?= e($p) ?>" <?= $old['priority'] === $p ? 'selected' : '' ?>>
                                        <?= e(ucfirst($p)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="6"
                                  placeholder="Describe what happened, when, where, and any details that will help us resolve it."
                                  required><?= e($old['description']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="attachment" class="form-label">
                            Attachment <small class="text-muted">(optional, max 5 MB)</small>
                        </label>
                        <input type="file" id="attachment" name="attachment" class="form-control"
                               accept=".jpg,.jpeg,.png,.pdf,.docx">
                        <small class="text-muted">Allowed: JPG, PNG, PDF, DOCX</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Submit Complaint
                        </button>
                        <a href="<?= e(url('student/dashboard.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3" style="background:var(--grad-soft);">
            <div class="card-body">
                <h6><i class="bi bi-info-circle me-1"></i> Before you submit</h6>
                <p class="small text-muted mb-2">
                    Have you tried our AI assistant? It answers most routine questions instantly —
                    no ticket needed.
                </p>
                <a href="#" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-chat-dots me-1"></i> Try AI Chat
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6><i class="bi bi-list-ul me-1"></i> Tips for faster resolution</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Use a clear, specific title</li>
                    <li>Pick the correct category</li>
                    <li>Add screenshots or documents if relevant</li>
                    <li>Set urgency honestly so we can prioritize</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
