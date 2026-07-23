<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$db = get_db();

$filter  = $_GET['status'] ?? 'all';
$allowed = ['all','pending','accepted','started','completed','cancelled','rejected'];
if (!in_array($filter, $allowed)) $filter = 'all';

$base_sql = "SELECT b.*,
                    p.name  AS passenger_name,
                    p.phone AS passenger_phone,
                    d.name  AS driver_name,
                    d.phone AS driver_phone
             FROM bookings b
             JOIN users p  ON p.id = b.passenger_id
             LEFT JOIN users d ON d.id = b.driver_id";

if ($filter !== 'all') {
    $stmt = $db->prepare($base_sql . " WHERE b.status = ? ORDER BY b.created_at DESC");
    $stmt->execute([$filter]);
} else {
    $stmt = $db->query($base_sql . " ORDER BY b.created_at DESC");
}
$bookings = $stmt->fetchAll();

$counts_raw = $db->query(
    "SELECT status, COUNT(*) AS cnt FROM bookings GROUP BY status"
)->fetchAll();
$counts = ['all' => 0];
foreach ($counts_raw as $r) {
    $counts[$r['status']] = (int)$r['cnt'];
    $counts['all'] += (int)$r['cnt'];
}

$badge_map = [
    'pending'   => 'neutral',
    'accepted'  => 'primary',
    'started'   => 'info',
    'completed' => 'success',
    'cancelled' => 'danger',
    'rejected'  => 'warning',
];

$page_title = 'Bookings';
require_once __DIR__ . '/includes/header.php';
?>

<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php
    $tabs = ['all'=>'All','pending'=>'Pending','accepted'=>'Accepted','started'=>'Started','completed'=>'Completed','cancelled'=>'Cancelled','rejected'=>'Rejected'];
    foreach ($tabs as $key => $label):
        $cnt = $counts[$key] ?? 0;
    ?>
        <a href="?status=<?= $key ?>" class="filter-pill <?= $filter === $key ? 'active' : '' ?>">
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
        <span class="panel-title">Bookings</span>
        <span class="badge badge-neutral"><?= count($bookings) ?> results</span>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                    <th>Pickup</th>
                    <th>Dropoff</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><span class="cell-mono">BK-<?= str_pad($b['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                    <td>
                        <div class="cell-strong"><?= htmlspecialchars($b['passenger_name']) ?></div>
                        <div class="cell-sub"><?= htmlspecialchars($b['passenger_phone']) ?></div>
                    </td>
                    <td>
                        <?php if ($b['driver_name']): ?>
                            <div class="cell-strong"><?= htmlspecialchars($b['driver_name']) ?></div>
                            <div class="cell-sub"><?= htmlspecialchars($b['driver_phone']) ?></div>
                        <?php else: ?>
                            <span class="text-subtle">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="max-width:180px">
                        <span style="font-size:13px;color:var(--text-body)"><?= htmlspecialchars($b['pickup_address']) ?></span>
                    </td>
                    <td style="max-width:180px">
                        <span style="font-size:13px;color:var(--text-body)"><?= htmlspecialchars($b['dropoff_address']) ?></span>
                    </td>
                    <td>
                        <?php
                        $cls = $badge_map[$b['status']] ?? 'neutral';
                        echo "<span class=\"badge badge-{$cls}\">" . ucfirst($b['status']) . "</span>";
                        ?>
                    </td>
                    <td><span class="text-muted" style="font-size:12px"><?= date('M j, Y g:i A', strtotime($b['created_at'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                <tr><td colspan="7" style="text-align:center;padding:48px;color:var(--text-muted)">No bookings found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
