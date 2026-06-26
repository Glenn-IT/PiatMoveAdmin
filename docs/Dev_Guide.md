# PiatMoveAdmin — Development Guide & Claude CLI Prompt

## Paste this prompt when resuming in Claude CLI

```
We are building PiatMoveAdmin — a PHP web admin dashboard for the PiatMove ride-booking app.
It lives at C:\xampp\htdocs\PiatMoveAdmin\ and runs on XAMPP (Apache + MySQL).

Context:
- The REST API is at C:\xampp\htdocs\piatmove-api\ (already complete and tested)
- Both apps share the same MySQL database: `piatmove`
- The admin panel uses PHP sessions for auth (NOT JWT — that's for the Android apps)
- The admin panel queries the DB directly via PDO (does not call the API)
- No framework — plain PHP, Bootstrap 5 via CDN

Database: piatmove (MySQL)
Tables: users, driver_info, bookings, fcm_tokens, admins
Admin login credentials are in the `admins` table (seeded separately in phpMyAdmin)

Folder structure:
PiatMoveAdmin/
├── config.php                ← DB credentials, base URL
├── logout.php                ← destroys session, redirects to login
├── index.php                 ← admin login page
├── dashboard.php             ← overview stats (users, drivers, bookings counts)
├── users.php                 ← list all users, search, activate/deactivate/delete
├── drivers.php               ← list drivers, filter by status, approve/reject
├── bookings.php              ← list all bookings, filter by status
├── includes/
│   ├── db.php                ← PDO singleton via get_db()
│   ├── auth.php              ← session_start() + redirect to login if not logged in
│   ├── header.php            ← HTML head + Bootstrap + dark sidebar nav
│   └── footer.php            ← closing tags + Bootstrap JS
├── assets/
│   ├── css/admin.css         ← custom styles on top of Bootstrap
│   └── js/admin.js           ← confirm dialogs, search filter
└── docs/
    └── Dev_Guide.md          ← this file

Current status: All files are built and working.

When resuming, check what the user wants to change or add:
- UI improvements (styling, layout)
- New pages or features
- Bug fixes
- Additional filters or search
```

---

## What's Built

| File | Purpose | Status |
|------|---------|--------|
| `config.php` | DB config | Done |
| `includes/db.php` | PDO connection | Done |
| `includes/auth.php` | Session guard | Done |
| `includes/header.php` | Nav + layout | Done |
| `includes/footer.php` | Closing tags | Done |
| `index.php` | Login page | Done |
| `logout.php` | Logout | Done |
| `dashboard.php` | Stats overview | Done |
| `users.php` | Manage users | Done |
| `drivers.php` | Approve drivers | Done |
| `bookings.php` | View bookings | Done |
| `assets/css/admin.css` | Styles | Done |
| `assets/js/admin.js` | JS helpers | Done |

---

## DB Connection

```php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
$db = get_db();
```

## Session Guard (include at top of every protected page)

```php
require_once __DIR__ . '/includes/auth.php';
```

## Page Template

```php
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Page Title';
require_once __DIR__ . '/includes/header.php';
?>

<!-- page content here -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

---

*Created: 2026-06-26*
*Stack: PHP 8 + MySQL + Bootstrap 5 + XAMPP*
