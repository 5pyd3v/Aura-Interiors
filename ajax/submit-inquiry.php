<?php
/**
 * Handles the consultation / quote form submission (AJAX, JSON response).
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    json_out(['success' => false, 'message' => 'Your session expired — please refresh the page and try again.'], 419);
}

// Simple honeypot spam trap (hidden field 'website' if present in future markup).
if (!empty($_POST['website'])) {
    json_out(['success' => true, 'message' => 'Thank you! Our team will contact you shortly.']);
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$phone    = trim((string) ($_POST['phone'] ?? ''));
$whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
$email    = trim((string) ($_POST['email'] ?? ''));
$city     = trim((string) ($_POST['city'] ?? ''));
$projectType   = trim((string) ($_POST['project_type'] ?? ''));
$propertyType  = trim((string) ($_POST['property_type'] ?? ''));
$area     = trim((string) ($_POST['area'] ?? ''));
$budget   = trim((string) ($_POST['budget'] ?? ''));
$startDate = trim((string) ($_POST['start_date'] ?? ''));
$message  = trim((string) ($_POST['message'] ?? ''));
$source   = trim((string) ($_POST['source'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));

$errors = [];
if ($fullName === '' || mb_strlen($fullName) < 2) {
    $errors['full_name'] = 'Please enter your full name.';
}
if ($phone === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($errors) {
    json_out(['success' => false, 'message' => 'Please check the highlighted fields.', 'errors' => $errors], 422);
}

$validDate = null;
if ($startDate !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $startDate);
    if ($d) {
        $validDate = $d->format('Y-m-d');
    }
}

try {
    $stmt = db()->prepare(
        'INSERT INTO inquiries (full_name, phone, whatsapp, email, city, project_type, property_type, area, budget, start_date, message, status, source_page, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "NEW", ?, ?)'
    );
    $stmt->execute([
        $fullName, $phone, $whatsapp ?: null, $email ?: null, $city ?: null,
        $projectType ?: null, $propertyType ?: null, $area ?: null, $budget ?: null,
        $validDate, $message ?: null,
        mb_substr($source, 0, 190),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
} catch (Throwable $e) {
    json_out(['success' => false, 'message' => 'We could not save your request right now. Please WhatsApp us directly.'], 500);
}

json_out([
    'success' => true,
    'message' => 'Thank you, ' . htmlspecialchars(explode(' ', $fullName)[0]) . '! Our team will contact you shortly to discuss your project.',
]);
