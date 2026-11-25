<?php
const CATEGORIES = ['General', 'Bug Report', 'Suggestion', 'Compliment', 'Complaint'];
const STATUSES   = ['New', 'Reviewed', 'Resolved'];

// Escape output to prevent XSS
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Validate feedback form input (the author comes from the login session, not the form)
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

function stars($n) { return str_repeat('★', (int)$n) . str_repeat('☆', 5 - (int)$n); }

function role_badge($role) {
    return $role === 'admin' ? '<span class="badge text-bg-primary">Admin</span>' : '<span class="badge text-bg-secondary">User</span>';
}

function status_badge($status) {
    $c = ['New' => 'primary', 'Reviewed' => 'secondary', 'Resolved' => 'success'];
    return '<span class="badge text-bg-' . ($c[$status] ?? 'secondary') . '">' . e($status) . '</span>';
}
