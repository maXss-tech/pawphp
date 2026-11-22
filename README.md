# PawBnB (PHP + MySQL)

## Setup
1. Install XAMPP / MAMP / Laragon (PHP 8.1+, MySQL or MariaDB).
2. Copy this folder to `htdocs/pawbnb` (or your web root).
3. In phpMyAdmin → Import → choose `database.sql` (creates the `pawbnb` database and tables).
4. Edit DB_HOST / DB_NAME / DB_USER / DB_PASS in `config.php`.
5. Open http://localhost/pawbnb/ and sign up.
6. Make yourself admin (phpMyAdmin → SQL):
   `UPDATE users SET role='admin' WHERE email='you@example.com';`
   Then use "Admin dashboard" in the account menu.

## What changed from the HTML/Supabase/localStorage version
- All data now lives in MySQL: users, pets, bookings, notifications, emergency requests, chat messages.
- Real login: passwords hashed with `password_hash()`, sessions, `session_regenerate_id()`.
- All queries use PDO prepared statements; output is escaped; every POST has a CSRF token.
- Booking flow: booking.php → review.php → confirm.php (saved to `bookings`).
- Added: My bookings (with cancel), admin dashboard (bookings, emergencies, pets, users, announcements).
- Removed: Supabase scripts, localStorage and the separate auth.js/admin.js.

## Before going live
Use HTTPS, set a non-root MySQL user with a strong password, and consider login rate limiting.
