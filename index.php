<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  =      $_POST['password'] ?? '';

    if ($email && $pass) {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id, name, password FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($pass, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
    }
    $error = 'Invalid email or password.';
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
        </div>

        <button type="submit" class="login-btn">Sign In</button>
    </form>

    <p class="login-trust">Secured by the Municipality of Piat &middot; Authorized personnel only</p>
</div>

</body>
</html>
