<?php
const CATEGORIES = ['General', 'Bug Report', 'Suggestion', 'Compliment', 'Complaint'];
const STATUSES   = ['New', 'Reviewed', 'Resolved'];

// Escape output to prevent XSS
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Validate and sanitize feedback form input. Returns [data, errors]
function clean_feedback($p) {
    $d = [
        'name'     => trim($p['name'] ?? ''),
        'email'    => trim($p['email'] ?? ''),
        'category' => $p['category'] ?? 'General',
        'rating'   => (int)($p['rating'] ?? 0),
        'message'  => trim($p['message'] ?? ''),
        'status'   => $p['status'] ?? 'New',
    ];
    $err = [];
    if ($d['name'] === '') $err[] = 'Name is required.';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'A valid email is required.';
    if (!in_array($d['category'], CATEGORIES, true)) $err[] = 'Invalid category.';
    if ($d['rating'] < 1 || $d['rating'] > 5) $err[] = 'Rating must be 1 to 5.';
    if (strlen($d['message']) < 5) $err[] = 'Message must be at least 5 characters.';
    if (!in_array($d['status'], STATUSES, true)) $d['status'] = 'New';
    return [$d, $err];
}

function stars($n) { return str_repeat('★', (int)$n) . str_repeat('☆', 5 - (int)$n); }
