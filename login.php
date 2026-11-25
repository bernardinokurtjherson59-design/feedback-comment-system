<?php
/*
 * File: login.php
 * Purpose: Authenticate an account and apply bot and brute-force protections.
 * Access: Guests; authenticated users are redirected to the feedback list.
 * Tables: users, login_attempts.
 */
// CRUD: READ account and lockout records; successful and failed logins update lockout data.
require 'db_connect.php';
require 'includes/auth.php';
require 'includes/security.php';
if (current_user()) { header('Location: index.php'); exit; }

$title = 'Login';
$error = '';
$username = '';
$ip = client_ip();
$wait = lock_remaining($conn, $ip);

// Validate the session token and bot checks before querying account credentials.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($wait > 0) {
        // Locked: do not even look at the submitted credentials
    } elseif (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // SECURITY: The hidden honeypot and minimum form time help screen automated submits.
        $looksLikeBot = ($_POST['website'] ?? '') !== ''
                     || (time() - ($_SESSION['login_form_time'] ?? 0)) < 2;
        // SECURITY: Restrict usernames and password size before checking the database.
        $validFormat = preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)
                    && $password !== '' && strlen($password) <= 72;

        $ok = false;
        if (!$looksLikeBot && $validFormat) {
            // SECURITY: Query by a bound username and verify the stored password hash.
            $s = $conn->prepare("SELECT id, full_name, username, password_hash, role FROM users WHERE username = ?");
            $s->bind_param('s', $username);
            $s->execute();
            $u = $s->get_result()->fetch_assoc();
            $ok = $u && password_verify($password, $u['password_hash']);
        }

        if ($ok) {
            clear_attempts($conn, $ip);
            // SECURITY: Rotate the session ID and issue a new CSRF token after login.
            session_regenerate_id(true); // prevents session fixation
            unset($_SESSION['csrf']);    // fresh token for the logged-in session
            $_SESSION['user'] = [
                'id' => (int)$u['id'],
                'full_name' => $u['full_name'],
                'username' => $u['username'],
                'role' => $u['role'],
            ];
            header('Location: index.php');
            exit;
        }

        // Count a failed attempt, add a small delay, and enforce the configured lockout.
        $left = register_failure($conn, $ip);
        usleep(400000);
        $wait = lock_remaining($conn, $ip);
        $error = 'Invalid username or password.';
        if ($wait === 0) $error .= " $left attempt(s) left before a " . LOCK_SECONDS . '-second lock.';
    }
}

// Record when this form was rendered so the next submission can be time-checked.
$_SESSION['login_form_time'] = time(); // start the time trap for the next submit
// Render the login form and any lockout or validation message.
include 'includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
  <h2 class="mb-3">Login</h2>
  <?php if (isset($_GET['registered'])): ?><div class="alert alert-success py-2">Account created. You can log in now.</div><?php endif; ?>
  <?php if ($wait > 0): ?>
    <div class="alert alert-warning py-2">⏳ Too many failed attempts. Please wait
      <strong><span id="countdown" data-seconds="<?= (int)$wait ?>"><?= (int)$wait ?></span></strong> seconds before trying again.</div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger py-2"><?= e($error) ?></div>
  <?php endif; ?>
  <form method="post" class="card card-body shadow-sm auth" autocomplete="off">
    <fieldset <?= $wait > 0 ? 'disabled' : '' ?>>
      <?= csrf_field() ?>
      <div class="hp-field" aria-hidden="true">
        <label>Leave this field empty</label>
        <input type="text" name="website" tabindex="-1" autocomplete="off">
      </div>
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control mb-3" maxlength="30" value="<?= e($username) ?>" required autofocus>
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control mb-3" maxlength="72" required>
      <button class="btn btn-primary w-100">Login</button>
    </fieldset>
    <p class="mt-3 mb-0 text-center">No account yet? <a href="register.php">Register</a></p>
  </form>
</div></div>
<?php include 'includes/footer.php'; ?>
