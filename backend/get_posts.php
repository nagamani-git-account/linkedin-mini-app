<?php
require_once _DIR_.'/cors.php';
session_start();
//session_start(); // not required but fine
require_once __DIR__.'/db.php';

$sql = "SELECT p.content, p.created_at, u.name
        FROM posts p JOIN users u ON u.id = p.user_id
        ORDER BY p.created_at DESC";
$res = mysqli_query($conn, $sql);

$rows = [];
while ($row = mysqli_fetch_assoc($res)) { $rows[] = $row; }

if (empty($rows)) {
  echo '';
  exit;
}

foreach ($rows as $r) {
  $name = htmlspecialchars($r['name']);
  $content = nl2br(htmlspecialchars($r['content']));
  $ts = htmlspecialchars($r['created_at']);
  echo "<div class='post'>
          <div class='meta'>{$name} • {$ts}</div>
          <div>{$content}</div>
        </div>";
}