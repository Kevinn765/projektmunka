<?php
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

$group = $_GET['group'] ?? '';
if (!$group) {
  echo json_encode([]);
  exit;
}

$stmt = $pdo->prepare("SELECT name, description, tips FROM exercises WHERE muscle_group = ?");
$stmt->execute([$group]);
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($exercises, JSON_UNESCAPED_UNICODE);
