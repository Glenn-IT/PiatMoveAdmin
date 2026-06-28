<?php
define('CURRENT_VERSION', 'v1.00');

require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Construction — PiatMove Admin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .uc-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 48px 40px;
            max-width: 440px;
            width: 90%;
            text-align: center;
        }

        .uc-icon {
            font-size: 56px;
            line-height: 1;
            margin-bottom: 16px;
        }

        .uc-badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 99px;
            margin-bottom: 20px;
        }

        .uc-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .uc-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .uc-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ef4444;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            padding: 11px 28px;
            border-radius: 8px;
            transition: background 0.15s;
        }

        .uc-logout:hover { background: #dc2626; }

        .uc-version {
            margin-top: 28px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="uc-card">
    <div class="uc-icon">🚧</div>
    <span class="uc-badge">Current Build: <?= CURRENT_VERSION ?></span>
    <h1 class="uc-title">Under Construction</h1>
    <p class="uc-desc">
        This feature hasn't been unlocked yet for the current presentation build.
        It will be available in an upcoming version.
    </p>
    <a href="<?= BASE_URL ?>/logout.php" class="uc-logout">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Logout
    </a>
    <p class="uc-version">PiatMove Admin &mdash; <?= CURRENT_VERSION ?></p>
</div>

</body>
</html>
<?php exit; ?>
