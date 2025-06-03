<?php 
session_start();
require_once 'includes/db.php';

$sessionId = $_GET['duelId'] ?? $_SESSION['session_id'] ?? null;
$error = '';

#region Ověření přítomnosti a formátu sessionId
if (!$sessionId) {
    exit('Session ID is missing.');
}

if(!preg_match('/^[a-f0-9\-]{36}$/', $sessionId)) {
    exit('Invalid session ID format.');
}
#endregion

$_SESSION['session_id'] = $sessionId;

#region Ověření existence session v db
$stmt = $pdo->prepare('SELECT * FROM sessions WHERE session_id = ?');
$stmt->execute([$sessionId]);
$sessionData = $stmt->fetch();

if (!$sessionData) {
    exit('Session not found.');
}
#endregion

#region Ověření, že uživatel patří do session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    exit('User ID is missing.');
}

$stmt = $pdo->prepare('SELECT * FROM session_users WHERE session_id = ? AND user_id = ?');
$stmt->execute([$sessionId, $userId]);

if ($stmt->rowCount() === 0) {
    exit('User is not part of this session.');
}
#endregion



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
    <title>Join a Duel - WatchMatch Duel</title>
</head>

<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.html' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if(empty($error)): ?>
                <div class="card border-0 p-4 mt-5 mb-5 slide-right" id="wm-welcome-card">
                    <div class="text-center">

                        <h1>Welcome to <span class="text-highlight">duel</span></h1>

                    </div>
                </div>
            <?php endif; ?>

        </div>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>