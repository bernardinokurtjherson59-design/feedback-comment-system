<?php
/*
 * File: logout.php
 * Purpose: Clear the current session and return to the login page.
 * Access: Logged-in users and admins.
 * Tables: None.
 */
// End the current session, then send the browser to the login page.
require 'includes/auth.php';
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
