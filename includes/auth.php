<?php
// Authentication + authorization helpers (sessions, roles, ownership checks, CSRF)
if (session_status() === PHP_SESSION_NONE) session_start();

// Returns the logged-in user array (id, full_name, username, role) or null
function current_user() { return $_SESSION['user'] ?? null; }

function is_admin() { return (current_user()['role'] ?? '') === 'admin'; }

// Send guests to the login page
function require_login() {
    if (!current_user()) { header('Location: login.php'); exit; }
}

// Does this feedback/comment row belong to the logged-in user?
function is_owner($row) {
    $u = current_user();
    return $u && (int)$row['user_id'] === (int)$u['id'];
}

// Admin may delete anything; a user may delete only their own
function can_delete($row) { return is_admin() || is_owner($row); }

// Only the owner (a regular user) edits the message. Admin changes status from the list.
function can_edit($row) { return !is_admin() && is_owner($row); }

// CSRF protection: a secret token stored in the session must come back with every sensitive form
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check($t) {
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

// Stop the request with a 403 page
function deny($msg = 'You do not have permission to do that.') {
    http_response_code(403);
    $title = 'Access denied';
    include __DIR__ . '/header.php';
    echo '<div class="alert alert-danger">' . e($msg) . '</div><a href="index.php" class="btn btn-secondary">Back</a>';
    include __DIR__ . '/footer.php';
    exit;
}
