<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Not authenticated']);
  exit;
}
require_once __DIR__.'/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$content = trim($input['content'] ?? '');
if ($content === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Post content required']);
  exit;
}

$user_id = $_SESSION['user']['id'];
$stmt = mysqli_prepare($conn, "INSERT INTO posts (user_id, content) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "is", $user_id, $content);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode(['message' => 'Post created']);