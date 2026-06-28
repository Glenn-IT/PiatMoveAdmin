<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

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

$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

function status_badge(string $status): string {
    $map = [
        'pending'   => 'neutral',
        'accepted'  => 'primary',
        'started'   => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'rejected'  => 'warning',
    ];
    $cls = $map[$status] ?? 'neutral';
    return '<span class="badge badge-' . htmlspecialchars($cls, ENT_QUOTES) . '">'
         . htmlspecialchars(ucfirst($status), ENT_QUOTES)
         . '</span>';
}
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
