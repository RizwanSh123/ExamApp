# Handoff Guide (Domain + Storage)

This guide is for the person who will attach a domain and hosting.

## 1) Environment Options
### A. Shared Hosting (cPanel)
- Upload project to `public_html/ExamApp`
- Create MySQL database + user in cPanel
- Import `sql/schema.sql` in phpMyAdmin
- Create `api/config.local.php` with DB creds
- Open: `https://yourdomain.com/ExamApp/index.php`

### B. VPS (Apache/Nginx + PHP + MySQL)
- Clone/upload project to web root
- Configure PHP 8.x, enable PDO MySQL
- Import `sql/schema.sql`
- Create `api/config.local.php`
- Point domain to server IP

### C. Google Cloud Run
- See `DEPLOY_GOOGLE_CLOUD.md`

## 2) Required PHP Extensions
- `pdo`
- `pdo_mysql`

## 3) Database Setup
1. Create database `exam_portal`
2. Import `sql/schema.sql`
3. Update `api/config.local.php` with credentials

## 4) Verify API
Open:
- `/api/health.php`
- `/api/state.php`

Both should return JSON with `"ok": true`.

## 5) Security (Required Before Public Launch)
- Add authentication/authorization to API endpoints
- Hash passwords (never store plain text)
- Enforce HTTPS
- Add CSRF protection
- Add rate limiting
- Lock down `/api` with server rules if possible

## 6) Backups
Schedule daily MySQL backups. Store in separate location.

## 7) Maintenance Notes
- Main UI + logic is in one file: `examportal-v6.html`
- State sync API is `api/state.php`
- Students/Faculty/Questions tables are currently **sync-only**.

## 8) Contact Notes
If issues occur:
- Check browser console for JS errors
- Check PHP error logs on server
- Ensure MySQL credentials are correct
