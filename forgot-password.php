<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$db    = get_db();
$step  = 'email';
$error = '';
$info  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $posted_step = $_POST['step'] ?? 'email';

    if ($posted_step === 'email') {
        $email = trim($_POST['email'] ?? '');
        if (!$email) {
            $error = 'Please enter your email address.';
            $step  = 'email';
        } else {
            $stmt = $db->prepare("SELECT id, name, security_question, security_answer FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if (!$admin || !$admin['security_question']) {
                $error = 'No account with a security question found for that email.';
                $step  = 'email';
            } else {
                $_SESSION['fp_admin_id'] = $admin['id'];
                $_SESSION['fp_question'] = $admin['security_question'];
                $step = 'question';
            }
        }

    } elseif ($posted_step === 'question') {
        if (empty($_SESSION['fp_admin_id'])) {
            $step  = 'email';
            $error = 'Session expired. Please start again.';
        } else {
            $answer = trim($_POST['security_answer'] ?? '');
            $stmt   = $db->prepare("SELECT security_answer FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['fp_admin_id']]);
            $row = $stmt->fetch();

            if (!$row || !password_verify(strtolower($answer), $row['security_answer'])) {
                $error = 'Incorrect answer. Please try again.';
                $step  = 'question';
            } else {
                $_SESSION['fp_verified'] = true;
                $step = 'reset';
            }
        }

    } elseif ($posted_step === 'reset') {
        if (empty($_SESSION['fp_verified']) || empty($_SESSION['fp_admin_id'])) {
            $step  = 'email';
            $error = 'Session expired. Please start again.';
        } else {
            $new_pw  = $_POST['new_password']     ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($new_pw) < 8) {
                $error = 'Password must be at least 8 characters.';
                $step  = 'reset';
            } elseif ($new_pw !== $confirm) {
                $error = 'Passwords do not match.';
                $step  = 'reset';
            } else {
                $hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $db->prepare("UPDATE admins SET password = ? WHERE id = ?")
                   ->execute([$hash, $_SESSION['fp_admin_id']]);

                unset($_SESSION['fp_admin_id'], $_SESSION['fp_question'], $_SESSION['fp_verified']);
                $info = 'Password reset successfully. You can now sign in.';
                $step = 'done';
            }
        }
    }

} else {
    unset($_SESSION['fp_admin_id'], $_SESSION['fp_question'], $_SESSION['fp_verified']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — PiatMove Admin</title>
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card">

    <div class="login-logo">
        <img src="<?= BASE_URL ?>/assets/logo-full.svg" alt="PiatMove">
    </div>

    <?php if ($step === 'done'): ?>

        <h1 class="login-title">Password Reset</h1>
        <p class="login-sub">Your password has been updated.</p>
        <div style="background:var(--green-50);color:var(--green-700);border:1px solid #A6F4C5;border-radius:var(--radius-md);padding:12px 14px;font-size:13px;font-weight:500;margin-bottom:20px">
            <?= htmlspecialchars($info) ?>
        </div>
        <a href="<?= BASE_URL ?>/index.php" class="login-btn" style="display:flex;align-items:center;justify-content:center;text-decoration:none">Back to Sign In</a>

    <?php elseif ($step === 'email' || (!isset($_SESSION['fp_admin_id']) && $step !== 'done')): ?>

        <h1 class="login-title">Forgot Password</h1>
        <p class="login-sub">Enter your admin email to continue</p>

        <?php if ($error): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="step" value="email">
            <label class="login-label" for="email">Email Address</label>
            <div class="login-input-wrap">
                <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                </svg>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="admin@piatmove.gov.ph" required autofocus>
            </div>
            <button type="submit" class="login-btn">Continue</button>
        </form>

        <p style="text-align:center;margin-top:18px;font-size:13px">
            <a href="<?= BASE_URL ?>/index.php" style="color:var(--text-link);text-decoration:none">← Back to Sign In</a>
        </p>

    <?php elseif ($step === 'question'): ?>

        <h1 class="login-title">Security Question</h1>
        <p class="login-sub">Answer the question below to verify your identity</p>

        <?php if ($error): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div style="background:var(--gray-50);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:14px 16px;font-size:14px;color:var(--text-strong);font-weight:600;margin-bottom:20px">
            <?= htmlspecialchars($_SESSION['fp_question']) ?>
        </div>

        <form method="POST">
            <input type="hidden" name="step" value="question">
            <label class="login-label" for="security_answer">Your Answer</label>
            <div class="login-input-wrap" style="margin-bottom:0">
                <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <input type="text" id="security_answer" name="security_answer"
                       placeholder="Type your answer" autocomplete="off" required autofocus>
            </div>
            <button type="submit" class="login-btn" style="margin-top:16px">Verify Answer</button>
        </form>

        <p style="text-align:center;margin-top:18px;font-size:13px">
            <a href="<?= BASE_URL ?>/forgot-password.php" style="color:var(--text-link);text-decoration:none">← Start over</a>
        </p>

    <?php elseif ($step === 'reset'): ?>

        <h1 class="login-title">Set New Password</h1>
        <p class="login-sub">Choose a strong new password</p>

        <?php if ($error): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="step" value="reset">

            <label class="login-label" for="new_password">New Password</label>
            <div class="login-input-wrap">
                <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" id="new_password" name="new_password"
                       placeholder="Min. 8 characters" required autofocus>
                <button type="button" class="pw-toggle" tabindex="-1" id="toggleNew">
                    <svg class="eye-show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-hide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>

            <label class="login-label" for="confirm_password">Confirm Password</label>
            <div class="login-input-wrap" style="margin-bottom:0">
                <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat new password" required>
                <button type="button" class="pw-toggle" tabindex="-1" id="toggleConfirm">
                    <svg class="eye-show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-hide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>

            <button type="submit" class="login-btn" style="margin-top:16px">Reset Password</button>
        </form>

    <?php endif; ?>

    <p class="login-trust" style="margin-top:24px">Secured by the Municipality of Piat &middot; Authorized personnel only</p>
</div>

<script>
document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var wrap = btn.closest('.login-input-wrap');
        var inp  = wrap.querySelector('input');
        var visible = inp.type === 'text';
        inp.type = visible ? 'password' : 'text';
        btn.querySelector('.eye-show').style.display = visible ? '' : 'none';
        btn.querySelector('.eye-hide').style.display = visible ? 'none' : '';
    });
});
</script>
</body>
</html>
