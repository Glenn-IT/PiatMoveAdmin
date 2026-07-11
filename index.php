<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';

$lockout_remaining = 0;
if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
    $lockout_remaining = $_SESSION['lockout_until'] - time();
} elseif (isset($_SESSION['lockout_until'])) {
    unset($_SESSION['lockout_until']);
    unset($_SESSION['login_attempts']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($lockout_remaining > 0) {
        $error = 'Too many failed attempts. Please wait ' . $lockout_remaining . ' seconds and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  =      $_POST['password'] ?? '';
        $ok    = false;

        if ($email && $pass) {
            $db   = get_db();
            // BINARY forces a case-sensitive comparison (column collation is case-insensitive by default)
            $stmt = $db->prepare('SELECT id, name, password FROM admins WHERE BINARY email = BINARY ?');
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($pass, $admin['password'])) {
                $ok = true;
                session_regenerate_id(true);
                unset($_SESSION['login_attempts'], $_SESSION['lockout_until']);
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: ' . BASE_URL . '/dashboard.php');
                exit;
            }
        }

        if (!$ok) {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_until']  = time() + 30;
                $lockout_remaining = 30;
                $error = 'Too many failed attempts. Please wait 30 seconds and try again.';
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — PiatMove Admin</title>
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card">

    <div class="login-logo">
        <img src="<?= BASE_URL ?>/assets/logo-full.svg" alt="PiatMove">
    </div>

    <h1 class="login-title">Admin Portal</h1>
    <p class="login-sub">Sign in to the Municipal Transport Office dashboard</p>

    <?php if ($error): ?>
        <div class="login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label class="login-label" for="email">Email Address</label>
        <div class="login-input-wrap">
            <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
            </svg>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="admin@piatmove.gov.ph" required autofocus>
        </div>

        <label class="login-label" for="password">Password</label>
        <div class="login-input-wrap" style="margin-bottom: 0">
            <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <button type="button" id="togglePw" class="pw-toggle" tabindex="-1" aria-label="Show password">
                <svg id="eyeShow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <svg id="eyeHide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </button>
        </div>

        <div style="text-align:right;margin-top:8px;margin-bottom:4px">
            <a href="<?= BASE_URL ?>/forgot-password.php" style="font-size:12px;color:var(--text-link);text-decoration:none;font-weight:500">Forgot password?</a>
        </div>

        <button type="submit" class="login-btn" id="loginBtn"<?= $lockout_remaining > 0 ? ' disabled' : '' ?>>
            <?= $lockout_remaining > 0 ? 'Try again in ' . $lockout_remaining . 's' : 'Sign In' ?>
        </button>
    </form>

    <p class="login-trust">Secured by the Municipality of Piat &middot; Authorized personnel only</p>
</div>

<script>
(function () {
    var btn = document.getElementById('togglePw');
    var inp = document.getElementById('password');
    var show = document.getElementById('eyeShow');
    var hide = document.getElementById('eyeHide');
    btn.addEventListener('click', function () {
        var visible = inp.type === 'text';
        inp.type = visible ? 'password' : 'text';
        show.style.display = visible ? '' : 'none';
        hide.style.display = visible ? 'none' : '';
    });
}());

(function () {
    var remaining = <?= (int) $lockout_remaining ?>;
    if (remaining <= 0) return;

    var loginBtn = document.getElementById('loginBtn');
    var timer = setInterval(function () {
        remaining--;
        if (remaining <= 0) {
            clearInterval(timer);
            loginBtn.disabled = false;
            loginBtn.textContent = 'Sign In';
        } else {
            loginBtn.textContent = 'Try again in ' + remaining + 's';
        }
    }, 1000);
}());
</script>
</body>
</html>
