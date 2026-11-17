<?php
// CREATE: insert new feedback
require 'db_connect.php';
$title = 'Submit Feedback';
$f = ['name' => '', 'email' => '', 'category' => 'General', 'rating' => 5, 'message' => '', 'status' => 'New'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$f, $errors] = clean_feedback($_POST);
    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, category, rating, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssis', $f['name'], $f['email'], $f['category'], $f['rating'], $f['message']);
        $stmt->execute();
        header('Location: index.php?msg=added');
        exit;
    }
}
$isEdit = false;
include 'includes/header.php';
?>
<h2 class="mb-3">Submit Feedback</h2>
<?php include 'includes/form.php'; ?>
<?php include 'includes/footer.php'; ?>
