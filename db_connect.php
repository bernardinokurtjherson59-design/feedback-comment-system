<?php
/*
 * File: db_connect.php
 * Purpose: Open the MySQL connection and load shared output helpers.
 * Access: Included by guest, user, and admin pages.
 * Tables: None directly; the connection is used by pages that query the database.
 */
// Database connection. Keep deployment credentials in the live copy of this file.
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'feedback_db';


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// Connect using the local or deployment configuration defined above.
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die('Database connection failed. Check db_connect.php credentials.');
}

require_once __DIR__ . '/includes/helpers.php';
