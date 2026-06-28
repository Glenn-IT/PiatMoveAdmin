<?php require_once __DIR__ . '/components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$db  = get_db();
$msg = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id     = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id) {
        if ($action === 'activate') {
            $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
            $msg = 'User activated successfully.';
        } elseif ($action === 'deactivate') {
            $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?")->execute([$id]);
            $msg = 'User deactivated.';
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            $msg = 'User deleted.';
        }
    }
}

$search = trim($_GET['q'] ?? '');
$role   = $_GET['role'] ?? '';

$where  = [];
$params = [];

if ($search) {
    $where[]  = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (in_array($role, ['passenger', 'driver'])) {
    $where[]  = 'u.role = ?';
    $params[] = $role;
}

$sql = "SELECT u.*, d.approval_status
        FROM users u
        LEFT JOIN driver_info d ON d.user_id = u.id"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY u.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$page_title = 'Users';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert-ds alert-<?= htmlspecialchars($msg_type) ?>" id="flash-msg">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="panel" style="margin-bottom:20px">
    <div style="padding:18px 22px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="text" name="q" class="form-input" style="width:280px"
                   placeholder="Search name, email, phone…"
                   value="<?= htmlspecialchars($search) ?>">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="passenger" <?= $role === 'passenger' ? 'selected' : '' ?>>Passenger</option>
                <option value="driver"    <?= $role === 'driver'    ? 'selected' : '' ?>>Driver</option>
            </select>
            <button type="submit" class="btn-primary-ds">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Search
            </button>
            <?php if ($search || $role): ?>
                <a href="<?= BASE_URL ?>/users.php" class="btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span class="panel-title">All Users</span>
        <span class="badge badge-neutral"><?= count($users) ?> results</span>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="cell-strong"><?= htmlspecialchars($u['name']) ?></div>
                        <div class="cell-sub text-mono" style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td>
                        <?php if ($u['role'] === 'driver'): ?>
                            <span class="badge badge-warning">Driver</span>
                        <?php else: ?>
                            <span class="badge badge-primary">Passenger</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['status'] === 'active'): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="text-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></span></td>
                    <td>
                        <div class="actions-cell">
                            <form method="POST" style="display:contents">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <?php if ($u['status'] === 'active'): ?>
                                    <button name="action" value="deactivate" class="btn-neutral-sm"
                                            onclick="return confirm('Deactivate this user?')">Deactivate</button>
                                <?php else: ?>
                                    <button name="action" value="activate" class="btn-success-sm">Activate</button>
                                <?php endif; ?>
                                <button name="action" value="delete" class="btn-danger-ghost"
                                        onclick="return confirm('Permanently delete this user? This cannot be undone.')">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr><td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted)">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('flash-msg') && setTimeout(() => {
    document.getElementById('flash-msg').style.opacity = '0';
    document.getElementById('flash-msg').style.transition = 'opacity .4s';
}, 3000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
