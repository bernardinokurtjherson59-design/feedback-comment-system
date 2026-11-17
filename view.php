<?php
// View one feedback item + its comments (comments have their own add/delete)
require 'db_connect.php';
$title = 'View Feedback';
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM feedback WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$f = $stmt->get_result()->fetch_assoc();
if (!$f) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'add_comment') {
        $author = trim($_POST['author'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        if ($author === '' || $comment === '') {
            $error = 'Name and comment are required.';
        } else {
            $s = $conn->prepare("INSERT INTO comments (feedback_id, author, comment) VALUES (?, ?, ?)");
            $s->bind_param('iss', $id, $author, $comment);
            $s->execute();
            header("Location: view.php?id=$id");
            exit;
        }
    } elseif (($_POST['action'] ?? '') === 'delete_comment') {
        $cid = (int)($_POST['comment_id'] ?? 0);
        $s = $conn->prepare("DELETE FROM comments WHERE id = ? AND feedback_id = ?");
        $s->bind_param('ii', $cid, $id);
        $s->execute();
        header("Location: view.php?id=$id");
        exit;
    }
}

$s = $conn->prepare("SELECT * FROM comments WHERE feedback_id = ? ORDER BY created_at ASC");
$s->bind_param('i', $id);
$s->execute();
$comments = $s->get_result();

include 'includes/header.php';
?>
<a href="index.php" class="btn btn-sm btn-outline-secondary mb-3">&larr; Back</a>
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between flex-wrap">
      <h4><?= e($f['category']) ?> <small class="text-warning"><?= stars($f['rating']) ?></small></h4>
      <span class="badge text-bg-secondary align-self-start"><?= e($f['status']) ?></span>
    </div>
    <p class="text-muted mb-2">by <?= e($f['name']) ?> (<?= e($f['email']) ?>) &middot; <?= e($f['created_at']) ?></p>
    <p class="mb-0"><?= nl2br(e($f['message'])) ?></p>
  </div>
</div>

<h5>Comments (<?= $comments->num_rows ?>)</h5>
<?php while ($c = $comments->fetch_assoc()): ?>
  <div class="card mb-2"><div class="card-body py-2 d-flex justify-content-between">
    <div><strong><?= e($c['author']) ?></strong> <small class="text-muted"><?= e($c['created_at']) ?></small><br><?= nl2br(e($c['comment'])) ?></div>
    <form method="post" onsubmit="return confirm('Delete this comment?');">
      <input type="hidden" name="action" value="delete_comment">
      <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
      <button class="btn btn-sm btn-outline-danger">Delete</button>
    </form>
  </div></div>
<?php endwhile; ?>

<form method="post" class="card card-body shadow-sm mt-3">
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
  <input type="hidden" name="action" value="add_comment">
  <input type="text" name="author" class="form-control mb-2" placeholder="Your name" maxlength="100" required>
  <textarea name="comment" class="form-control mb-2" rows="3" placeholder="Write a comment..." required></textarea>
  <button class="btn btn-primary align-self-start">Post Comment</button>
</form>
<?php include 'includes/footer.php'; ?>
