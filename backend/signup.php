<?php
declare(strict_types=1);

// show errors while we debug
ini_set('display_errors', '1');
error_reporting(E_ALL);

// always JSON
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/db.php';   // <-- make sure this file exists

// read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = (string)($data['password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

// email exists?
$stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already registered']);
    mysqli_stmt_close($stmt);
    exit;
}
mysqli_stmt_close($stmt);

// insert user
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn, 'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['error' => 'DB insert failed', 'detail' => mysqli_error($conn)]);
    mysqli_stmt_close($stmt);
    exit;
}
$user_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// login session
$_SESSION['user'] = ['id' => $user_id, 'name' => $name, 'email' => $email];

echo json_encode(['message' => 'Signup successful', 'user' => $_SESSION['user']]);