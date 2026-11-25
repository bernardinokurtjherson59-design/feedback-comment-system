-- MIGRATION: adds login (users table) to an EXISTING database.
-- Run ONCE in phpMyAdmin (select your database first, then SQL tab, paste, Go).
-- Existing feedback/comments become owned by the admin account.
-- Default admin login:  username: admin   password: Admin@123   (change it after your first login)

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (full_name, username, password_hash, role)
VALUES ('Administrator', 'admin', '$2y$12$vAEHI87iBnplJvWAxbgs1.XpobrDFSmNXxvjSDrEk/qA/B4IJLTt6', 'admin');

-- feedback: add owner, drop the old name/email columns
ALTER TABLE feedback ADD COLUMN user_id INT NULL AFTER id;
UPDATE feedback SET user_id = (SELECT id FROM users WHERE username = 'admin');
ALTER TABLE feedback
  MODIFY user_id INT NOT NULL,
  ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  DROP COLUMN name,
  DROP COLUMN email;

-- comments: add author (user), drop the old author text column
ALTER TABLE comments ADD COLUMN user_id INT NULL AFTER feedback_id;
UPDATE comments SET user_id = (SELECT id FROM users WHERE username = 'admin');
ALTER TABLE comments
  MODIFY user_id INT NOT NULL,
  ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  DROP COLUMN author;
