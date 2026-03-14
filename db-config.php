<?php
// Database configuration (use environment variables when available)
// NOTE: Use 127.0.0.1 instead of 'localhost' to force TCP (avoids Unix socket lookup)
$host = getenv('DB_HOST') ?: '::1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'pm_db1';
$user = getenv('DB_USER') ?: 'pm_user';
$pass = getenv('DB_PASS') ?: '@L0lly122511';
$charset = 'utf8mb4';

$dsn = 'mysql:host=' . $host;
if ($port !== '') {
    $dsn .= ';port=' . $port;
}
$dsn .= ';dbname=' . $dbname . ';charset=' . $charset;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // In a production environment you might want to log this instead
    http_response_code(500);
    echo "Database connection failed: " . $e->getMessage();
    exit;
}
