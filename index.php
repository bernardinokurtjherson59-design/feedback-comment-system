<?php
/*
 * File: index.php
 * Purpose: List feedback, provide live search/status filtering, and show feedback actions.
 * Access: Logged-in users and admins.
 * Tables: feedback, users, comments.
 */
// CRUD: READ feedback and comment totals; the same results power live table refreshes.
require 'db_connect.php';
require 'includes/auth.php';
require_login();
$title = 'All Feedback';

// Read optional filters so the page and its AJAX response show matching feedback.
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$like = '%' . $q . '%';

// Join the author and count comments for display in each feedback row.
// SECURITY: Bind all filter values to keep user input out of the SQL statement.
$stmt = $conn->prepare(
  "SELECT f.*, u.full_name,
          (SELECT COUNT(*) FROM comments c WHERE c.feedback_id = f.id) AS total_comments
   FROM feedback f
   JOIN users u ON u.id = f.user_id
   WHERE (u.full_name LIKE ? OR f.message LIKE ?) AND (? = '' OR f.status = ?)
   ORDER BY f.created_at DESC"
);
$stmt->bind_param('ssss', $like, $like, $status, $status);
$stmt->execute();
$rows = $stmt->get_result();

// Render feedback rows for both the full page and partial AJAX response.
// SECURITY: Escape user-controlled text and check permissions before showing actions.
// Parameters: mysqli_result $rows; returns void and writes table rows to the response.
function render_rows($rows) {
    if ($rows->num_rows === 0) {
        echo '<tr><td colspan="8" class="text-center text-muted py-4">No feedback found.</td></tr>';
        return;
    }
    while ($r = $rows->fetch_assoc()): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= e($r['full_name']) ?> <?php if (is_owner($r)): ?><span class="badge badge-you">You</span><?php endif; ?></td>
      <td><?= e($r['category']) ?></td>
      <td class="stars"><?= stars($r['rating']) ?></td>
      <td><?= e(mb_strimwidth($r['message'], 0, 60, '...')) ?></td>
      <td class="text-nowrap">
        <?php if (is_admin()): ?>
          <select class="form-select form-select-sm status-select status-<?= strtolower($r['status']) ?>"
                  data-id="<?= $r['id'] ?>" data-current="<?= e($r['status']) ?>" aria-label="Status">
            <?php foreach (STATUSES as $s): ?><option <?= $r['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
          </select><span class="status-msg small ms-1"></span>
        <?php else: ?>
          <?= status_badge($r['status']) ?>
        <?php endif; ?>
      </td>
      <td><?= $r['total_comments'] ?></td>
      <td class="text-end text-nowrap">
        <a href="view.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
        <?php if (can_edit($r)): ?>
          <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (can_delete($r)): ?>
        <form action="delete.php" method="post" class="d-inline" onsubmit="return confirm('Delete this feedback and its comments?');">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-danger">Delete</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile;
}

// Return only the refreshed rows when app.js requests a partial table update.
if (isset($_GET['ajax'])) { render_rows($rows); exit; }

// Choose a success message from known keys rather than displaying a raw query value.
$msgs = ['added' => 'Feedback submitted.', 'updated' => 'Feedback updated.', 'deleted' => 'Feedback deleted.'];
$msg = $msgs[$_GET['msg'] ?? ''] ?? '';

// Render the page shell and feedback controls after the query is ready.
include 'includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h2 class="mb-0">All Feedback</h2>
  <?php if (!is_admin()): ?><a href="add.php" class="btn btn-primary">+ Submit Feedback</a><?php endif; ?>
</div>

<form id="filterForm" class="row g-2 mb-3" method="get">
  <div class="col-md-8"><input type="text" name="q" class="form-control" placeholder="Search author or message..." value="<?= e($q) ?>"></div>
  <div class="col-md-4">
    <select name="status" class="form-select" aria-label="Filter by status">
      <option value="">All statuses</option>
      <?php foreach (STATUSES as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
    </select>
  </div>
</form>

<div class="table-responsive card shadow-sm">
<table class="table table-hover align-middle mb-0">
  <thead class="thead-soft">
    <tr><th>#</th><th>Author</th><th>Category</th><th>Rating</th><th>Message</th><th>Status</th><th>Comments</th><th class="text-end">Actions</th></tr>
  </thead>
  <tbody id="feedbackBody"><?php render_rows($rows); ?></tbody>
</table>
</div>
<?php include 'includes/footer.php'; ?>
