<?php
define('DB_HOST',  getenv('DB_HOST') ?: 'localhost');
define('DB_NAME',  getenv('DB_NAME') ?: 'piatmove');
define('DB_USER',  getenv('DB_USER') ?: 'root');
define('DB_PASS',  getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

if (getenv('BASE_URL')) {
    define('BASE_URL', rtrim(getenv('BASE_URL'), '/'));
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir    = rtrim(str_replace('\\', '/', dirname($script)), '/');
    define('BASE_URL', $scheme . '://' . $host . ($dir !== '' ? $dir : ''));
}
