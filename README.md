# Online Feedback and Comment System
PHP + MySQL + Bootstrap 5 CRUD application (Web Systems Technologies final project).

## Features
- Register / login with two roles: **admin** and **user**
- Users: view all feedback, comment, edit/delete only their own feedback
- Admin: view, comment, change status (New / Reviewed / Resolved) instantly, delete any feedback
- Live search and status filter (no page reload)
- Dark mode and "About the website" menu; Charcoal & Coral theme
- Security: password hashing, prepared statements, output escaping, CSRF tokens, honeypot + time trap,
  login lockout (5 failed attempts = 80 seconds), server-side permission checks

## Run locally (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\feedback-system`
2. Start Apache and MySQL in XAMPP
3. In phpMyAdmin create database `feedback_db`, import `database.sql`
4. Open http://localhost/feedback-system/  (default admin: `admin` / `Admin@123`, change it)

## Updating an older database
Run `migration.sql` (adds users), then `migration_2_login_security.sql` (adds login lockout table).

## Database
- `users` (id, full_name, username, password_hash, role, created_at)
- `feedback` (id, user_id -> users.id, category, rating, message, status, created_at)
- `comments` (id, feedback_id -> feedback.id, user_id -> users.id, comment, created_at)
- `login_attempts` (ip, attempts, locked_until, last_attempt)
