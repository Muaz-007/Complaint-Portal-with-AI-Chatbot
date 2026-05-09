<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('student');
$user = current_user();
$pdo = db();

$complaint_id = (int) ($_GET['id'] ?? 0);
if ($complaint_id < 1) {
    flash('error', 'Invalid complaint ID.');
    redirect('student/complaints/list.php');
}

$complaint = load_complaint_for_viewer($complaint_id, 'student', (int) $user['id']);
if (!$complaint) {
    flash('error', 'Complaint not found or you do not have access to it.');
    redirect('student/complaints/list.php');
}

// Handle: post a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'message') {
    csrf_verify();
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($message !== '') {
        $stmt = $pdo->prepare(
            'INSERT INTO complaint_messages (complaint_id, sender_type, sender_id, message)
             VALUES (?, "student", ?, ?)'
        );
        $stmt->execute([$complaint_id, $user['id'], $message]);
        flash('success', 'Message sent.');
    }
    redirect('student/complaints/view.php?id=' . $complaint_id);
}

// Handle: submit feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'feedback') {
    csrf_verify();
    $rating  = (int) ($_POST['rating'] ?? 0);
    $comment = trim((string) ($_POST['comments'] ?? ''));

    if ($rating < 1 || $rating > 5) {
        flash('error', 'Please select a rating between 1 and 5.');
    } elseif ($complaint['status'] !== 'resolved') {
        flash('error', 'Feedback can only be submitted for resolved complaints.');
    } else {
        try {
            $pdo->prepare('INSERT INTO feedback (complaint_id, rating, comments) VALUES (?, ?, ?)')
                ->execute([$complaint_id, $rating, $comment ?: null]);
            $pdo->prepare('UPDATE complaints SET status = "closed" WHERE complaint_id = ?')
                ->execute([$complaint_id]);
            flash('success', 'Thank you for your feedback! Your complaint is now closed.');
        } catch (Throwable $e) {
            flash('error', APP_DEBUG ? $e->getMessage() : 'Could not save feedback.');
        }
    }
    redirect('student/complaints/view.php?id=' . $complaint_id);
}

$messages = load_complaint_messages($complaint_id);

// Existing feedback (if any)
$existing_feedback = null;
$stmt = $pdo->prepare('SELECT * FROM feedback WHERE complaint_id = ? LIMIT 1');
$stmt->execute([$complaint_id]);
$existing_feedback = $stmt->fetch() ?: null;

$page_title = $complaint['reference_no'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= e(url('student/complaints/list.php')) ?>" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Back to my complaints
    </a>
</div>

<div class="row g-4">
    <!-- Main column -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <code class="text-muted small"><?= e($complaint['reference_no']) ?></code>
                        <h4 class="mt-1 mb-0"><?= e($complaint['title']) ?></h4>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?= status_badge($complaint['status']) ?>
                        <?= priority_badge($complaint['priority']) ?>
                    </div>
                </div>

                <div class="row g-3 mb-3 small">
                    <div class="col-md-4">
                        <div class="text-muted">Category</div>
                        <div class="fw-semibold"><?= e($complaint['category']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Department</div>
                        <div class="fw-semibold"><?= e($complaint['department_name'] ?? 'Unassigned') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Submitted</div>
                        <div class="fw-semibold"><?= e(date('d M Y, h:i A', strtotime($complaint['created_at']))) ?></div>
                    </div>
                </div>

                <hr>

                <h6 class="text-muted text-uppercase small mb-2" style="letter-spacing:0.05em;">Description</h6>
                <p style="white-space:pre-wrap;"><?= e($complaint['description']) ?></p>

                <?php if ($complaint['attachment_path']): ?>
                    <hr>
                    <h6 class="text-muted text-uppercase small mb-2" style="letter-spacing:0.05em;">Attachment</h6>
                    <a href="<?= e(url($complaint['attachment_path'])) ?>" target="_blank"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-paperclip me-1"></i>
                        <?= e(basename($complaint['attachment_path'])) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Messages -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-chat-dots me-2"></i>Communication</h5>

                <?php if (empty($messages)): ?>
                    <p class="text-muted small mb-3">No messages yet. Send the first one below.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($messages as $m): ?>
                            <?php $mine = $m['sender_type'] === 'student' && (int) $m['sender_id'] === (int) $user['id']; ?>
                            <div class="d-flex <?= $mine ? 'justify-content-end' : '' ?>">
                                <div style="max-width:80%;">
                                    <div class="small text-muted mb-1">
                                        <strong><?= e(ucfirst($m['sender_type'])) ?></strong>
                                        · <?= e(time_ago($m['created_at'])) ?>
                                    </div>
                                    <div class="p-3" style="
                                        background:<?= $mine ? 'var(--grad-primary)' : 'var(--bg-soft)' ?>;
                                        color:<?= $mine ? '#fff' : 'var(--text-900)' ?>;
                                        border-radius:1rem;
                                        border-bottom-<?= $mine ? 'right' : 'left' ?>-radius:0.25rem;
                                        white-space:pre-wrap;">
                                        <?= e($m['message']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!in_array($complaint['status'], ['closed'], true)): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="message">
                        <div class="input-group">
                            <textarea name="message" class="form-control" rows="2"
                                      placeholder="Write a reply…" required></textarea>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="small text-muted mb-0"><i class="bi bi-lock me-1"></i> This complaint is closed. No further messages can be sent.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status timeline -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-3" style="letter-spacing:0.05em;">Status</h6>
                <div class="text-center py-3">
                    <?= status_badge($complaint['status']) ?>
                </div>
                <?php if ($complaint['assigned_staff_id']): ?>
                    <hr>
                    <div class="small text-muted">Assigned to</div>
                    <div class="fw-semibold"><?= e($complaint['staff_name']) ?></div>
                <?php endif; ?>
                <?php if ($complaint['resolved_at']): ?>
                    <hr>
                    <div class="small text-muted">Resolved on</div>
                    <div class="fw-semibold"><?= e(date('d M Y, h:i A', strtotime($complaint['resolved_at']))) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Feedback -->
        <?php if ($complaint['status'] === 'resolved' && !$existing_feedback): ?>
            <div class="card border-0 shadow-sm mb-3" style="background:var(--grad-soft);">
                <div class="card-body">
                    <h6><i class="bi bi-star-fill text-warning me-1"></i>Rate the resolution</h6>
                    <p class="small text-muted">Your feedback helps us improve.</p>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="feedback">
                        <div class="mb-2">
                            <select name="rating" class="form-select form-select-sm" required>
                                <option value="">Select rating</option>
                                <option value="5">★★★★★ Excellent</option>
                                <option value="4">★★★★ Good</option>
                                <option value="3">★★★ Okay</option>
                                <option value="2">★★ Poor</option>
                                <option value="1">★ Very Poor</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="comments" class="form-control form-control-sm" rows="3"
                                      placeholder="Comments (optional)"></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm w-100" type="submit">Submit Feedback</button>
                    </form>
                </div>
            </div>
        <?php elseif ($existing_feedback): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6><i class="bi bi-star-fill text-warning me-1"></i>Your feedback</h6>
                    <div class="fs-5"><?= str_repeat('★', (int) $existing_feedback['rating']) . str_repeat('☆', 5 - (int) $existing_feedback['rating']) ?></div>
                    <?php if ($existing_feedback['comments']): ?>
                        <p class="small text-muted mb-0 mt-1"><?= e($existing_feedback['comments']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
