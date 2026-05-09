<?php
/**
 * AI Chatbot matching API.
 *
 * Receives a JSON or form-encoded query, scores against the FAQs knowledge base,
 * returns the best match (or an escalation suggestion), and logs the interaction.
 *
 * POST  query=<text>
 * Returns: { match: bool, intent: string|null, answer: string, faq_id: int|null,
 *            should_escalate: bool, suggestion: string }
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

start_secure_session();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Accept either JSON body or form-encoded
$raw   = file_get_contents('php://input');
$body  = json_decode($raw ?: 'null', true);
$query = trim((string) ($body['query'] ?? $_POST['query'] ?? ''));

if ($query === '') {
    echo json_encode(['error' => 'Empty query']);
    exit;
}
if (mb_strlen($query) > 500) {
    $query = mb_substr($query, 0, 500);
}

$user      = current_user();
$studentId = ($user && $user['role'] === 'student') ? (int) $user['id'] : null;

try {
    $faqs = db()->query('SELECT * FROM faqs WHERE is_active = 1')->fetchAll();
} catch (Throwable $e) {
    echo json_encode(['error' => 'Knowledge base unavailable.']);
    exit;
}

// --- Score each FAQ by keyword overlap ---
$qLower = mb_strtolower($query);
$best   = null;
$bestScore = 0;

foreach ($faqs as $faq) {
    $keywords = array_filter(array_map('trim', explode(',', mb_strtolower($faq['keywords']))));
    $score = 0;
    foreach ($keywords as $kw) {
        if ($kw !== '' && mb_strpos($qLower, $kw) !== false) {
            // Longer keyword matches count more (more specific)
            $score += 1 + (mb_strlen($kw) >= 5 ? 1 : 0);
        }
    }
    if ($score > $bestScore) {
        $bestScore = $score;
        $best = $faq;
    }
}

// --- Build response ---
if ($best && $bestScore > 0) {
    $response = [
        'match'           => true,
        'faq_id'          => (int) $best['faq_id'],
        'intent'          => $best['category'],
        'answer'          => $best['answer'],
        'should_escalate' => false,
        'suggestion'      => 'Did this help? If not, you can submit a formal complaint.',
    ];

    try {
        db()->prepare('UPDATE faqs SET hit_count = hit_count + 1 WHERE faq_id = ?')
            ->execute([$best['faq_id']]);
    } catch (Throwable $e) { /* ignore */ }
} else {
    $response = [
        'match'           => false,
        'faq_id'          => null,
        'intent'          => null,
        'answer'          => "I'm sorry — I couldn't find an answer to that. For complex or sensitive issues, the best path is to file a formal complaint so the right department can help you directly.",
        'should_escalate' => true,
        'suggestion'      => 'Submit a formal complaint',
    ];
}

// --- Log the interaction ---
try {
    db()->prepare(
        'INSERT INTO chatbot_logs (student_id, query_text, response_text, intent, was_escalated)
         VALUES (?, ?, ?, ?, ?)'
    )->execute([
        $studentId,
        $query,
        $response['answer'],
        $response['intent'],
        $response['should_escalate'] ? 1 : 0,
    ]);
} catch (Throwable $e) { /* ignore */ }

echo json_encode($response);
