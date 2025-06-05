<?php 
session_start();
require_once 'includes/db.php';

$sessionId = $_SESSION['session_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
$errors = [];

if (!$sessionId || !$userId) {
    exit('Session ID or User ID is missing.');
}

// Kontrola, zda už uživatel hodnotil v této session
$checkStmt = $pdo->prepare("
    SELECT COUNT(*) FROM ratings WHERE session_id = ? AND user_id = ?
");
$checkStmt->execute([$sessionId, $userId]);
$alreadyRated = $checkStmt->fetchColumn();

if ($alreadyRated == 0) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exit('Invalid request method.');
    }

    $ratings = $_POST['ratings'] ?? [];

    if (empty($ratings)) {
        exit('No ratings provided.');
    }

    #region Validace všech položek
    foreach ($ratings as $contentId => $rating) {
        // 1. Ověření, že rating není prázdný
        if ($rating === '' || $rating === null) {
            $errors[] = "Rating for content ID $contentId is missing.";
            continue;
        }

        // 2. Převedení na int a ověření rozsahu
        $rating = (int)$rating;
        if ($rating < 1 || $rating > 5) {
            $errors[] = "Invalid rating for content ID $contentId. Must be between 1 and 5.";
            continue;
        }

        // 3. Ověření, že daný content_id patří k session
        $checkStmt = $pdo->prepare("SELECT 1 FROM session_content WHERE session_id = ? AND content_id = ?");
        $checkStmt->execute([$sessionId, $contentId]);
        if (!$checkStmt->fetchColumn()) {
            $errors[] = "Content ID $contentId does not belong to this session.";
        }
    }
    #endregion

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO ratings (session_id, user_id, content_id, rating)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($ratings as $contentId => $rating) {
            $stmt->execute([$sessionId, $userId, $contentId, (int)$rating]);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="icon" href="public/images/icon.png" type="image/png">
    <title>Submit rating - WatchMatch Duel</title>
</head>

<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.html' ?>

        <?php if (!empty($errors)): ?>
            <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center">
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <button onclick="window.history.back();" class="btn btn-primary">Back to form</button>
            </div>
        <?php endif; ?>

        <?php if (empty($errors)): ?>
            
            <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">
                <h3>Ratings submitted successfully!</h3>
                <p>Waiting for the other player to finish rating...</p>
            </div>

        <?php endif; ?>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>