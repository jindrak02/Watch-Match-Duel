<?php
session_start();
include 'includes/db.php';
$sessionId = $_SESSION['session_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

header('Content-Type: application/json');

if (!$sessionId || !$userId) {
    echo json_encode(['ready' => false, 'error' => 'Session ID or User ID is missing.']);
    exit;
}

# 1. Kolik filmů/seriálů je v duelu
$stmt = $pdo->prepare("SELECT COUNT(*) FROM session_content WHERE session_id = ?");
$stmt->execute([$sessionId]);
$contentCount = (int)$stmt->fetchColumn();

# 2. Kolik uživatelů ohodnotilo všechny položky
$stmt = $pdo->prepare("
    SELECT user_id, COUNT(*) as rating_count
    FROM ratings
    WHERE session_id = ?
    GROUP BY user_id
    HAVING rating_count >= ?
");
$stmt->execute([$sessionId, $contentCount]);
$usersRated = $stmt->fetchAll(PDO::FETCH_COLUMN);

# 3. Kolik uživatelů je v session
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT user_id) FROM session_users
    WHERE session_id = ?
");
$stmt->execute([$sessionId]);
$usersInSessionCount = (int)$stmt->fetchColumn();

$isReady = count($usersRated) >= $usersInSessionCount;

echo json_encode([
    'ready' => $isReady,
    'usersRatedCount' => count($usersRated),
    'usersInSessionCount' => $usersInSessionCount,
    'duelId' => $sessionId,
    'error' => $isReady ? null : 'Not enough users have rated yet.'
]);
exit;


?>