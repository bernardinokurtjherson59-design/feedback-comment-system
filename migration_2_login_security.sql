-- File: migration_2_login_security.sql
-- Purpose: Add persistent per-IP failed-login tracking to an upgraded database.
-- Access: Run once by the database administrator after migration.sql as needed.
-- Tables: login_attempts.
-- MIGRATION 2: login protection table (5 failed attempts => 80-second lock).
-- Run ONCE in phpMyAdmin: select your database -> SQL tab -> paste -> Go.
-- (Run migration.sql first if you have not added the users table yet.)
CREATE TABLE IF NOT EXISTS login_attempts (
  ip VARCHAR(45) NOT NULL PRIMARY KEY,
  attempts INT NOT NULL DEFAULT 0,
  locked_until INT NOT NULL DEFAULT 0,
  last_attempt INT NOT NULL DEFAULT 0
);
