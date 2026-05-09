<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/complaint_helpers.php';
require_once __DIR__ . '/../../config/database.php';

require_login('staff');
$user   = current_user();
$deptId = current_user_dept();
$pdo    = db();

$complaint_id = (int) ($_GET['id'] ?? 0);
if ($complaint_id < 1) {
    flash('error', 'Invalid complaint ID.');
    redirect('staff/complaints/list.php');
}

$complaint = load_complaint_for_viewer($complaint_id, 'staff', (int) $user['id'], $deptId);
if (!$complaint) {
    flash('error', 'Complaint not found, or it is not assigned to your department.');
    redirect('staff/complaints/list.php');
}

// --- Action handlers ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'message' || $action === 'note') {
            $message = trim((string) ($_POST['message'] ?? ''));
            if ($message !== '') {
                $isNote = $action === 'note' ? 1 : 0;
                $stmt = $pdo->prepare(
                    'INSERT INTO complaint_messages (complaint_id, sender_type, sender_id, message, is_internal_note)
                     VALUES (?, "staff", ?, ?, ?)'
                );
                $stmt->execute([$complaint_id, $user['id'], $message, $isNote]);
                flash('success', $isNote ? 'Internal note added.' : 'Message sent to student.');
            }
        } elseif ($action === 'accept') {
            $pdo->prepare(
                'UPDATE complaints SET status = "in_progress", assigned_staff_id = ? WHERE complaint_id = ?'
            )->execute([$user['id'], $complaint_id]);
            flash('success', 'You have accepted this complaint. Status set to In Progress.');
        } elseif ($action === 'status') {
            $newStatus = $_POST['status'] ?? '';
            $allowed = ['in_progress', 'on_hold', 'resolved', 'reopened'];
            if (!in_array($newStatus, $allowed, true)) {
                flash('error', 'Invalid status.');
            } else {
                if ($newStatus === 'resolved') {
                    $pdo->prepare(
                        'UPDATE complaints SET status = ?, resolved_at = NOW() WHERE complaint_id = ?'
                    )->execute([$newStatus, $complaint_id]);
                } else {
                    $pdo->prepare(
                        'UPDATE complaints SET status = ?, resolved_at = NULL WHERE complaint_id = ?'
                    )->execute([$newStatus, $complaint_id]);
                }
                flash('success', 'Status updated to ' . str_replace('_', ' ', $newStatus) . '.');
            }
        }
    } catch (Throwable $e) {
        flash('error', APP_DEBUG ? $e->getMessage() : 'Action failed. Please try again.');
    }

    redirect('staff/complaints/view.php?id=' . $complaint_id);
}

$messages = load_complaint_messages($complaint_id, true); // include internal notes for staff

