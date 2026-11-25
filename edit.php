<?php
// UPDATE: a user edits the content of their OWN feedback. (Admins change status from the list.)
require 'db_connect.php';
require 'includes/auth.php';
require_login();
$title = 'Edit Feedback';
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM feedback WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$f = $stmt->get_result()->fetch_assoc();
if (!$f) { header('Location: index.php'); exit; }
if (!can_edit($f)) deny('You can only edit your own feedback.'); // server-side permission check

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = clean_feedback($_POST);
    $f = array_merge($f, $d);
    if (!$errors) {
        $uid = current_user()['id'];
        // AND user_id = ? is a second safety net: only the owner's row can change
        $s = $conn->prepare("UPDATE feedback SET category = ?, rating = ?, message = ? WHERE id = ? AND user_id = ?");
        $s->bind_param('sisii', $f['category'], $f['rating'], $f['message'], $id, $uid);
        $s->execute();
        header('Location: index.php?msg=updated');
        exit;
    }
}
$isEdit = true;
include 'includes/header.php';
?>
<h2 class="mb-3">Edit Feedback #<?= $id ?></h2>
<?php include 'includes/form.php'; ?>
<?php include 'includes/footer.php'; ?>
