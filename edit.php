<?php
// UPDATE: edit existing feedback
require 'db_connect.php';
$title = 'Edit Feedback';
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM feedback WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$f = $stmt->get_result()->fetch_assoc();
if (!$f) { header('Location: index.php'); exit; }
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$f, $errors] = clean_feedback($_POST);
    if (!$errors) {
        $stmt = $conn->prepare("UPDATE feedback SET name=?, email=?, category=?, rating=?, message=?, status=? WHERE id=?");
        $stmt->bind_param('sssissi', $f['name'], $f['email'], $f['category'], $f['rating'], $f['message'], $f['status'], $id);
        $stmt->execute();
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
