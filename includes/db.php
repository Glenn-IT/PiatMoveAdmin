<?php
function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('<div style="font-family:system-ui,sans-serif;padding:30px;max-width:600px;margin:50px auto;border:1px solid #f87171;background:#fef2f2;border-radius:12px;color:#991b1b;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                <h2 style="margin-top:0;font-size:20px;display:flex;align-items:center;gap:8px;">⚠️ Database Connection Failed</h2>
                <p style="font-size:14px;color:#374151;">Could not connect to MySQL with the credentials in <code>admin/config.php</code>:</p>
                <div style="background:#fee2e2;padding:12px;border-radius:6px;font-family:monospace;font-size:13px;margin:14px 0;word-break:break-all;color:#991b1b;">' . htmlspecialchars($e->getMessage()) . '</div>
                <div style="font-size:13px;color:#4b5563;line-height:1.6;background:#fff;padding:14px;border-radius:6px;border:1px solid #e5e7eb;">
                    <strong>Please check <code>public_html/admin/config.php</code> in Hostinger File Manager:</strong><br>
                    • <strong>DB_HOST:</strong> <code>localhost</code><br>
                    • <strong>DB_NAME:</strong> Your Hostinger database name (e.g. <code>u..._piatmove</code>)<br>
                    • <strong>DB_USER:</strong> Your Hostinger database username (e.g. <code>u..._admin</code>)<br>
                    • <strong>DB_PASS:</strong> Your Hostinger database password
                </div>
            </div>');
        }
    }
    return $pdo;
}
