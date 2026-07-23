<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$db  = get_db();
$msg = '';
$msg_type = 'success';

if (!empty($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

$create_errors = [];
$open_modal = false;
$form = ['name' => '', 'email' => '', 'phone' => '', 'role' => 'passenger',
          'license_no' => '', 'vehicle_no' => '', 'vehicle_type' => '', 'barangay' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $open_modal = true;
        $form['name']  = trim($_POST['name'] ?? '');
        $form['email'] = trim($_POST['email'] ?? '');
        $form['phone'] = trim($_POST['phone'] ?? '');
        $form['role']  = in_array($_POST['role'] ?? '', ['passenger', 'driver']) ? $_POST['role'] : 'passenger';
        $password      = $_POST['password'] ?? '';

        if ($form['role'] === 'driver') {
            $form['license_no']   = trim($_POST['license_no'] ?? '');
            $form['vehicle_no']   = trim($_POST['vehicle_no'] ?? '');
            $form['vehicle_type'] = 'Tricycle';
            $form['barangay']     = trim($_POST['barangay'] ?? '');
        }

        if ($form['name'] === '') $create_errors['name'] = 'Name is required.';
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $create_errors['email'] = 'A valid email is required.';
        if (!preg_match('/^\d{11}$/', $form['phone'])) $create_errors['phone'] = 'Phone number must be exactly 11 digits (e.g. 09171234567).';
        if (strlen($password) < 6) $create_errors['password'] = 'Password must be at least 6 characters.';

        if ($form['role'] === 'driver') {
            if ($form['license_no'] === '') $create_errors['license_no'] = 'License number is required.';
            if ($form['vehicle_no'] === '')  $create_errors['vehicle_no'] = 'Vehicle number is required.';
            if ($form['barangay'] === '')    $create_errors['barangay']  = 'Barangay is required.';
        }

        if (!$create_errors) {
            $exists = $db->prepare('SELECT id FROM users WHERE email = ?');
            $exists->execute([$form['email']]);
            if ($exists->fetch()) {
                $create_errors['email'] = 'A user with this email already exists.';
            }
        }

        if (!$create_errors) {
            $db->beginTransaction();
            $db->prepare('INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, ?)')
               ->execute([
                    $form['name'],
                    $form['email'],
                    password_hash($password, PASSWORD_DEFAULT),
                    $form['phone'],
                    $form['role'],
                    'active',
               ]);
            $newUserId = (int)$db->lastInsertId();

            if ($form['role'] === 'driver') {
                $db->prepare('INSERT INTO driver_info (user_id, license_no, vehicle_no, vehicle_type, barangay, approval_status) VALUES (?, ?, ?, ?, ?, ?)')
                   ->execute([$newUserId, $form['license_no'], $form['vehicle_no'], $form['vehicle_type'], $form['barangay'], 'approved']);
            }
            $db->commit();

            $_SESSION['flash_msg'] = ucfirst($form['role']) . ' "' . $form['name'] . '" added successfully.';
            header('Location: ' . BASE_URL . '/users.php');
            exit;
        }
    } else {
        $id = (int)($_POST['user_id'] ?? 0);
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
            <button type="button" class="btn-primary-ds" style="margin-left:auto" onclick="openAddUserModal()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add User
            </button>
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

<div class="modal-overlay<?= $open_modal ? ' open' : '' ?>" id="addUserOverlay">
    <div class="modal-box">
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="modal-header">
                <span class="modal-title">Add User</span>
                <button type="button" class="modal-close" onclick="closeAddUserModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <label class="form-label" for="au-role">Role</label>
                    <select name="role" id="au-role" class="form-select" style="width:100%" onchange="toggleDriverFields()">
                        <option value="passenger" <?= $form['role'] === 'passenger' ? 'selected' : '' ?>>Passenger</option>
                        <option value="driver" <?= $form['role'] === 'driver' ? 'selected' : '' ?>>Driver</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="au-name">Full Name</label>
                    <input type="text" name="name" id="au-name" class="form-input" value="<?= htmlspecialchars($form['name']) ?>" required>
                    <?php if (!empty($create_errors['name'])): ?><div class="form-error"><?= htmlspecialchars($create_errors['name']) ?></div><?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="au-email">Email</label>
                    <input type="email" name="email" id="au-email" class="form-input" value="<?= htmlspecialchars($form['email']) ?>" required>
                    <?php if (!empty($create_errors['email'])): ?><div class="form-error"><?= htmlspecialchars($create_errors['email']) ?></div><?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="au-phone">Phone</label>
                    <input type="text" name="phone" id="au-phone" class="form-input"
                           value="<?= htmlspecialchars($form['phone']) ?>"
                           inputmode="numeric" pattern="\d{11}" maxlength="11"
                           placeholder="09171234567" autocomplete="off" required>
                    <?php if (!empty($create_errors['phone'])): ?><div class="form-error"><?= htmlspecialchars($create_errors['phone']) ?></div><?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="au-password">Password</label>
                    <input type="password" name="password" id="au-password" class="form-input" placeholder="Min. 6 characters" required>
                    <?php if (!empty($create_errors['password'])): ?><div class="form-error"><?= htmlspecialchars($create_errors['password']) ?></div><?php endif; ?>
                </div>

                <div id="driverFields" style="display:<?= $form['role'] === 'driver' ? 'flex' : 'none' ?>;flex-direction:column;gap:14px">
                    <div>
                        <label class="form-label" for="au-license">License No.</label>
                        <input type="text" name="license_no" id="au-license" class="form-input" value="<?= htmlspecialchars($form['license_no']) ?>">
                        <?php if (!empty($create_errors['license_no'])): ?><div class="form-error"><?= htmlspecialchars($create_errors['license_no']) ?></div><?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="au-vehicle-no">Vehicle No.</label>
                        <input type="text" name="vehicle_no" id="au-vehicle-no" class="form-input" value="<?= htmlspecialchars($form['vehicle_no']) ?>">
                        <?php if (!empty($create_errors['vehicle_no'])): ?><div class="form-error"><?= htmlspecialchars($create_errors['vehicle_no']) ?></div><?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="au-vehicle-type">Vehicle Type</label>
                        <input type="text" id="au-vehicle-type" class="form-input" value="Tricycle" disabled>
                        <input type="hidden" name="vehicle_type" value="Tricycle">
                    </div>
                    <div>
                        <label class="form-label" for="au-barangay">Barangay</label>
                        <select name="barangay" id="au-barangay" class="form-select" style="width:100%">
                            <option value="" disabled <?= $form['barangay'] === '' ? 'selected' : '' ?>>Select Barangay</option>
                            <?php foreach ([
                                'Apayao', 'Aquib', 'Baung', 'Calaoagan', 'Catarauan', 'Dugayung',
                                'Gumarueng', 'Macapil', 'Maguilling', 'Minanga', 'Poblacion I',
                                'Poblacion II', 'Santa Barbara', 'Santo Domingo', 'Sicatna',
                                'Villa Rey (San Gaspar)', 'Villa Reyno', 'Warat',
                            ] as $brgy): ?>
                                <option value="<?= htmlspecialchars($brgy) ?>" <?= $form['barangay'] === $brgy ? 'selected' : '' ?>><?= htmlspecialchars($brgy) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($create_errors['barangay'])): ?><div class="form-error"><?= htmlspecialchars($create_errors['barangay']) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn-primary-ds">Add User</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('flash-msg') && setTimeout(() => {
    document.getElementById('flash-msg').style.opacity = '0';
    document.getElementById('flash-msg').style.transition = 'opacity .4s';
}, 3000);

function openAddUserModal() {
    document.getElementById('addUserOverlay').classList.add('open');
}
function closeAddUserModal() {
    document.getElementById('addUserOverlay').classList.remove('open');
}
function toggleDriverFields() {
    var isDriver = document.getElementById('au-role').value === 'driver';
    document.getElementById('driverFields').style.display = isDriver ? 'flex' : 'none';
}

var auPhone = document.getElementById('au-phone');
if (auPhone) {
    auPhone.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
