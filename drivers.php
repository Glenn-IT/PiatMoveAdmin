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

$search = trim($_GET['q'] ?? '');

$base_sql = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                    d.license_no, d.vehicle_no, d.vehicle_type, d.barangay, d.approval_status, d.is_online,
                    d.plate_proof_path, d.license_proof_path, d.photo_path, d.tricycle_photo_path
             FROM users u
             JOIN driver_info d ON d.user_id = u.id";

$where  = [];
$params = [];

if ($filter !== 'all') {
    $where[]  = 'd.approval_status = ?';
    $params[] = $filter;
}
if ($search) {
    $where[]  = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR d.license_no LIKE ? OR d.vehicle_no LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = $base_sql
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY FIELD(d.approval_status,'pending','approved','rejected'), u.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
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

<div class="panel" style="margin-bottom:20px">
    <div style="padding:18px 22px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <input type="text" name="q" class="form-input" style="width:280px"
                   placeholder="Search name, email, phone, license, vehicle…"
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-primary-ds">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Search
            </button>
            <?php if ($search): ?>
                <a href="?filter=<?= urlencode($filter) ?>" class="btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php
    $tabs = ['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'];
    foreach ($tabs as $key => $label):
        $cnt = $counts[$key] ?? 0;
        $qs  = 'filter=' . $key . ($search ? '&q=' . urlencode($search) : '');
    ?>
        <a href="?<?= $qs ?>" class="filter-pill <?= $filter === $key ? 'active' : '' ?>">
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
                    <th>Documents</th>
                    <th>Online</th>
                    <th>Approval</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $doc_labels = [
                    'plate_proof_path'   => 'Plate Proof',
                    'license_proof_path' => 'License Proof',
                    'photo_path'         => 'Driver Photo',
                    'tricycle_photo_path' => 'Tricycle Photo',
                ];
                $image_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                ?>
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
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php
                            $hasDocs = false;
                            foreach ($doc_labels as $col => $label):
                                $path = $d[$col] ?? null;
                                if (!$path) continue;
                                $hasDocs = true;
                                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                $url = BASE_URL . '/' . ltrim($path, '/');
                            ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener" title="<?= htmlspecialchars($label) ?>">
                                    <?php if (in_array($ext, $image_ext, true)): ?>
                                        <img src="<?= htmlspecialchars($url) ?>" alt="<?= htmlspecialchars($label) ?>" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--gray-100)">
                                    <?php else: ?>
                                        <span class="badge badge-neutral"><?= htmlspecialchars($label) ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                            <?php if (!$hasDocs): ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </div>
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
                            <button type="button" class="btn-neutral-sm view-driver-btn"
                                    data-name="<?= htmlspecialchars($d['name']) ?>"
                                    data-email="<?= htmlspecialchars($d['email']) ?>"
                                    data-phone="<?= htmlspecialchars($d['phone']) ?>"
                                    data-status="<?= htmlspecialchars(ucfirst($d['status'])) ?>"
                                    data-license="<?= htmlspecialchars($d['license_no']) ?>"
                                    data-vehicle="<?= htmlspecialchars($d['vehicle_no']) ?>"
                                    data-vehicle-type="<?= htmlspecialchars($d['vehicle_type']) ?>"
                                    data-barangay="<?= htmlspecialchars($d['barangay']) ?>"
                                    data-online="<?= $d['is_online'] ? 'Online' : 'Offline' ?>"
                                    data-approval="<?= htmlspecialchars(ucfirst($d['approval_status'])) ?>"
                                    data-joined="<?= htmlspecialchars(date('M j, Y', strtotime($d['created_at']))) ?>"
                                    data-plate-proof="<?= $d['plate_proof_path'] ? htmlspecialchars(BASE_URL . '/' . ltrim($d['plate_proof_path'], '/')) : '' ?>"
                                    data-license-proof="<?= $d['license_proof_path'] ? htmlspecialchars(BASE_URL . '/' . ltrim($d['license_proof_path'], '/')) : '' ?>"
                                    data-photo="<?= $d['photo_path'] ? htmlspecialchars(BASE_URL . '/' . ltrim($d['photo_path'], '/')) : '' ?>"
                                    data-tricycle-photo="<?= $d['tricycle_photo_path'] ? htmlspecialchars(BASE_URL . '/' . ltrim($d['tricycle_photo_path'], '/')) : '' ?>"
                                    onclick="openDriverModal(this)">View</button>
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
                <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--text-muted)">No drivers found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="driverDetailOverlay">
    <div class="modal-box" style="max-width:640px">
        <div class="modal-header">
            <span class="modal-title">Driver Details</span>
            <button type="button" class="modal-close" onclick="closeDriverModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="driver-detail-grid">
                <div class="driver-detail-item">
                    <span class="form-label">Name</span>
                    <div class="driver-detail-value" id="dd-name"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Account Status</span>
                    <div class="driver-detail-value" id="dd-status"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Email</span>
                    <div class="driver-detail-value" id="dd-email"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Phone</span>
                    <div class="driver-detail-value" id="dd-phone"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">License No.</span>
                    <div class="driver-detail-value" id="dd-license"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Plate Number</span>
                    <div class="driver-detail-value" id="dd-vehicle"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Vehicle Type</span>
                    <div class="driver-detail-value" id="dd-vehicle-type"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Barangay</span>
                    <div class="driver-detail-value" id="dd-barangay"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Online Status</span>
                    <div class="driver-detail-value" id="dd-online"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Approval</span>
                    <div class="driver-detail-value" id="dd-approval"></div>
                </div>
                <div class="driver-detail-item">
                    <span class="form-label">Joined</span>
                    <div class="driver-detail-value" id="dd-joined"></div>
                </div>
            </div>

            <div>
                <span class="form-label" style="margin-top:6px">Verification Documents</span>
                <div class="driver-detail-grid" id="dd-docs" style="grid-template-columns:repeat(3,1fr)"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-ghost" onclick="closeDriverModal()">Close</button>
        </div>
    </div>
