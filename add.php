<?php
// CREATE: users submit feedback (admins cannot)
require 'db_connect.php';
require 'includes/auth.php';
require_login();
if (is_admin()) deny('Admins cannot submit feedback.');

$title = 'Submit Feedback';
$f = ['category' => 'General', 'rating' => 5, 'message' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$f, $errors] = clean_feedback($_POST);
    if (!$errors) {
        $uid = current_user()['id']; // owner comes from the session, never from the form
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, category, rating, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isis', $uid, $f['category'], $f['rating'], $f['message']);
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