$page_title = $complaint['reference_no'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= e(url('staff/complaints/list.php')) ?>" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Back to queue
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
                        <div class="text-muted">Student</div>
                        <div class="fw-semibold"><?= e($complaint['student_name']) ?></div>
                        <small class="text-muted"><?= e($complaint['student_email']) ?></small>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Category</div>
                        <div class="fw-semibold"><?= e($complaint['category']) ?></div>
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
                    <a href="<?= e(url($complaint['attachment_path'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-paperclip me-1"></i> <?= e(basename($complaint['attachment_path'])) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Messages -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-chat-dots me-2"></i>Communication</h5>

                <?php if (empty($messages)): ?>
                    <p class="text-muted small mb-3">No messages yet.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($messages as $m): ?>
                            <?php
                            $isStaff   = $m['sender_type'] === 'staff';
                            $isNote    = (int) $m['is_internal_note'] === 1;
                            $alignEnd  = $isStaff;
                            $bg        = $isNote ? '#fff7ed' : ($isStaff ? 'var(--grad-primary)' : 'var(--bg-soft)');
                            $color     = $isNote ? '#9a3412' : ($isStaff ? '#fff' : 'var(--text-900)');
                            $border    = $isNote ? '1.5px dashed #fdba74' : '0';
                            ?>
                            <div class="d-flex <?= $alignEnd ? 'justify-content-end' : '' ?>">
                                <div style="max-width:80%;">
                                    <div class="small text-muted mb-1">
                                        <strong><?= e(ucfirst($m['sender_type'])) ?></strong>
                                        <?php if ($isNote): ?>
                                            <span class="badge bg-warning text-dark ms-1">Internal note</span>
                                        <?php endif; ?>
                                        · <?= e(time_ago($m['created_at'])) ?>
                                    </div>
                                    <div class="p-3" style="
                                        background:<?= $bg ?>;
                                        color:<?= $color ?>;
                                        border:<?= $border ?>;
                                        border-radius:1rem;
                                        white-space:pre-wrap;">
                                        <?= e($m['message']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#reply-tab"><i class="bi bi-reply me-1"></i>Reply to Student</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#note-tab"><i class="bi bi-journal-text me-1"></i>Internal Note</a></li>
                </ul>
                <div class="tab-content border border-top-0 rounded-bottom p-3">
                    <div class="tab-pane fade show active" id="reply-tab">
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="message">
                            <div class="input-group">
                                <textarea name="message" class="form-control" rows="2"
                                          placeholder="Write a reply visible to the student…" required></textarea>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-send me-1"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="note-tab">
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="note">
                            <div class="input-group">
                                <textarea name="message" class="form-control" rows="2"
                                          placeholder="Internal note — only staff and admins can see this…" required></textarea>
                                <button class="btn btn-warning" type="submit">
                                    <i class="bi bi-journal-plus me-1"></i> Add Note
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick action: accept -->
        <?php if ($complaint['status'] === 'pending'): ?>
            <div class="card border-0 shadow-sm mb-3" style="background:var(--grad-soft);">
                <div class="card-body">
                    <h6><i class="bi bi-check2-circle me-1"></i>Accept this complaint</h6>
                    <p class="small text-muted mb-3">Take ownership and move it to <em>In Progress</em>.</p>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="accept">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bi bi-hand-thumbs-up me-1"></i> Accept &amp; Start
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Status update -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-arrow-clockwise me-1"></i>Update Status</h6>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="status">
                    <select name="status" class="form-select form-select-sm mb-2" required>
                        <option value="">Select new status</option>
                        <option value="in_progress" <?= $complaint['status'] === 'in_progress' ? 'disabled' : '' ?>>In Progress</option>
                        <option value="on_hold"     <?= $complaint['status'] === 'on_hold'     ? 'disabled' : '' ?>>On Hold</option>
                        <option value="resolved"    <?= $complaint['status'] === 'resolved'    ? 'disabled' : '' ?>>Resolved</option>
                        <option value="reopened"    <?= $complaint['status'] === 'reopened'    ? 'disabled' : '' ?>>Reopened</option>
                    </select>
                    <button class="btn btn-primary btn-sm w-100" type="submit"
                            data-confirm="Update the status of this complaint?">
                        <i class="bi bi-check2 me-1"></i> Apply
                    </button>
                </form>
            </div>
        </div>

        <!-- Meta -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-3" style="letter-spacing:0.05em;">Details</h6>
                <div class="small mb-2">
                    <div class="text-muted">Department</div>
                    <div class="fw-semibold"><?= e($complaint['department_name'] ?? '—') ?></div>
                </div>
                <div class="small mb-2">
                    <div class="text-muted">Assigned to</div>
                    <div class="fw-semibold"><?= e($complaint['staff_name'] ?? 'Unassigned') ?></div>
                </div>
                <?php if ($complaint['resolved_at']): ?>
                    <div class="small mb-2">
                        <div class="text-muted">Resolved on</div>
                        <div class="fw-semibold"><?= e(date('d M Y, h:i A', strtotime($complaint['resolved_at']))) ?></div>
                    </div>
                <?php endif; ?>
                <div class="small">
                    <div class="text-muted">Last updated</div>
                    <div class="fw-semibold"><?= e(time_ago($complaint['updated_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
