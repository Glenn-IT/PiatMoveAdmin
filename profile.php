<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$db    = get_db();
$admin = $db->prepare("SELECT * FROM admins WHERE id = ?");
$admin->execute([$_SESSION['admin_id']]);
$admin = $admin->fetch();

$msg      = '';
$msg_type = 'success';

$security_questions = [
    "What is your mother's maiden name?",
    "What was the name of your first pet?",
    "What city were you born in?",
    "What is the name of your elementary school?",
    "What was your childhood nickname?",
    "What is the name of the street you grew up on?",
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $section = $_POST['section'] ?? '';

    if ($section === 'info') {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!$name || !$email) {
            $msg      = 'Name and email are required.';
            $msg_type = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg      = 'Please enter a valid email address.';
            $msg_type = 'danger';
        } else {
            $check = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $check->execute([$email, $_SESSION['admin_id']]);
            if ($check->fetch()) {
                $msg      = 'That email is already in use by another account.';
                $msg_type = 'danger';
            } else {
                $db->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?")
                   ->execute([$name, $email, $_SESSION['admin_id']]);
                $_SESSION['admin_name'] = $name;
                $msg = 'Profile updated successfully.';
                $admin['name']  = $name;
                $admin['email'] = $email;
            }
        }

    } elseif ($section === 'password') {
        $current  = $_POST['current_password']  ?? '';
        $new_pw   = $_POST['new_password']       ?? '';
        $confirm  = $_POST['confirm_password']   ?? '';

        if (!$current || !$new_pw || !$confirm) {
            $msg      = 'All password fields are required.';
            $msg_type = 'danger';
        } elseif (!password_verify($current, $admin['password'])) {
            $msg      = 'Current password is incorrect.';
            $msg_type = 'danger';
        } elseif (strlen($new_pw) < 8) {
            $msg      = 'New password must be at least 8 characters.';
            $msg_type = 'danger';
        } elseif ($new_pw !== $confirm) {
            $msg      = 'New passwords do not match.';
            $msg_type = 'danger';
        } else {
            $hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $db->prepare("UPDATE admins SET password = ? WHERE id = ?")
               ->execute([$hash, $_SESSION['admin_id']]);
            $admin['password'] = $hash;
            $msg = 'Password changed successfully.';
        }

    } elseif ($section === 'security') {
        $question = $_POST['security_question'] ?? '';
        $answer   = trim($_POST['security_answer'] ?? '');

        if (!in_array($question, $security_questions)) {
            $msg      = 'Please select a valid security question.';
            $msg_type = 'danger';
        } elseif (strlen($answer) < 2) {
            $msg      = 'Security answer is too short.';
            $msg_type = 'danger';
        } else {
            $hashed_answer = password_hash(strtolower($answer), PASSWORD_DEFAULT);
            $db->prepare("UPDATE admins SET security_question = ?, security_answer = ? WHERE id = ?")
               ->execute([$question, $hashed_answer, $_SESSION['admin_id']]);
            $admin['security_question'] = $question;
            $msg = 'Security question saved successfully.';
        }
    }
}

$page_title = 'Profile';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert-ds alert-<?= htmlspecialchars($msg_type) ?>" id="flash-msg">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <?php if ($msg_type === 'success'): ?>
                <polyline points="20 6 9 17 4 12"/>
            <?php else: ?>
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            <?php endif; ?>
        </svg>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div style="max-width:680px;display:flex;flex-direction:column;gap:24px">

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Account Information</span>
        </div>
        <form method="POST" style="padding:24px;display:flex;flex-direction:column;gap:16px">
            <?= csrf_field() ?>
            <input type="hidden" name="section" value="info">

            <div>
                <label class="profile-label">Full Name</label>
                <input type="text" name="name" class="form-input"
                       value="<?= htmlspecialchars($admin['name']) ?>" required>
            </div>
            <div>
                <label class="profile-label">Email Address</label>
                <input type="email" name="email" class="form-input"
                       value="<?= htmlspecialchars($admin['email']) ?>" required>
            </div>
            <div>
                <button type="submit" class="btn-primary-ds">Save Changes</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Change Password</span>
        </div>
        <form method="POST" style="padding:24px;display:flex;flex-direction:column;gap:16px">
            <?= csrf_field() ?>
            <input type="hidden" name="section" value="password">

            <div>
                <label class="profile-label">Current Password</label>
                <div class="profile-pw-wrap">
                    <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
                    <button type="button" class="pw-toggle-inline" tabindex="-1">
                        <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="profile-label">New Password <span style="color:var(--text-muted);font-weight:400">(min. 8 characters)</span></label>
                <div class="profile-pw-wrap">
                    <input type="password" name="new_password" class="form-input" placeholder="New password" required>
                    <button type="button" class="pw-toggle-inline" tabindex="-1">
                        <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="profile-label">Confirm New Password</label>
                <div class="profile-pw-wrap">
                    <input type="password" name="confirm_password" class="form-input" placeholder="Repeat new password" required>
                    <button type="button" class="pw-toggle-inline" tabindex="-1">
                        <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <button type="submit" class="btn-primary-ds">Change Password</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Security Question</span>
            <?php if ($admin['security_question']): ?>
                <span class="badge badge-success">Set</span>
            <?php else: ?>
                <span class="badge badge-warning">Not set</span>
            <?php endif; ?>
        </div>
        <form method="POST" style="padding:24px;display:flex;flex-direction:column;gap:16px">
            <?= csrf_field() ?>
            <input type="hidden" name="section" value="security">

            <p style="font-size:13px;color:var(--text-muted);margin:0">
                This question is used to verify your identity when you forget your password.
            </p>

            <div>
                <label class="profile-label">Security Question</label>
                <select name="security_question" class="form-select" style="width:100%" required>
                    <option value="">— Select a question —</option>
                    <?php foreach ($security_questions as $q): ?>
                        <option value="<?= htmlspecialchars($q) ?>"
                            <?= ($admin['security_question'] === $q) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="profile-label">Your Answer</label>
                <input type="text" name="security_answer" class="form-input"
                       placeholder="<?= $admin['security_question'] ? 'Enter new answer to update' : 'Type your answer' ?>"
                       autocomplete="off" required>
                <p style="font-size:11px;color:var(--text-muted);margin-top:6px">Answer is case-insensitive.</p>
            </div>
            <div>
                <button type="submit" class="btn-primary-ds">Save Security Question</button>
            </div>
        </form>
    </div>

</div>

<script>
document.getElementById('flash-msg') && setTimeout(function () {
    var el = document.getElementById('flash-msg');
    el.style.transition = 'opacity .4s';
    el.style.opacity = '0';
}, 3000);

document.querySelectorAll('.pw-toggle-inline').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var inp = btn.closest('.profile-pw-wrap').querySelector('input');
        var visible = inp.type === 'text';
        inp.type = visible ? 'password' : 'text';
        btn.querySelector('.eye-show').style.display = visible ? '' : 'none';
        btn.querySelector('.eye-hide').style.display = visible ? 'none' : '';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
