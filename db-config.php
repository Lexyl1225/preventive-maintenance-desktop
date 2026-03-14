<?php
// Database configuration (use environment variables when available)
// NOTE: Use 127.0.0.1 instead of 'localhost' to force TCP (avoids Unix socket lookup)
// replace it with your actual credentials
$host = getenv('DB_HOST') ?: '::1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'yourdb_name';
$user = getenv('DB_USER') ?: 'yourdb_username';
$pass = getenv('DB_PASS') ?: '@your_password';
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
