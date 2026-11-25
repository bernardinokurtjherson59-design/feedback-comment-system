<?php
/*
 * File: includes/helpers.php
 * Purpose: Share allowed feedback values, validation, and presentation helpers.
 * Access: Included by guest, user, and admin pages through db_connect.php.
 * Tables: None.
 */
const CATEGORIES = ['General', 'Bug Report', 'Suggestion', 'Compliment', 'Complaint'];
const STATUSES   = ['New', 'Reviewed', 'Resolved'];

// SECURITY: Escape dynamic text before placing it in HTML output.
// Parameters: mixed $s; returns an HTML-escaped string.
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Validate feedback fields and return normalized values with any validation errors.
// Parameters: array $p; returns [array $data, array $errors].
function clean_feedback($p) {
    $d = [
        'category' => $p['category'] ?? 'General',
        'rating'   => (int)($p['rating'] ?? 0),
        'message'  => trim($p['message'] ?? ''),
    ];
    $err = [];
    if (!in_array($d['category'], CATEGORIES, true)) $err[] = 'Invalid category.';
    if ($d['rating'] < 1 || $d['rating'] > 5) $err[] = 'Rating must be 1 to 5.';
    if (strlen($d['message']) < 5) $err[] = 'Message must be at least 5 characters.';
    return [$d, $err];
}

// Format a rating as filled and empty star characters.
// Parameters: int|string $n; returns a string of five stars.
function stars($n) { return str_repeat('★', (int)$n) . str_repeat('☆', 5 - (int)$n); }

// Return a Bootstrap badge for the supplied account role.
// Parameters: string $role; returns an HTML badge string.
function role_badge($role) {
    return $role === 'admin' ? '<span class="badge text-bg-primary">Admin</span>' : '<span class="badge text-bg-secondary">User</span>';
}

// Return a Bootstrap badge with a color mapped to the feedback status.
// Parameters: string $status; returns an escaped HTML badge string.
function status_badge($status) {
    $c = ['New' => 'primary', 'Reviewed' => 'secondary', 'Resolved' => 'success'];
    return '<span class="badge text-bg-' . ($c[$status] ?? 'secondary') . '">' . e($status) . '</span>';
}
