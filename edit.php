<?php
/*
 * File: edit.php
 * Purpose: Let the owner update the category, rating, or message of their feedback.
 * Access: Logged-in feedback owners; admins and other users are denied.
 * Tables: feedback.
 */
// CRUD: UPDATE feedback content.
require 'db_connect.php';
require 'includes/auth.php';
require_login();
$title = 'Edit Feedback';
$id = (int)($_GET['id'] ?? 0);

// Load the selected feedback before showing an edit form.
// SECURITY: Bind the requested ID in the prepared statement.
$stmt = $conn->prepare("SELECT * FROM feedback WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$f = $stmt->get_result()->fetch_assoc();
if (!$f) { header('Location: index.php'); exit; }
// SECURITY: Enforce ownership on the server for every edit request.
if (!can_edit($f)) deny('You can only edit your own feedback.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the changed fields before saving them.
    [$d, $errors] = clean_feedback($_POST);
    $f = array_merge($f, $d);
    if (!$errors) {
        $uid = current_user()['id'];
        // SECURITY: Recheck ownership in the UPDATE condition as a second safeguard.
        $s = $conn->prepare("UPDATE feedback SET category = ?, rating = ?, message = ? WHERE id = ? AND user_id = ?");
        $s->bind_param('sisii', $f['category'], $f['rating'], $f['message'], $id, $uid);
        $s->execute();
        header('Location: index.php?msg=updated');
        exit;
    }
}
$isEdit = true;
// Render the shared edit form and page shell.
include 'includes/header.php';
?>
<h2 class="mb-3">Edit Feedback #<?= $id ?></h2>
<?php include 'includes/form.php'; ?>
<?php include 'includes/footer.php'; ?>
