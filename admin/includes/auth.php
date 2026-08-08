<?php
/**
 * Include at the very top of every protected admin page (after bootstrap).
 * Redirects to the login screen if there's no valid admin session.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

if (empty($_SESSION['admin_id'])) {
    redirect('index.php');
}

$currentAdmin = [
    'id'    => (int) $_SESSION['admin_id'],
    'name'  => $_SESSION['admin_name'] ?? 'Admin',
    'role'  => $_SESSION['admin_role'] ?? 'editor',
];

/** Convenience wrapper: verify CSRF on admin POST requests, redirect back with a flash on failure. */
function admin_verify_csrf(string $redirectTo): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !csrf_verify($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Your session expired. Please try again.');
        redirect($redirectTo);
    }
}
