<?php
/*
 * File: view.php
 * Purpose: Display one feedback item and its comments; add comments or remove allowed comments.
 * Access: Logged-in users and admins.
 * Tables: feedback, users, comments.
 */
// CRUD: READ feedback/comments, CREATE comments, and DELETE permitted comments.
require 'db_connect.php';
require 'includes/auth.php';
require_login();
$title = 'View Feedback';
// Validate the record identifier before using it in a query.
$id = (int)($_GET['id'] ?? 0);
$me = current_user();

// Load the selected feedback and its author for the detail view.
// SECURITY: Bind the requested ID in the prepared statement.
$stmt = $conn->prepare("SELECT f.*, u.full_name FROM feedback f JOIN users u ON u.id = f.user_id WHERE f.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$f = $stmt->get_result()->fetch_assoc();
if (!$f) { header('Location: index.php'); exit; }

// Process comment creation and deletion, then redirect after successful POST requests.
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_comment') {
        // Reject empty comments before inserting them.
        $comment = trim($_POST['comment'] ?? '');
        if ($comment === '') {
            $error = 'Comment cannot be empty.';
        } else {
            // SECURITY: Use a prepared statement and take the comment owner from the session.
            $s = $conn->prepare("INSERT INTO comments (feedback_id, user_id, comment) VALUES (?, ?, ?)");
            $s->bind_param('iis', $id, $me['id'], $comment);
            $s->execute();
            header("Location: view.php?id=$id");
            exit;
        }
    } elseif ($action === 'delete_comment') {
        $cid = (int)($_POST['comment_id'] ?? 0);
        // Fetch the comment's owner before applying the server-side delete permission check.
        // SECURITY: The permission check is enforced here, not only by hiding the delete form.
        $s = $conn->prepare("SELECT id, user_id FROM comments WHERE id = ? AND feedback_id = ?");
        $s->bind_param('ii', $cid, $id);
        $s->execute();
        $row = $s->get_result()->fetch_assoc();
        if ($row) {
            if (!can_delete($row)) deny('You can only delete your own comments.');
            // Delete only the selected comment after confirming access.
            $s = $conn->prepare("DELETE FROM comments WHERE id = ?");
            $s->bind_param('i', $cid);
            $s->execute();
        }
        header("Location: view.php?id=$id");
        exit;
    }
}

// Load comments in chronological order for the thread.
$s = $conn->prepare("SELECT c.*, u.full_name, u.role FROM comments c JOIN users u ON u.id = c.user_id WHERE c.feedback_id = ? ORDER BY c.created_at ASC");
$s->bind_param('i', $id);
$s->execute();
$comments = $s->get_result();

// Render escaped feedback and comment text with available actions.
include 'includes/header.php';
?>
<a href="index.php" class="btn btn-sm btn-outline-secondary mb-3">&larr; Back</a>
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between flex-wrap">
      <h4><?= e($f['category']) ?> <small class="stars"><?= stars($f['rating']) ?></small></h4>
      <span class="align-self-start"><?= status_badge($f['status']) ?></span>
    </div>
    <p class="text-muted mb-2">by <?= e($f['full_name']) ?> &middot; <?= e($f['created_at']) ?></p>
    <p class="mb-0"><?= nl2br(e($f['message'])) ?></p>
  </div>
</div>

<h5>Comments (<?= $comments->num_rows ?>)</h5>
<?php while ($c = $comments->fetch_assoc()): ?>
  <div class="card mb-2"><div class="card-body py-2 d-flex justify-content-between">
    <div>
      <strong><?= e($c['full_name']) ?></strong> <?= role_badge($c['role']) ?>
      <small class="text-muted"><?= e($c['created_at']) ?></small><br>
      <?= nl2br(e($c['comment'])) ?>
    </div>
    <?php if (can_delete($c)): ?>
    <form method="post" onsubmit="return confirm('Delete this comment?');">
      <input type="hidden" name="action" value="delete_comment">
      <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
      <button class="btn btn-sm btn-outline-danger">Delete</button>
    </form>
    <?php endif; ?>
  </div></div>
<?php endwhile; ?>

<form method="post" class="card card-body shadow-sm mt-3">
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
  <input type="hidden" name="action" value="add_comment">
  <label class="form-label">Comment as <strong><?= e($me['full_name']) ?></strong></label>
  <textarea name="comment" class="form-control mb-2" rows="3" placeholder="Write a comment..." required></textarea>
  <button class="btn btn-primary align-self-start">Post Comment</button>
</form>
<?php include 'includes/footer.php'; ?>
