<?php

$dbDsn  = 'mysql:host=localhost;dbname=myapp;charset=utf8mb4';
$dbUser = 'myapp_user';
$dbPass = 'secret_password';


function send_json($status, $msg) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => $status, 'message' => $msg]);
    exit;
}

try {
    $pdo = new PDO($dbDsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // In production, do not reveal DB details
    http_response_code(500);
    exit('Server error');
}

// Start session securely
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']), // only true if using HTTPS
    'cookie_samesite' => 'Lax'
]);

// Simple CSRF token generator for forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Only accept POST for login processing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If you want to return the token for a JS client:
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['csrf_token' => $_SESSION['csrf_token']]);
    exit;
}

// Validate CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], (string)$csrf)) {
    http_response_code(400);
    send_json('error', 'Invalid CSRF token');
}

// Basic input validation
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    send_json('error', 'Missing username or password');
}

// Retrieve user by username (use prepared statement)
$stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :u LIMIT 1');
$stmt->execute([':u' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    // Authentication failed — generic message
    http_response_code(401);
    send_json('error', 'Invalid credentials');
}

// Authentication success — regenerate session id
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

// Return success (or redirect)
send_json('ok', 'Login successful');
