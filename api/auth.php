<?php
session_start();
require_once __DIR__.'/../db-config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Email and password required']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE identifier = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
    exit;
}

// verify password if hash exists
$ok = false;
if (!empty($user['password_hash'])) {
    // attempt SCrypt verification using libsodium if available
    if (function_exists('sodium_crypto_pwhash_scryptsalsa208sha256_str_verify')) {
        try {
            $ok = sodium_crypto_pwhash_scryptsalsa208sha256_str_verify(
                $user['password_hash'], $password
            );
        } catch (Exception $e) {
            $ok = false;
        }
    }
}

if (!$ok) {
    echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
    exit;
}

// authentication successful
$_SESSION['user'] = [
    'id' => $user['id'],
    'identifier' => $user['identifier'],
    'full_name' => $user['full_name'],
    'role' => $user['role'],
    'user_uid' => $user['user_uid']
];

// optionally update last_signed_in
$stmt = $pdo->prepare('UPDATE users SET last_signed_in = NOW() WHERE id = ?');
$stmt->execute([$user['id']]);

echo json_encode(['success'=>true]);
