<?php 
session_start();
require_once 'includes/db.php';

$sessionId = $_SESSION['session_id'] ?? null;
header('Content-Type: application/json');

if (!$sessionId) {
    echo json_encode(['ready' => false]);
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) as user_count FROM session_users WHERE session_id = ?');
$stmt->execute([$sessionId]);
$row = $stmt->fetch();

if ($row && $row['user_count'] >= 2) {
    echo json_encode(['ready' => true, 'duelId' => $sessionId]);
} else {
    echo json_encode(['ready' => false]);
}
?>