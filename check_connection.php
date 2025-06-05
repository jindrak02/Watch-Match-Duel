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

$stmt = $pdo->prepare('SELECT expected_user_count FROM sessions WHERE session_id = ?');
$stmt->execute([$sessionId]);
$expectedUserCount = $stmt->fetchColumn();

if ($row && $row['user_count'] >= $expectedUserCount) {
    echo json_encode(['ready' => true, 'duelId' => $sessionId]);
} else {
    echo json_encode(['ready' => false]);
    exit;
}
?>