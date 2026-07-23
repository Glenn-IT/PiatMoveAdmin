<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/charts.php';

$db = get_db();

$range = $_GET['range'] ?? 'month';
$allowed_ranges = ['today', 'week', 'month', '30days', 'all'];
if (!in_array($range, $allowed_ranges)) $range = 'month';

$today = date('Y-m-d');
switch ($range) {
    case 'today':   $from = $today; break;
    case 'week':    $from = date('Y-m-d', strtotime('monday this week')); break;
    case '30days':  $from = date('Y-m-d', strtotime('-29 days')); break;
    case 'all':     $from = '2000-01-01'; break;
    case 'month':
    default:        $from = date('Y-m-01'); break;
}
$from = $_GET['from'] ?? $from;
$to   = $_GET['to'] ?? $today;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = $today;

$fromDt = $from . ' 00:00:00';
$toDt   = $to . ' 23:59:59';

$base_sql = "SELECT b.*,
                    p.name  AS passenger_name,
                    p.phone AS passenger_phone,
                    d.name  AS driver_name,
                    d.phone AS driver_phone
             FROM bookings b
             JOIN users p  ON p.id = b.passenger_id
             LEFT JOIN users d ON d.id = b.driver_id
             WHERE b.created_at BETWEEN ? AND ?
             ORDER BY b.created_at DESC";

$stmt = $db->prepare($base_sql);
$stmt->execute([$fromDt, $toDt]);
$bookings = $stmt->fetchAll();

