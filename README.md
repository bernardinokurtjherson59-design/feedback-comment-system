# Online Feedback and Comment System

A web application for collecting feedback, discussing submissions with threaded
comments, and tracking each submission's review status. Regular users manage
their own submissions, while administrators moderate feedback and comments.

## Features

### Guest

- Register an account or sign in.
- Use the navbar's About the website popup and choose a browser-saved theme.
- Feedback pages require a signed-in account; guests cannot browse the feedback list.

### User

- Read feedback from all accounts and search by author or message.
- Submit feedback with a category, 1-5 star rating, and message.
- Edit or delete only feedback they own.
- Comment on feedback and delete their own comments.
- Filter feedback by status while the table refreshes live.

### Admin

- Read all feedback and participate in comment discussions.
- Change feedback status to New, Reviewed, or Resolved with an instant table update.
- Delete any feedback or comment.
- Admins cannot submit or edit feedback through the regular-user forms.

## Technologies

- PHP and MySQLi for server-side pages, session authentication, and database access.
- MySQL for account, feedback, comment, and login-attempt records.
- Bootstrap 5.3.3 for responsive layout and components.
- CSS for the Charcoal & Coral theme and light/dark color variables.
- JavaScript Fetch/AJAX for live search, status filtering, and administrator status updates.

## Folder structure

```text
feedback-system/
|-- index.php                         # Feedback list, live filters, and action links
|-- add.php                           # Create feedback
|-- view.php                          # Read one feedback item and its comments
|-- edit.php                          # Update owned feedback
|-- delete.php                        # Delete permitted feedback
|-- login.php                         # Sign in and enforce login protection
|-- register.php                      # Create a regular user account
|-- logout.php                        # End the current session
|-- update_status.php                 # AJAX endpoint for admin status changes
|-- db_connect.php                    # MySQL connection and shared helper loading
|-- database.sql                      # Fresh-install schema and admin seed
|-- migration.sql                     # Upgrade older feedback/comment schema for accounts
|-- migration_2_login_security.sql    # Add persistent login-attempt tracking
|-- README.md                          # Project, setup, schema, and security guide
|-- CHANGELOG.md                       # High-level project version history
|-- includes/
|   |-- auth.php                       # Session, role, ownership, and CSRF helpers
|   |-- security.php                   # Per-IP login lockout helpers
|   |-- helpers.php                    # Feedback validation and display helpers
|   |-- header.php                     # Shared page head and navigation
|   |-- footer.php                     # Shared closing markup, About popup, and scripts
|   `-- form.php                       # Shared feedback create/edit form
|-- assets/
|   |-- style.css                      # Theme, responsive component styling, and honeypot
|   `-- app.js                         # Theme, countdown, live filters, and admin updates
```

## Database design

The full schema in `database.sql` creates four tables. Column types and defaults
below match that file.

### Tables and columns

| Table | Columns |
|---|---|
| `users` | `id INT AUTO_INCREMENT PRIMARY KEY`; `full_name VARCHAR(100) NOT NULL`; `username VARCHAR(50) NOT NULL UNIQUE`; `password_hash VARCHAR(255) NOT NULL`; `role ENUM('user','admin') NOT NULL DEFAULT 'user'`; `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `feedback` | `id INT AUTO_INCREMENT PRIMARY KEY`; `user_id INT NOT NULL`; `category VARCHAR(30) NOT NULL DEFAULT 'General'`; `rating TINYINT NOT NULL`; `message TEXT NOT NULL`; `status ENUM('New','Reviewed','Resolved') NOT NULL DEFAULT 'New'`; `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `comments` | `id INT AUTO_INCREMENT PRIMARY KEY`; `feedback_id INT NOT NULL`; `user_id INT NOT NULL`; `comment TEXT NOT NULL`; `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `login_attempts` | `ip VARCHAR(45) NOT NULL PRIMARY KEY`; `attempts INT NOT NULL DEFAULT 0`; `locked_until INT NOT NULL DEFAULT 0`; `last_attempt INT NOT NULL DEFAULT 0` |

`feedback.user_id` references `users.id`. `comments.feedback_id` references
`feedback.id`, and `comments.user_id` references `users.id`. Each of these
foreign keys uses `ON DELETE CASCADE`, so removing an account removes its
feedback and comments, and removing feedback removes its comments. The
`login_attempts` table is keyed by IP and has no user foreign key.

### Text ERD

```text
users (1) ----< feedback (many)
  |                  |
  |                  +----< comments (many)
  +-----------------------< comments (many)

