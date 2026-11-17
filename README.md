# Online Feedback and Comment System
PHP + MySQL + Bootstrap 5 CRUD application (Web Systems Technologies final project).

## Features
- Create / Read / Update / Delete feedback
- Comments on each feedback (separate table, foreign key with cascade delete)
- Search by name/message, filter by status
- Prepared statements (SQL injection safe) and output escaping (XSS safe)

## Run locally (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\feedback-system`
2. Start Apache and MySQL in XAMPP
3. In phpMyAdmin create database `feedback_db`, import `database.sql`
4. Open http://localhost/feedback-system/

## Database
- `feedback` (id, name, email, category, rating, message, status, created_at)
- `comments` (id, feedback_id -> feedback.id, author, comment, created_at)
