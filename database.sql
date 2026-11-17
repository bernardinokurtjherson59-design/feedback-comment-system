-- Online Feedback and Comment System
-- Local (XAMPP): create a database named feedback_db in phpMyAdmin, then import this file.
-- InfinityFree: create the DB in Control Panel > MySQL Databases, open phpMyAdmin, select it, then import.

CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  category VARCHAR(30) NOT NULL DEFAULT 'General',
  rating TINYINT NOT NULL,
  message TEXT NOT NULL,
  status ENUM('New','Reviewed','Resolved') NOT NULL DEFAULT 'New',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  feedback_id INT NOT NULL,
  author VARCHAR(100) NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE
);

INSERT INTO feedback (name, email, category, rating, message, status) VALUES
('Juan Dela Cruz','juan@example.com','Suggestion',4,'Please add a dark mode to the portal.','New'),
('Maria Santos','maria@example.com','Compliment',5,'The enrollment page was fast and easy to use!','Reviewed');
INSERT INTO comments (feedback_id, author, comment) VALUES
(1,'Admin','Thanks! We will consider this for the next update.');
