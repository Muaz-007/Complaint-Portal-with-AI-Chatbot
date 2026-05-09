<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

require_login('admin');
$pdo = db();
$me  = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create' || $action === 'update') {
            $category = trim((string) ($_POST['category'] ?? 'general'));
            $keywords = trim((string) ($_POST['keywords'] ?? ''));
            $question = trim((string) ($_POST['question'] ?? ''));
            $answer   = trim((string) ($_POST['answer']   ?? ''));
            $errors   = [];
            if ($keywords === '') $errors[] = 'Keywords are required.';
            if ($question === '') $errors[] = 'Question is required.';
            if ($answer === '')   $errors[] = 'Answer is required.';

            if (empty($errors)) {
                if ($action === 'create') {
                    $pdo->prepare(
                        'INSERT INTO faqs (category, keywords, question, answer) VALUES (?, ?, ?, ?)'
                    )->execute([$category, $keywords, $question, $answer]);
                    $newId = (int) $pdo->lastInsertId();
                    log_audit('admin', (int) $me['id'], 'faq.create', 'faqs', $newId, $question);
                    flash('success', 'FAQ added.');
                } else {
                    $id = (int) ($_POST['faq_id'] ?? 0);
                    $pdo->prepare(
                        'UPDATE faqs SET category = ?, keywords = ?, question = ?, answer = ? WHERE faq_id = ?'
                    )->execute([$category, $keywords, $question, $answer, $id]);
                    log_audit('admin', (int) $me['id'], 'faq.update', 'faqs', $id, $question);
                    flash('success', 'FAQ updated.');
                }
            } else {
                foreach ($errors as $err) flash('error', $err);
            }
        } elseif ($action === 'toggle') {
            $id    = (int) ($_POST['faq_id'] ?? 0);
            $state = (int) ($_POST['new_state'] ?? 0);
            $pdo->prepare('UPDATE faqs SET is_active = ? WHERE faq_id = ?')->execute([$state, $id]);
            flash('success', $state ? 'FAQ activated.' : 'FAQ disabled.');
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['faq_id'] ?? 0);
            $pdo->prepare('DELETE FROM faqs WHERE faq_id = ?')->execute([$id]);
            log_audit('admin', (int) $me['id'], 'faq.delete', 'faqs', $id);
            flash('success', 'FAQ deleted.');
        }
    } catch (Throwable $e) {
        flash('error', APP_DEBUG ? $e->getMessage() : 'Action failed.');
    }
    redirect('admin/faqs/list.php');
}

$faqs = [];
try {
    $faqs = $pdo->query('SELECT * FROM faqs ORDER BY hit_count DESC, faq_id DESC')->fetchAll();
} catch (Throwable $e) {
    flash('error', APP_DEBUG ? $e->getMessage() : 'Could not load FAQs. Have you re-imported the schema?');
}

$page_title = 'AI Knowledge Base';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-robot me-2"></i>AI Chatbot Knowledge Base</h3>
        <small class="text-muted">FAQs the chatbot uses to answer student queries instantly.</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newFaqModal">
        <i class="bi bi-plus-circle me-1"></i> Add FAQ
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($faqs)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-robot"></i></div>
                <h5>No FAQs yet</h5>
                <p>Add at least one FAQ so the chatbot can answer student queries.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="letter-spacing:0.05em;">
                        <tr>
                            <th>Category</th><th>Question</th><th>Keywords</th>
                            <th>Hits</th><th>Status</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faqs as $f): ?>
                            <tr>
                                <td><span class="badge badge-priority-medium"><?= e($f['category']) ?></span></td>
                                <td>
                                    <div class="fw-semibold"><?= e($f['question']) ?></div>
                                    <small class="text-muted text-truncate-2"><?= e($f['answer']) ?></small>
                                </td>
                                <td><small class="text-muted text-truncate-2" style="max-width:240px;display:inline-block;">
                                    <?= e($f['keywords']) ?>
                                </small></td>
                                <td><?= (int) $f['hit_count'] ?></td>
                                <td>
                                    <?php if ((int) $f['is_active'] === 1): ?>
                                        <span class="badge badge-status-resolved">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-status-on-hold">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#editFaq<?= (int) $f['faq_id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="faq_id" value="<?= (int) $f['faq_id'] ?>">
                                            <input type="hidden" name="new_state" value="<?= (int) $f['is_active'] === 1 ? 0 : 1 ?>">
                                            <button type="submit"
                                                    class="btn btn-sm <?= (int) $f['is_active'] === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                <i class="bi <?= (int) $f['is_active'] === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="faq_id" value="<?= (int) $f['faq_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-confirm="Delete this FAQ?">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php foreach ($faqs as $f): ?>
                <div class="modal fade" id="editFaq<?= (int) $f['faq_id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <form method="post" class="modal-content">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="faq_id" value="<?= (int) $f['faq_id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit FAQ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Category</label>
                                        <input name="category" class="form-control" value="<?= e($f['category']) ?>" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Keywords <small class="text-muted">(comma-separated)</small></label>
                                        <input name="keywords" class="form-control" value="<?= e($f['keywords']) ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Question</label>
                                        <input name="question" class="form-control" value="<?= e($f['question']) ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Answer</label>
                                        <textarea name="answer" class="form-control" rows="4" required><?= e($f['answer']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create modal -->
<div class="modal fade" id="newFaqModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i>New FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <input name="category" class="form-control" placeholder="e.g. Library" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Keywords <small class="text-muted">(comma-separated)</small></label>
                        <input name="keywords" class="form-control" placeholder="library, hours, timing" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Question</label>
                        <input name="question" class="form-control" placeholder="What are the library timings?" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Answer</label>
                        <textarea name="answer" class="form-control" rows="4"
                                  placeholder="The library is open Mon–Fri, 8 AM to 10 PM…" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Create FAQ</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
