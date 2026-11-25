<?php
// DELETE: admin can delete any feedback; a user only their own (comments go with it via ON DELETE CASCADE)
require 'db_connect.php';
require 'includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("SELECT id, user_id FROM feedback WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        if (!can_delete($row)) deny('You can only delete your own feedback.');
        $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
header('Location: index.php?msg=deleted');
exit;
