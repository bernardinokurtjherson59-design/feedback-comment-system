<?php
// Database connection. Change these values to your InfinityFree credentials when deploying.
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'feedback_db';

/*
$DB_HOST = 'sql304.infinityfree.com';       // InfinityFree: e.g. sqlXXX.infinityfree.com
$DB_USER = 'if0_43463004';                  // InfinityFree: e.g. if0_12345678
$DB_PASS = 'hide';                          // InfinityFree: your MySQL password
$DB_NAME = 'if0_43463004_owfeedback_db';    // InfinityFree: e.g. if0_12345678_feedback_db
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die('Database connection failed. Check db_connect.php credentials.');
}

require_once __DIR__ . '/includes/helpers.php';
