<?php
/*
 * File: delete.php
 * Purpose: Delete feedback when the requester has permission.
 * Access: Logged-in users may delete their own feedback; admins may delete any.
 * Tables: feedback; related comments are removed by the database foreign key.
 */
// CRUD: DELETE feedback.
require 'db_connect.php';
require 'includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    // Load the owner before checking whether this requester may delete the record.
    // SECURITY: Permission is checked on the server for every delete request.
    $stmt = $conn->prepare("SELECT id, user_id FROM feedback WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        if (!can_delete($row)) deny('You can only delete your own feedback.');
        // The foreign key removes comments belonging to this feedback.
        $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
header('Location: index.php?msg=deleted');
exit;
