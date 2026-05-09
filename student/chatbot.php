<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('student');
$user = current_user();

$page_title = 'AI Assistant';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= e(url('student/dashboard.php')) ?>" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Back to dashboard
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="height:75vh;">
            <div class="card-header bg-white border-0 d-flex align-items-center gap-2 py-3">
                <div class="brand-mark"><i class="bi bi-robot text-white"></i></div>
                <div>
                    <h5 class="mb-0">AI Assistant</h5>
                    <small class="text-success"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Online · Always free</small>
                </div>
            </div>

            <div class="card-body d-flex flex-column" style="overflow:hidden;">
                <div id="chatLog" class="flex-grow-1 overflow-auto pe-2 mb-3" style="scroll-behavior:smooth;">
                    <div class="d-flex mb-3">
                        <div class="brand-mark me-2 flex-shrink-0" style="width:32px;height:32px;font-size:0.85rem;">
                            <i class="bi bi-robot text-white"></i>
                        </div>
                        <div style="max-width:85%;">
                            <div class="p-3" style="background:var(--bg-soft);border-radius:1rem;border-bottom-left-radius:0.25rem;">
                                Hi <strong><?= e($user['name'] ?: 'there') ?></strong>! I'm your campus AI assistant. Ask me about
                                library hours, exam schedules, hostel allocation, fees, or anything else.
                            </div>
                            <small class="text-muted ms-2">Just now</small>
                        </div>
                    </div>
                </div>

                <form id="chatForm" class="d-flex gap-2">
                    <input type="hidden" id="csrfToken" value="<?= e(csrf_token()) ?>">
                    <input type="text" id="chatInput" class="form-control" required maxlength="500"
                           placeholder="Type your question…" autocomplete="off">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-send"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3" style="background:var(--grad-soft);">
            <div class="card-body">
                <h6><i class="bi bi-lightbulb-fill text-warning me-1"></i>Try asking</h6>
                <div class="d-flex flex-column gap-2 mt-2">
                    <button class="btn btn-sm btn-outline-primary text-start chat-suggestion">What are the library timings?</button>
                    <button class="btn btn-sm btn-outline-primary text-start chat-suggestion">When is the exam date sheet?</button>
                    <button class="btn btn-sm btn-outline-primary text-start chat-suggestion">How do I apply for a hostel room?</button>
                    <button class="btn btn-sm btn-outline-primary text-start chat-suggestion">Where can I see the fee structure?</button>
                    <button class="btn btn-sm btn-outline-primary text-start chat-suggestion">How do I get the campus Wi-Fi password?</button>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6><i class="bi bi-info-circle me-1"></i>Can't find an answer?</h6>
                <p class="small text-muted mb-2">
                    For complex or sensitive issues, file a formal complaint and we'll route it to the
                    right department.
                </p>
                <a href="<?= e(url('student/complaints/new.php')) ?>" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-pencil-square me-1"></i> Submit a Complaint
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const log   = document.getElementById('chatLog');
    const form  = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const apiUrl = <?= json_encode(url('chatbot/api.php')) ?>;
    const escalateUrl = <?= json_encode(url('student/complaints/new.php')) ?>;

    function bubble(side, html, label) {
        const wrap = document.createElement('div');
        wrap.className = 'd-flex mb-3 ' + (side === 'user' ? 'justify-content-end' : '');
        if (side === 'user') {
            wrap.innerHTML = `
                <div style="max-width:85%;">
                    <div class="p-3 text-white" style="background:var(--grad-primary);border-radius:1rem;border-bottom-right-radius:0.25rem;">${html}</div>
                    <small class="text-muted text-end d-block me-2">${label}</small>
                </div>`;
        } else {
            wrap.innerHTML = `
                <div class="brand-mark me-2 flex-shrink-0" style="width:32px;height:32px;font-size:0.85rem;">
                    <i class="bi bi-robot text-white"></i>
                </div>
                <div style="max-width:85%;">
                    <div class="p-3" style="background:var(--bg-soft);border-radius:1rem;border-bottom-left-radius:0.25rem;">${html}</div>
                    <small class="text-muted ms-2">${label}</small>
                </div>`;
        }
        log.appendChild(wrap);
        log.scrollTop = log.scrollHeight;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function typingIndicator() {
        const id = 'typing-' + Date.now();
        const wrap = document.createElement('div');
        wrap.id = id;
        wrap.className = 'd-flex mb-3';
        wrap.innerHTML = `
            <div class="brand-mark me-2 flex-shrink-0" style="width:32px;height:32px;font-size:0.85rem;">
                <i class="bi bi-robot text-white"></i>
            </div>
            <div class="p-3 d-flex gap-1" style="background:var(--bg-soft);border-radius:1rem;border-bottom-left-radius:0.25rem;">
                <span class="spinner-grow spinner-grow-sm text-secondary"></span>
                <span class="spinner-grow spinner-grow-sm text-secondary" style="animation-delay:0.15s;"></span>
                <span class="spinner-grow spinner-grow-sm text-secondary" style="animation-delay:0.3s;"></span>
            </div>`;
        log.appendChild(wrap);
        log.scrollTop = log.scrollHeight;
        return id;
    }

    async function send(query) {
        bubble('user', escapeHtml(query), 'You · just now');
        const typingId = typingIndicator();

        try {
            const res = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query }),
            });
            const data = await res.json();
            document.getElementById(typingId)?.remove();

            let html = escapeHtml(data.answer || '');
            if (data.should_escalate) {
                html += `<div class="mt-3"><a href="${escalateUrl}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil-square me-1"></i>Submit a Complaint</a></div>`;
            }
            bubble('bot', html, data.intent ? data.intent : 'AI · just now');
        } catch (err) {
            document.getElementById(typingId)?.remove();
            bubble('bot', 'Sorry, the AI service seems to be unavailable. Please try again later.', 'Error');
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        send(text);
    });

    document.querySelectorAll('.chat-suggestion').forEach(btn => {
        btn.addEventListener('click', () => {
            input.value = btn.textContent.trim();
            form.requestSubmit();
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
