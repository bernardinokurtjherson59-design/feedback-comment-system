<?php
// DELETE: remove feedback (comments are removed automatically via ON DELETE CASCADE)
require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
header('Location: index.php?msg=deleted');
exit;
