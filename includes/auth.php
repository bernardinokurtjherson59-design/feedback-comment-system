<?php
/*
 * File: includes/auth.php
 * Purpose: Start sessions and provide authentication, authorization, and CSRF helpers.
 * Access: Included by guest, user, and admin pages.
 * Tables: None.
 */
// Start a session once so authentication state and CSRF tokens are available.
if (session_status() === PHP_SESSION_NONE) session_start();

// Return the signed-in user's session data or null when no user is authenticated.
// Parameters: none; returns an account array or null.
function current_user() { return $_SESSION['user'] ?? null; }

// Check whether the current account has the administrator role.
// Parameters: none; returns bool.
function is_admin() { return (current_user()['role'] ?? '') === 'admin'; }

// Redirect guests to login and stop the current request.
// Parameters: none; returns void by exiting when access is not authenticated.
function require_login() {
    if (!current_user()) { header('Location: login.php'); exit; }
}

// SECURITY: Compare a row's owner ID to the signed-in user's ID.
// Parameters: array $row; returns bool.
function is_owner($row) {
    $u = current_user();
    return $u && (int)$row['user_id'] === (int)$u['id'];
}

// SECURITY: Allow administrators to delete any row and users to delete their own.
// Parameters: array $row; returns bool.
function can_delete($row) { return is_admin() || is_owner($row); }

// SECURITY: Allow only a regular user to edit a row they own.
// Parameters: array $row; returns bool.
function can_edit($row) { return !is_admin() && is_owner($row); }

// SECURITY: Create and retain a random CSRF token for the current session.
// Parameters: none; returns the token string.
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
// SECURITY: Build the hidden form field containing the escaped CSRF token.
// Parameters: none; returns an HTML input element string.
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
// SECURITY: Compare a submitted CSRF token safely with the current session token.
// Parameters: mixed $t; returns bool.
function csrf_check($t) {
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

// Deny the request, render an escaped explanation, and stop processing.
// Parameters: string $msg; returns void by exiting the request.
function deny($msg = 'You do not have permission to do that.') {
    http_response_code(403);
    $title = 'Access denied';
    include __DIR__ . '/header.php';
    echo '<div class="alert alert-danger">' . e($msg) . '</div><a href="index.php" class="btn btn-secondary">Back</a>';
    include __DIR__ . '/footer.php';
    exit;
}
