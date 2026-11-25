<?php
// AJAX endpoint: admin changes a feedback's status instantly. Returns JSON.
require 'db_connect.php';
require 'includes/auth.php';
header('Content-Type: application/json');

function out($code, $data) { http_response_code($code); echo json_encode($data); exit; }

if (!current_user()) out(401, ['ok' => false, 'error' => 'Please log in.']);
if (!is_admin()) out(403, ['ok' => false, 'error' => 'Only admins can change status.']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, ['ok' => false, 'error' => 'POST only.']);
if (!csrf_check($_POST['csrf'] ?? '')) out(400, ['ok' => false, 'error' => 'Invalid session token. Reload the page.']);

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
if ($id < 1 || !in_array($status, STATUSES, true)) out(422, ['ok' => false, 'error' => 'Invalid data.']);

$s = $conn->prepare("UPDATE feedback SET status = ? WHERE id = ?");
$s->bind_param('si', $status, $id);
$s->execute();
out(200, ['ok' => true, 'status' => $status]);
