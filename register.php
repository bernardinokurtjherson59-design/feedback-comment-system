<?php
// REGISTER: create a new account. Role is ALWAYS 'user' (admins are created in the database only).
// Same bot protection as login: CSRF token, honeypot field, time trap, strict validation, prepared statements.
require 'db_connect.php';
require 'includes/auth.php';
if (current_user()) { header('Location: index.php'); exit; }

$title = 'Register';
$full_name = $username = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'Your session expired. Please try again.';
    elseif (($_POST['website'] ?? '') !== '' || (time() - ($_SESSION['reg_form_time'] ?? 0)) < 2) $errors[] = 'Submission blocked. Please try again.';
    else {
        if ($full_name === '' || strlen($full_name) > 100) $errors[] = 'Full name is required (max 100 characters).';
        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) $errors[] = 'Username must be 3-30 letters, numbers or underscores.';
        if (strlen($password) < 8 || strlen($password) > 72) $errors[] = 'Password must be 8 to 72 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $s = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $s->bind_param('s', $username);
        $s->execute();
        if ($s->get_result()->num_rows > 0) {
            $errors[] = 'That username is already taken.';
        } else {
            // Never store plain passwords: hash them
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $s = $conn->prepare("INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)");
            $s->bind_param('sss', $full_name, $username, $hash);
            $s->execute();
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
$_SESSION['reg_form_time'] = time();
include 'includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
  <h2 class="mb-3">Create an account</h2>
  <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="card card-body shadow-sm auth" autocomplete="off">
    <?= csrf_field() ?>
    <div class="hp-field" aria-hidden="true">
      <label>Leave this field empty</label>
      <input type="text" name="website" tabindex="-1" autocomplete="off">
    </div>
    <label class="form-label">Full name</label>
    <input type="text" name="full_name" class="form-control mb-3" maxlength="100" value="<?= e($full_name) ?>" required>
    <label class="form-label">Username</label>
    <input type="text" name="username" class="form-control mb-3" maxlength="30" value="<?= e($username) ?>" required>
    <label class="form-label">Password (8-72 characters)</label>
    <input type="password" name="password" class="form-control mb-3" maxlength="72" required>
    <label class="form-label">Confirm password</label>
    <input type="password" name="confirm" class="form-control mb-3" maxlength="72" required>
    <button class="btn btn-primary w-100">Register</button>
    <p class="mt-3 mb-0 text-center">Already have an account? <a href="login.php">Log in</a></p>
  </form>
</div></div>
<?php include 'includes/footer.php'; ?>
