<?php
/*
 * File: add.php
 * Purpose: Validate and create feedback submitted by a regular user.
 * Access: Logged-in users; admins are denied.
 * Tables: feedback.
 */
// CRUD: CREATE feedback.
require 'db_connect.php';
require 'includes/auth.php';
require_login();
// Keep the administrator role read-only for feedback submission.
if (is_admin()) deny('Admins cannot submit feedback.');

$title = 'Submit Feedback';
$f = ['category' => 'General', 'rating' => 5, 'message' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate submitted fields before saving the feedback.
    [$f, $errors] = clean_feedback($_POST);
    if (!$errors) {
        // SECURITY: Derive ownership from the authenticated session, not submitted data.
        $uid = current_user()['id'];
        // SECURITY: Use a prepared statement for all user-provided feedback values.
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, category, rating, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isis', $uid, $f['category'], $f['rating'], $f['message']);
        $stmt->execute();
        header('Location: index.php?msg=added');
        exit;
    }
}
$isEdit = false;
// Render the shared form and page shell after validation is complete.
include 'includes/header.php';
?>
<h2 class="mb-3">Submit Feedback</h2>
<?php include 'includes/form.php'; ?>
<?php include 'includes/footer.php'; ?>
