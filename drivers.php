<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$db  = get_db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $user_id = (int)($_POST['user_id'] ?? 0);
    $action  = $_POST['action'] ?? '';

    if ($user_id && in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $db->prepare("UPDATE driver_info SET approval_status = ? WHERE user_id = ?")
           ->execute([$status, $user_id]);
        $msg = 'Driver ' . $status . '.';
    }
}

$filter  = $_GET['filter'] ?? 'all';
$allowed = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($filter, $allowed)) $filter = 'all';

$base_sql = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                    d.license_no, d.vehicle_no, d.vehicle_type, d.approval_status, d.is_online
             FROM users u
             JOIN driver_info d ON d.user_id = u.id";

if ($filter !== 'all') {
    $stmt = $db->prepare($base_sql . " WHERE d.approval_status = ? ORDER BY FIELD(d.approval_status,'pending','approved','rejected'), u.created_at DESC");
    $stmt->execute([$filter]);
} else {
    $stmt = $db->query($base_sql . " ORDER BY FIELD(d.approval_status,'pending','approved','rejected'), u.created_at DESC");
}
$drivers = $stmt->fetchAll();

$counts_raw = $db->query(
    "SELECT approval_status, COUNT(*) AS cnt FROM driver_info GROUP BY approval_status"
)->fetchAll();
$counts = ['all' => 0];
foreach ($counts_raw as $r) {
    $counts[$r['approval_status']] = (int)$r['cnt'];
    $counts['all'] += (int)$r['cnt'];
}

$page_title = 'Drivers';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert-ds alert-success" id="flash-msg">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Filter Pills -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php
    $tabs = ['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'];
    foreach ($tabs as $key => $label):
        $cnt = $counts[$key] ?? 0;
    ?>
        <a href="?filter=<?= $key ?>" class="filter-pill <?= $filter === $key ? 'active' : '' ?>">
            <?= $label ?>
            <?php if ($cnt > 0): ?>
                <span style="background:<?= $filter === $key ? 'rgba(255,255,255,.25)' : 'var(--gray-100)' ?>;color:<?= $filter === $key ? '#fff' : 'var(--text-muted)' ?>;padding:1px 7px;border-radius:var(--radius-pill);font-size:11px;font-weight:700">
                    <?= $cnt ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Drivers</span>
        <span class="badge badge-neutral"><?= count($drivers) ?> results</span>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Phone</th>
                    <th>License No.</th>
                    <th>Vehicle</th>
                    <th>Online</th>
                    <th>Approval</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drivers as $d): ?>
                <tr>
                    <td>
                        <div class="cell-strong"><?= htmlspecialchars($d['name']) ?></div>
                        <div class="cell-sub" style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($d['email']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($d['phone']) ?></td>
                    <td><span class="cell-mono"><?= htmlspecialchars($d['license_no']) ?></span></td>
                    <td>
                        <div class="cell-strong"><?= htmlspecialchars($d['vehicle_no']) ?></div>
                        <div class="cell-sub"><?= htmlspecialchars($d['vehicle_type']) ?></div>
                    </td>
                    <td>
                        <?php if ($d['is_online']): ?>
                            <span class="badge badge-success">Online</span>
                        <?php else: ?>
                            <span class="badge badge-neutral">Offline</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $ap_map = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
                        $cls = $ap_map[$d['approval_status']] ?? 'neutral';
                        echo "<span class=\"badge badge-{$cls}\">" . ucfirst($d['approval_status']) . "</span>";
                        ?>
                    </td>
                    <td>
                        <div class="actions-cell">
                            <form method="POST" style="display:contents">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= $d['id'] ?>">
                                <?php if ($d['approval_status'] === 'pending'): ?>
                                    <button name="action" value="approve" class="btn-success-sm"
                                            onclick="return confirm('Approve this driver?')">Approve</button>
                                    <button name="action" value="reject" class="btn-danger-ghost"
                                            onclick="return confirm('Reject this driver?')">Reject</button>
                                <?php elseif ($d['approval_status'] === 'approved'): ?>
                                    <button name="action" value="reject" class="btn-danger-ghost"
                                            onclick="return confirm('Revoke this driver\'s approval?')">Revoke</button>
                                <?php else: ?>
                                    <button name="action" value="approve" class="btn-neutral-sm"
                                            onclick="return confirm('Re-approve this driver?')">Re-approve</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($drivers)): ?>
                <tr><td colspan="7" style="text-align:center;padding:48px;color:var(--text-muted)">No drivers found</td></tr>
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