login_attempts: independent records keyed by client IP
```

## Permission matrix

| Action | Guest | User | Admin |
|---|---:|---:|---:|
| Register / sign in | Yes | Sign in | Sign in |
| View feedback list and details | No | Yes | Yes |
| Search and filter feedback | No | Yes | Yes |
| Submit feedback | No | Yes | No |
| Edit feedback | No | Own feedback only | No |
| Delete feedback | No | Own feedback only | Any feedback |
| Add a comment | No | Yes | Yes |
| Delete a comment | No | Own comments only | Any comment |
| Change feedback status | No | No | Yes |
| Use the theme toggle and About popup | Yes | Yes | Yes |

The interface hides actions that are not available, but authorization is also
checked by PHP on the server. In particular, edit, delete, and status changes
must pass their server-side permission checks.

## Security measures

- **Prepared SQL statements:** Database pages bind request values as parameters
  rather than concatenating them into query strings.
- **Password hashing:** Registration stores a `password_hash()` result; login
  uses `password_verify()` rather than comparing plaintext passwords.
- **Session authentication and roles:** The session identifies the signed-in
  account; server-side role and ownership helpers gate protected actions.
- **CSRF tokens:** Login, registration, and the admin status endpoint validate
  a token stored in the PHP session. The token is included in forms by
  `includes/auth.php`.
- **Output escaping:** `e()` applies HTML escaping to dynamic text before it is
  inserted into page markup, reducing cross-site scripting risk.
- **Login anti-bot checks:** Login and registration forms include a hidden
  honeypot field and a two-second time trap. Username formats and password
  lengths are checked on the server.
- **IP-based lockout:** Five failed login attempts trigger an 80-second
  cooldown. Attempts and lock expiry are stored in `login_attempts`, not only
  in browser cookies; records are cleared after successful login.
- **Credential/session handling:** Login rotates the session ID after success.
  Keep the live database connection configuration separate from the local
  development copy and never publish database credentials.

These are the protections implemented in this project, not a substitute for
reviewing deployment configuration and testing the application before exposure.

## Local setup with XAMPP

1. Install XAMPP and place the project folder at
   `C:\xampp\htdocs\feedback-system`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin/` and create a database named
   `feedback_db`.
4. Select `feedback_db`, choose **Import**, and import `database.sql` for a fresh
   installation. Do not run the migration files on a fresh schema.
5. Check that the local `db_connect.php` points to the local database. Keep any
   actual database password private; do not copy it into this document or source
   control.
6. Visit `http://localhost/feedback-system/`, register a regular account, and
   verify feedback, comments, and search.
7. Sign in with the seeded administrator account from the fresh-install
   database, then change its default password immediately.

## Deployment on InfinityFree

1. Create a hosting account and a MySQL database in the InfinityFree control
   panel. Record the assigned database name, host, username, and password
   privately; InfinityFree database details are not necessarily `localhost`.
2. Open that database's phpMyAdmin from the hosting control panel. Select the
   intended database before importing any SQL.
3. For a new installation, import `database.sql` once. For an existing legacy
   installation, back up first and apply `migration.sql`, then
   `migration_2_login_security.sql` if login protection has not already been
   added. Do not apply migrations to a fresh schema.
4. Upload the project files to the website's document root. Keep the local
   development `db_connect.php` separate: configure the live copy with the
   host-provided database settings on the server, and do not replace the local
   copy or commit real credentials.
5. Visit the deployed site over HTTPS if available. Test registration, sign-in,
   feedback ownership, comments, and admin status updates.
6. Change the seeded administrator password immediately after the first
   successful admin sign-in. Remove or restrict access to installation
   materials and database tools when setup is complete.

## Default administrator account

`database.sql` seeds an administrator account for first-time setup. Change its
default password immediately after installation, and do not reuse a database
password for this account. The SQL files contain the seeded password hash; use
the initial application credentials supplied with the project installation.

## Unique features and innovations

1. **Role-based access and ownership:** Administrators can change status and
   delete any feedback; users can edit and delete only their own. PHP rechecks
   access on the server, with shared rules in `includes/auth.php` and page
   checks in `edit.php`, `delete.php`, and `update_status.php`.
2. **Live administrator status changes:** An admin selects New, Reviewed, or
   Resolved directly in the feedback table. `assets/app.js` sends the change to
   `update_status.php`, shows a Saved confirmation, and restores the previous
   choice if the request fails.
3. **Live search and status filtering:** The table refreshes while the user
   types or selects a status, without a Filter button or full-page reload.
   `assets/app.js` requests the partial table response provided by `index.php`.
4. **Layered login bot and brute-force checks:** Login uses a session CSRF
   token, hidden honeypot, two-second time trap, strict username pattern,
   prepared query, password verification, and an 80-second lock after five
   failures. `includes/security.php` stores the IP-based lock state in MySQL,
   so clearing cookies does not remove that database record.
5. **Threaded comments tied to accounts:** Comments belong to both a feedback
   item and a user account. `view.php` shows the thread and permits deletion
   only by the comment owner or an administrator.
6. **Remembered dark mode and About popup:** `assets/app.js` saves the theme
   choice in browser storage, while `includes/header.php` provides the navbar
   menu and About dialog.
7. **Charcoal & Coral theme:** `assets/style.css` overrides Bootstrap 5
   variables for a coordinated palette and matching dark mode, with component
   colors chosen for readable contrast.
8. **Responsive layout:** Bootstrap's collapsing navbar and responsive table
   wrapper keep the interface usable on smaller screens. The shared layout is
   in `includes/header.php`, with table markup in `index.php`.
9. **Safe schema upgrades:** `database.sql` provides a full fresh-install
   schema, while `migration.sql` and `migration_2_login_security.sql` upgrade
   older installations in stages.

## Project information

- **Course:** Web Systems Technologies
- **Project:** Final Project
- **Author:** [Your Name]
- **Date:** November 25, 2026