// ---- CSV export ----
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="bookings-report_' . $from . '_to_' . $to . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reference', 'Passenger', 'Passenger Phone', 'Driver', 'Driver Phone', 'Pickup', 'Dropoff', 'Status', 'Fare', 'Date']);
    foreach ($bookings as $b) {
        fputcsv($out, [
            'BK-' . str_pad($b['id'], 4, '0', STR_PAD_LEFT),
            $b['passenger_name'],
            $b['passenger_phone'],
            $b['driver_name'] ?? '',
            $b['driver_phone'] ?? '',
            $b['pickup_address'],
            $b['dropoff_address'],
            ucfirst($b['status']),
            $b['fare'] ?? '',
            $b['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

// ---- Summary stats for the selected range ----
$status_counts = ['pending' => 0, 'accepted' => 0, 'started' => 0, 'completed' => 0, 'cancelled' => 0, 'rejected' => 0];
$total_fare = 0;
foreach ($bookings as $b) {
    $status_counts[$b['status']] = ($status_counts[$b['status']] ?? 0) + 1;
    if ($b['status'] === 'completed' && $b['fare'] !== null) $total_fare += (float)$b['fare'];
}
$total_bookings = count($bookings);

// ---- Booking history (daily) within the selected range ----
$bh_rows = $db->prepare(
    "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM bookings
     WHERE created_at BETWEEN ? AND ?
     GROUP BY DATE(created_at)"
);
$bh_rows->execute([$fromDt, $toDt]);
$bh_rows = $bh_rows->fetchAll(PDO::FETCH_KEY_PAIR);

$booking_history = [];
$dayCursor = strtotime($from);
$dayEnd    = strtotime($to);
while ($dayCursor <= $dayEnd) {
    $d = date('Y-m-d', $dayCursor);
    $booking_history[$d] = (int)($bh_rows[$d] ?? 0);
    $dayCursor = strtotime('+1 day', $dayCursor);
}

// ---- Driver registrations within the selected range ----
$driver_stmt = $db->prepare(
    "SELECT approval_status, COUNT(*) AS c FROM driver_info
     WHERE created_at BETWEEN ? AND ?
     GROUP BY approval_status"
);
$driver_stmt->execute([$fromDt, $toDt]);
$driver_counts = $driver_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$new_approved = (int)($driver_counts['approved'] ?? 0);
$new_pending  = (int)($driver_counts['pending'] ?? 0);
$new_rejected = (int)($driver_counts['rejected'] ?? 0);
$new_drivers  = $new_approved + $new_pending + $new_rejected;

$barangay_stmt = $db->prepare(
    "SELECT barangay, COUNT(*) AS c FROM driver_info
     WHERE vehicle_type = 'Tricycle' AND barangay <> '' AND created_at BETWEEN ? AND ?
     GROUP BY barangay ORDER BY c DESC"
);
$barangay_stmt->execute([$fromDt, $toDt]);
$barangay_counts = $barangay_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$range_labels = ['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', '30days' => 'Last 30 Days', 'all' => 'All Time'];

$page_title = 'Reports';
require_once __DIR__ . '/includes/header.php';
?>

<div class="panel" style="margin-bottom:20px">
    <div style="padding:18px 22px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <?php foreach ($range_labels as $key => $label): ?>
                    <a href="?range=<?= $key ?>" class="filter-pill <?= $range === $key ? 'active' : '' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
            <span style="width:1px;height:24px;background:var(--border-subtle)"></span>
            <input type="date" name="from" class="form-input" style="width:150px" value="<?= htmlspecialchars($from) ?>">
            <span class="text-muted">to</span>
            <input type="date" name="to" class="form-input" style="width:150px" value="<?= htmlspecialchars($to) ?>">
            <button type="submit" class="btn-primary-ds">Apply</button>
            <a href="?range=<?= htmlspecialchars($range) ?>&from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>&export=1" class="btn-ghost">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value"><?= number_format($total_bookings) ?></div>
            <div class="stat-sub"><?= htmlspecialchars($from) ?> &ndash; <?= htmlspecialchars($to) ?></div>
        </div>
        <div class="stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Completed Rides</div>
            <div class="stat-value"><?= number_format($status_counts['completed']) ?></div>
            <div class="stat-sub"><?= $total_bookings > 0 ? round($status_counts['completed'] / $total_bookings * 100) : 0 ?>% of total</div>
        </div>
        <div class="stat-icon green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Cancelled / Rejected</div>
            <div class="stat-value"><?= number_format($status_counts['cancelled'] + $status_counts['rejected']) ?></div>
            <div class="stat-sub"><?= $status_counts['cancelled'] ?> cancelled &middot; <?= $status_counts['rejected'] ?> rejected</div>
        </div>
        <div class="stat-icon red">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Revenue (Completed)</div>
            <div class="stat-value">&#8369;<?= number_format($total_fare, 2) ?></div>
        </div>
        <div class="stat-icon cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:24px">
    <div class="panel-header">
        <span class="panel-title">Booking History</span>
        <span class="text-muted" style="font-size:12px"><?= htmlspecialchars($range_labels[$range]) ?></span>
    </div>
    <div class="chart-panel-body">
        <?= render_booking_history_chart($booking_history, 'Booking history for selected range') ?>
    </div>
</div>

<div class="charts-grid">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Bookings by Status</span>
        </div>
        <div class="chart-panel-body">
            <?php
            $status_colors = [
                'completed' => 'var(--green-500)', 'pending' => 'var(--gray-400)', 'accepted' => 'var(--blue-500)',
                'started' => 'var(--cyan-500)', 'cancelled' => 'var(--red-500)', 'rejected' => 'var(--amber-500)',
            ];
            $status_items = array_filter($status_counts, fn($c) => $c > 0);
            if (empty($status_items)):
            ?>
                <div class="chart-empty">No bookings in this range.</div>
            <?php else: foreach ($status_items as $status => $count): $pct = round($count / max($status_items) * 100, 1); ?>
                <div class="chart-bar-row">
                    <div class="chart-bar-label" style="width:170px">
                        <span class="chart-legend-dot" style="background:<?= $status_colors[$status] ?>"></span><?= ucfirst($status) ?>
                    </div>
                    <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= $pct ?>%;background:<?= $status_colors[$status] ?>"></div></div>
                    <div class="chart-bar-value"><?= number_format($count) ?></div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">New Tricycle Drivers per Barangay</span>
        </div>
        <div class="chart-panel-body">
            <?= render_bar_rows($barangay_counts, 'var(--blue-500)', 'No new drivers registered in this range.') ?>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:24px">
    <div class="panel-header">
        <span class="panel-title">New Driver Registrations</span>
        <span class="badge badge-neutral"><?= number_format($new_drivers) ?> total</span>
    </div>
    <div class="chart-panel-body">
        <?php
        $new_driver_items = ['Approved' => $new_approved, 'Pending' => $new_pending];
        if ($new_rejected > 0) $new_driver_items['Rejected'] = $new_rejected;
        $new_driver_colors = ['Approved' => 'var(--green-500)', 'Pending' => 'var(--amber-500)', 'Rejected' => 'var(--red-500)'];
        if ($new_drivers === 0):
        ?>
            <div class="chart-empty">No driver registrations in this range.</div>
        <?php else: foreach ($new_driver_items as $label => $count): $pct = round($count / max($new_driver_items) * 100, 1); ?>
            <div class="chart-bar-row">
                <div class="chart-bar-label" style="width:170px">
                    <span class="chart-legend-dot" style="background:<?= $new_driver_colors[$label] ?>"></span><?= $label ?>
                </div>
                <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= $pct ?>%;background:<?= $new_driver_colors[$label] ?>"></div></div>
                <div class="chart-bar-value"><?= number_format($count) ?></div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Bookings in Range</span>
        <span class="badge badge-neutral"><?= count($bookings) ?> results</span>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                    <th>Status</th>
                    <th>Fare</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><span class="cell-mono">BK-<?= str_pad($b['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                    <td><span class="cell-strong"><?= htmlspecialchars($b['passenger_name']) ?></span></td>
                    <td><?= $b['driver_name'] ? htmlspecialchars($b['driver_name']) : '<span class="text-subtle">&mdash;</span>' ?></td>
                    <td><?= status_badge($b['status']) ?></td>
                    <td><?= $b['fare'] !== null ? '&#8369;' . number_format($b['fare'], 2) : '<span class="text-subtle">&mdash;</span>' ?></td>
                    <td><span class="text-muted" style="font-size:12px"><?= date('M j, Y g:i A', strtotime($b['created_at'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                <tr><td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted)">No bookings found in this range</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
