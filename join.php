<?php 
session_start();
require_once 'includes/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Ramsey\Uuid\Uuid;

#region code to connect validation
$code = $_GET['code'] ?? null;
$error = '';

if (!$code) {
    $error = 'Invalid or missing connection code.';
}

$stmt = $pdo->prepare('SELECT session_id FROM sessions WHERE code_to_connect = ?');
$stmt->execute([$code]);
$session = $stmt->fetch();

if (!$session) {
    $error = 'Invalid or missing connection code.';
} else {
    $sessionId = $session['session_id'];
    $_SESSION['session_id'] = $sessionId;
    $_SESSION['code_to_connect'] = $code;
}
#endregion

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'enter_username2') {
        // Kontrola, zdali uživatel již není v duelu
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM session_users WHERE session_id = ? AND user_id = ?');
            $stmt->execute([$sessionId, $userId]);
            $alreadyJoined = (int)$stmt->fetchColumn() > 0;

            if ($alreadyJoined) {
                header('Location: duel.php?duelId=' . urlencode($sessionId));
                exit();
            }
        }

        // Pokud je uživatel přihlášen, použijeme jeho ID
        if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) {
            $userId = $_SESSION['user_id'];

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM session_users WHERE session_id = ?');
            $stmt->execute([$sessionId]);
            $userCount = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare('SELECT expected_user_count FROM sessions WHERE session_id = ?');
            $stmt->execute([$sessionId]);
            $expectedUserCount = (int)$stmt->fetchColumn();

            if ($userCount >= $expectedUserCount) {
                $error = 'This duel is already full.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO session_users (session_id, user_id) VALUES (?, ?)');
                $stmt->execute([$sessionId, $userId]);

                header('Location: duel.php' . '?duelId=' . urlencode($sessionId));
                exit();
            }
        } else {
            // Pokud uživatel není přihlášen, zpracujeme formulář pro zadání uživatelského jména
            $username2 =  trim($_POST['username2'] ?? '');

            if (empty($username2)) {
                $error = 'Username cannot be empty.';
            } elseif (strlen($username2) > 30) {
                $error = 'Username cannot exceed 30 characters.';
            } else {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM session_users WHERE session_id = ?');
                $stmt->execute([$sessionId]);
                $userCount = (int)$stmt->fetchColumn();

                $stmt = $pdo->prepare('SELECT expected_user_count FROM sessions WHERE session_id = ?');
                $stmt->execute([$sessionId]);
                $expectedUserCount = (int)$stmt->fetchColumn();

                if ($userCount >= $expectedUserCount) {
                    $error = 'This duel is already full.';
                } else {
                    $userId = Uuid::uuid4()->toString();
                    $stmt = $pdo->prepare('INSERT INTO users (user_id, username, is_guest) VALUES (?, ?, ?)');
                    $stmt->execute([$userId, $username2, 1]);

                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username2;

                    $stmt = $pdo->prepare('INSERT INTO session_users (session_id, user_id) VALUES (?, ?)');
                    $stmt->execute([$sessionId, $userId]);

                    header('Location: duel.php' . '?duelId=' . urlencode($sessionId));
                    exit();
                }
            }
        }

    } else {
        $error = 'Invalid form submission.';
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
    <title>Join a Duel - WatchMatch Duel</title>
</head>

<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.php' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if($error != 'Invalid or missing connection code.'): ?>
                <div class="card border-0 p-4 mt-5 mb-5 slide-right" id="wm-welcome-card">
                    <div class="text-center">

                        <form class="flex-column-center" method="POST">
                            <input type="hidden" name="form_type" value="enter_username2">

                            <?php if(!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']): ?>
                                <h1 class="mb-4">Enter your <span class="text-highlight">username</span></h1>
                                <div class="mb-3 w-50">
                                    <input type="text" class="form-control" name="username2" id="username2" required maxlength="30">
                                </div>
                            <?php else: ?>
                                <h1 class="mb-4">You are joining the duel as <span class="text-highlight"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h1>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary btn-lg px-5 mb-4" id="enter_username2_btn">Join Duel</button>
                        </form>

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