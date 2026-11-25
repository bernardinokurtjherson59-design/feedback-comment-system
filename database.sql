-- File: database.sql
-- Purpose: Create the complete schema and seed the administrator for a fresh install.
-- Access: Run by the database administrator during setup, not through the website.
-- Tables: users, feedback, comments, login_attempts.
-- Online Feedback and Comment System (FULL schema for a fresh database)
-- Local (XAMPP): create a database named feedback_db in phpMyAdmin, then import this file.
-- InfinityFree: create the DB in Control Panel > MySQL Databases, open phpMyAdmin, select it, then import.
-- Default admin login:  username: admin   password: Admin@123   (change after first login)

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category VARCHAR(30) NOT NULL DEFAULT 'General',
  rating TINYINT NOT NULL,
  message TEXT NOT NULL,
  status ENUM('New','Reviewed','Resolved') NOT NULL DEFAULT 'New',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  feedback_id INT NOT NULL,
  user_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (full_name, username, password_hash, role)
VALUES ('Administrator', 'admin', '$2y$12$vAEHI87iBnplJvWAxbgs1.XpobrDFSmNXxvjSDrEk/qA/B4IJLTt6', 'admin');

-- Login protection (5 failed attempts => 80-second lock)
CREATE TABLE IF NOT EXISTS login_attempts (
  ip VARCHAR(45) NOT NULL PRIMARY KEY,
  attempts INT NOT NULL DEFAULT 0,
  locked_until INT NOT NULL DEFAULT 0,
  last_attempt INT NOT NULL DEFAULT 0
);
