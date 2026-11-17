<?php
// READ: list all feedback with search + status filter
require 'db_connect.php';
$title = 'All Feedback';

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$like = '%' . $q . '%';

$stmt = $conn->prepare(
  "SELECT f.*, (SELECT COUNT(*) FROM comments c WHERE c.feedback_id = f.id) AS total_comments
   FROM feedback f
   WHERE (f.name LIKE ? OR f.message LIKE ?) AND (? = '' OR f.status = ?)
   ORDER BY f.created_at DESC"
);
$stmt->bind_param('ssss', $like, $like, $status, $status);
$stmt->execute();
$rows = $stmt->get_result();

$msgs = ['added' => 'Feedback submitted.', 'updated' => 'Feedback updated.', 'deleted' => 'Feedback deleted.'];
$msg = $msgs[$_GET['msg'] ?? ''] ?? '';
$badge = ['New' => 'primary', 'Reviewed' => 'warning', 'Resolved' => 'success'];

include 'includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h2 class="mb-0">All Feedback</h2>
  <a href="add.php" class="btn btn-primary">+ Submit Feedback</a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Search name or message..." value="<?= e($q) ?>"></div>
  <div class="col-md-3">
    <select name="status" class="form-select">
      <option value="">All statuses</option>
      <?php foreach (STATUSES as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 d-grid"><button class="btn btn-outline-primary">Filter</button></div>
</form>

<div class="table-responsive card shadow-sm">
<table class="table table-hover align-middle mb-0">
  <thead class="table-light">
    <tr><th>#</th><th>Name</th><th>Category</th><th>Rating</th><th>Message</th><th>Status</th><th>Comments</th><th class="text-end">Actions</th></tr>
  </thead>
  <tbody>
  <?php if ($rows->num_rows === 0): ?>
    <tr><td colspan="8" class="text-center text-muted py-4">No feedback found.</td></tr>
  <?php endif; ?>
  <?php while ($r = $rows->fetch_assoc()): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= e($r['name']) ?><br><small class="text-muted"><?= e($r['email']) ?></small></td>
      <td><?= e($r['category']) ?></td>
      <td class="text-warning"><?= stars($r['rating']) ?></td>
      <td><?= e(mb_strimwidth($r['message'], 0, 60, '...')) ?></td>
      <td><span class="badge text-bg-<?= $badge[$r['status']] ?>"><?= e($r['status']) ?></span></td>
      <td><?= $r['total_comments'] ?></td>
      <td class="text-end text-nowrap">
        <a href="view.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">View</a>
        <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
        <form action="delete.php" method="post" class="d-inline" onsubmit="return confirm('Delete this feedback and its comments?');">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div>
<?php include 'includes/footer.php'; ?>
