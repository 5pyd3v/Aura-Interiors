<?php
/**
 * Lightweight analytics beacon: WhatsApp clicks, call clicks, project views, etc.
 * Fire-and-forget — never throws, always returns fast.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$allowedTypes = ['page_view', 'project_view', 'whatsapp_click', 'call_click', 'inquiry_submit'];
$type = $_POST['type'] ?? '';
$reference = isset($_POST['reference']) ? mb_substr((string) $_POST['reference'], 0, 190) : null;

if (in_array($type, $allowedTypes, true)) {
    log_event($type, $reference ?: null);
}

echo json_encode(['ok' => true]);
