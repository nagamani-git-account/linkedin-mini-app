<?php
require_once _DIR_.'/cors.php';
session_start();
header('Content-Type: application/json');
session_start();
require_once __DIR__.'/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Email and password required']);
  exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, name, email, password_hash FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user['password_hash'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Invalid credentials']);
  exit;
}

$_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
echo json_encode(['message' => 'Login successful']);