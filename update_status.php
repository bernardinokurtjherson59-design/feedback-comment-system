<?php
/*
 * File: update_status.php
 * Purpose: Validate and save an administrator's AJAX feedback status change.
 * Access: Admins only.
 * Tables: feedback.
 */
// CRUD: UPDATE feedback status.
require 'db_connect.php';
require 'includes/auth.php';
header('Content-Type: application/json');

// Return a JSON response and stop processing the request.
// Parameters: int $code and array $data; returns void by exiting the request.
function out($code, $data) { http_response_code($code); echo json_encode($data); exit; }

// SECURITY: Require a signed-in administrator and valid same-session CSRF token.
if (!current_user()) out(401, ['ok' => false, 'error' => 'Please log in.']);
if (!is_admin()) out(403, ['ok' => false, 'error' => 'Only admins can change status.']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, ['ok' => false, 'error' => 'POST only.']);
if (!csrf_check($_POST['csrf'] ?? '')) out(400, ['ok' => false, 'error' => 'Invalid session token. Reload the page.']);

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
if ($id < 1 || !in_array($status, STATUSES, true)) out(422, ['ok' => false, 'error' => 'Invalid data.']);

// SECURITY: Bind validated values instead of building SQL from request data.
$s = $conn->prepare("UPDATE feedback SET status = ? WHERE id = ?");
$s->bind_param('si', $status, $id);
$s->execute();
out(200, ['ok' => true, 'status' => $status]);
