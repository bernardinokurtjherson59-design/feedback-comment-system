<?php
/*
 * File: includes/security.php
 * Purpose: Track failed logins by client IP and enforce temporary lockouts.
 * Access: Included by guest login processing.
 * Tables: login_attempts.
 */
// A lock begins at five failures; older attempts expire after fifteen minutes.
const MAX_ATTEMPTS   = 5;
const LOCK_SECONDS   = 80;
const ATTEMPT_WINDOW = 900; // failed attempts older than 15 minutes are forgotten

// Read the remote address in the maximum length supported by the database column.
// Parameters: none; returns an IP address string.
function client_ip() { return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45); }

// SECURITY: Find the number of seconds remaining in this IP's lockout.
// Parameters: mysqli $conn and string $ip; returns remaining seconds as int.
function lock_remaining(mysqli $conn, string $ip): int {
    $s = $conn->prepare("SELECT locked_until FROM login_attempts WHERE ip = ?");
    $s->bind_param('s', $ip);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    return $r ? max(0, (int)$r['locked_until'] - time()) : 0;
}

// SECURITY: Store a failed attempt and begin a lock when the configured limit is reached.
// Parameters: mysqli $conn and string $ip; returns attempts left, or 0 when locked.
function register_failure(mysqli $conn, string $ip): int {
    $now = time();
    $cut = $now - ATTEMPT_WINDOW;
    $s = $conn->prepare(
        "INSERT INTO login_attempts (ip, attempts, locked_until, last_attempt) VALUES (?, 1, 0, ?)
         ON DUPLICATE KEY UPDATE attempts = IF(last_attempt < ?, 1, attempts + 1), last_attempt = ?"
    );
    $s->bind_param('siii', $ip, $now, $cut, $now);
    $s->execute();

    $s = $conn->prepare("SELECT attempts FROM login_attempts WHERE ip = ?");
    $s->bind_param('s', $ip);
    $s->execute();
    $n = (int)$s->get_result()->fetch_assoc()['attempts'];

    if ($n >= MAX_ATTEMPTS) {
        $until = $now + LOCK_SECONDS;
        $s = $conn->prepare("UPDATE login_attempts SET attempts = 0, locked_until = ? WHERE ip = ?");
        $s->bind_param('is', $until, $ip);
        $s->execute();
        return 0;
    }
    return MAX_ATTEMPTS - $n;
}

// SECURITY: Remove the failed-attempt record after successful authentication.
// Parameters: mysqli $conn and string $ip; returns void.
function clear_attempts(mysqli $conn, string $ip): void {
    $s = $conn->prepare("DELETE FROM login_attempts WHERE ip = ?");
    $s->bind_param('s', $ip);
    $s->execute();
}
