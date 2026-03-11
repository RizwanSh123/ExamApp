# ExamApp (ExamPortal v6)

Single‑page exam portal with Admin, Faculty/Sub‑Admin, and Student flows.

## Quick Start (XAMPP)
1. Copy the project folder into your XAMPP `htdocs` (example: `C:\xampp\htdocs\ExamApp`).
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Create database + tables:
   - Open `http://localhost/phpmyadmin`
   - Import `sql/schema.sql`
4. Configure DB (local):
   - Copy `api/config.local.php.example` to `api/config.local.php`
   - Update DB credentials if needed
5. Open the app:
   - `http://localhost/ExamApp/index.php`

## What This App Stores
- Primary state stored in browser `localStorage`.
- On save, state is synced to MySQL table `app_state`.
- Students/Faculty/Questions are also copied into real tables:
  - `students`, `faculty`, `questions` (read‑only for now).

## Default Admin Login
- Username: `admin`
- Password: `admin123`

## Key Files
- `examportal-v6.html` — main UI + app logic
- `api/state.php` — state sync API
- `api/db.php` — database connection
- `sql/schema.sql` — DB schema

## Notes
- This is not production‑ready security. Do **not** deploy to public without hardening.
- Passwords are stored in plain text in the current build.

For deployment steps, see `HANDOFF.md`.
