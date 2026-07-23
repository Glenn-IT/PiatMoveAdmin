<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/charts.php';

$db = get_db();

$total_users        = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_drivers      = $db->query("SELECT COUNT(*) FROM users WHERE role = 'driver'")->fetchColumn();
$pending_drivers    = $db->query("SELECT COUNT(*) FROM driver_info WHERE approval_status = 'pending'")->fetchColumn();
$active_bookings    = $db->query("SELECT COUNT(*) FROM bookings WHERE status IN ('pending','accepted','started')")->fetchColumn();
$completed_bookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn();
$total_bookings     = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

$recent = $db->query(
    "SELECT b.id, b.status, b.created_at,
            p.name AS passenger_name,
            d.name AS driver_name
     FROM bookings b
     JOIN users p ON p.id = b.passenger_id
     LEFT JOIN users d ON d.id = b.driver_id
     ORDER BY b.created_at DESC
     LIMIT 8"
)->fetchAll();

// Booking history — daily counts for the last 30 days
$bh_rows = $db->query(
    "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM bookings
     WHERE created_at >= CURDATE() - INTERVAL 29 DAY
     GROUP BY DATE(created_at)"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$booking_history = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $booking_history[$d] = (int)($bh_rows[$d] ?? 0);
}

// Tricycle driver registrants per barangay
$barangay_counts = $db->query(
    "SELECT barangay, COUNT(*) AS c FROM driver_info
     WHERE vehicle_type = 'Tricycle' AND barangay <> ''
     GROUP BY barangay ORDER BY c DESC"
)->fetchAll(PDO::FETCH_KEY_PAIR);

// Registered (approved) vs not approved yet (pending) vs rejected
$approval_counts = $db->query(
    "SELECT approval_status, COUNT(*) AS c FROM driver_info GROUP BY approval_status"
)->fetchAll(PDO::FETCH_KEY_PAIR);
$approved_count = (int)($approval_counts['approved'] ?? 0);
$pending_count  = (int)($approval_counts['pending'] ?? 0);
$rejected_count = (int)($approval_counts['rejected'] ?? 0);

$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?= number_format($total_users) ?></div>
        </div>
        <div class="stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Drivers</div>
            <div class="stat-value"><?= number_format($total_drivers) ?></div>
            <?php if ($pending_drivers > 0): ?>
                <div class="stat-sub">
                    <a href="<?= BASE_URL ?>/drivers.php?filter=pending" style="color:var(--amber-700);font-weight:600;font-size:12px;text-decoration:none;">
                        <?= $pending_drivers ?> pending approval
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Active Rides</div>
            <div class="stat-value"><?= number_format($active_bookings) ?></div>
            <div class="stat-sub">pending · accepted · started</div>
        </div>
        <div class="stat-icon green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="3 11 22 2 13 21 11 13 3 11"/>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Completed Rides</div>
            <div class="stat-value"><?= number_format($completed_bookings) ?></div>
            <div class="stat-sub">of <?= number_format($total_bookings) ?> total</div>
        </div>
        <div class="stat-icon cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:24px">
    <div class="panel-header">
        <span class="panel-title">Booking History</span>
        <span class="text-muted" style="font-size:12px">Last 30 days</span>
    </div>
    <div class="chart-panel-body">
        <?= render_booking_history_chart($booking_history) ?>
    </div>
</div>

<div class="charts-grid">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Tricycle Drivers per Barangay</span>
        </div>
        <div class="chart-panel-body">
            <?= render_bar_rows($barangay_counts, 'var(--blue-500)', 'No tricycle drivers registered yet.') ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Registered vs. Unregistered</span>
        </div>
        <div class="chart-panel-body">
            <?php
            $approval_items = ['Approved' => $approved_count, 'Unregistered' => $pending_count];
            if ($rejected_count > 0) $approval_items['Rejected'] = $rejected_count;
            $approval_colors = ['Approved' => 'var(--green-500)', 'Unregistered' => 'var(--amber-500)', 'Rejected' => 'var(--red-500)'];
            if (array_sum($approval_items) === 0):
            ?>
                <div class="chart-empty">No drivers registered yet.</div>
            <?php else: foreach ($approval_items as $label => $count): $pct = array_sum($approval_items) > 0 ? round(($count / max($approval_items)) * 100, 1) : 0; ?>
                <div class="chart-bar-row">
                    <div class="chart-bar-label" style="width:170px">
                        <span class="chart-legend-dot" style="background:<?= $approval_colors[$label] ?>"></span><?= htmlspecialchars($label) ?>
                    </div>
                    <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= $pct ?>%;background:<?= $approval_colors[$label] ?>"></div></div>
                    <div class="chart-bar-value"><?= number_format($count) ?></div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Recent Bookings</span>
        <a href="<?= BASE_URL ?>/bookings.php" class="btn-ghost">View All</a>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $b): ?>
                <tr>
                    <td><span class="cell-mono">BK-<?= str_pad($b['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                    <td><span class="cell-strong"><?= htmlspecialchars($b['passenger_name']) ?></span></td>
                    <td><?= $b['driver_name'] ? htmlspecialchars($b['driver_name']) : '<span class="text-subtle">—</span>' ?></td>
                    <td><?= status_badge($b['status']) ?></td>
                    <td><span class="text-muted"><?= date('M j, g:i A', strtotime($b['created_at'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">No bookings yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