</div>

<script>
document.getElementById('flash-msg') && setTimeout(() => {
    document.getElementById('flash-msg').style.opacity = '0';
    document.getElementById('flash-msg').style.transition = 'opacity .4s';
}, 3000);

function openDriverModal(btn) {
    var d = btn.dataset;
    document.getElementById('dd-name').textContent = d.name;
    document.getElementById('dd-status').textContent = d.status;
    document.getElementById('dd-email').textContent = d.email;
    document.getElementById('dd-phone').textContent = d.phone;
    document.getElementById('dd-license').textContent = d.license;
    document.getElementById('dd-vehicle').textContent = d.vehicle;
    document.getElementById('dd-vehicle-type').textContent = d.vehicleType;
    document.getElementById('dd-barangay').textContent = d.barangay;
    document.getElementById('dd-online').textContent = d.online;
    document.getElementById('dd-approval').textContent = d.approval;
    document.getElementById('dd-joined').textContent = d.joined;

    var docs = [
        ['Plate Proof', d.plateProof],
        ['License Proof', d.licenseProof],
        ['Driver Photo', d.photo],
        ['Tricycle Photo', d.tricyclePhoto],
    ];
    var imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    var docsEl = document.getElementById('dd-docs');
    docsEl.innerHTML = '';
    docs.forEach(function (doc) {
        var label = doc[0], url = doc[1];
        var card = document.createElement('div');
        card.className = 'driver-doc-card';
        if (!url) {
            card.innerHTML = '<span class="form-label">' + label + '</span><span class="text-muted">Not provided</span>';
            docsEl.appendChild(card);
            return;
        }
        var ext = url.split('.').pop().toLowerCase();
        var preview = imageExt.indexOf(ext) !== -1
            ? '<img src="' + url + '" alt="' + label + '">'
            : '';
        card.innerHTML = '<span class="form-label">' + label + '</span>' + preview +
            '<a href="' + url + '" target="_blank" rel="noopener" class="btn-ghost">View Full</a>';
        docsEl.appendChild(card);
    });

    document.getElementById('driverDetailOverlay').classList.add('open');
}

function closeDriverModal() {
    document.getElementById('driverDetailOverlay').classList.remove('open');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
