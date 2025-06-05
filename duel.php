<?php
session_start();
require_once 'includes/db.php';

$sessionId = $_GET['duelId'] ?? $_SESSION['session_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
$error = '';
$formValidationErrors = $_SESSION['errors'] ?? [];

#region Ověření přítomnosti a formátu sessionId
if (!$sessionId) {
    exit('Session ID is missing.');
}

if (!preg_match('/^[a-f0-9\-]{36}$/', $sessionId)) {
    exit('Invalid session ID format.');
}
#endregion

#region Ověření existence session v db
$stmt = $pdo->prepare('SELECT * FROM sessions WHERE session_id = ?');
$stmt->execute([$sessionId]);
$sessionData = $stmt->fetch();

if (!$sessionData) {
    exit('Session not found.');
}
#endregion

#region Ověření, že uživatel patří do session
if (!$userId) {
    exit('User ID is missing.');
}

$stmt = $pdo->prepare('SELECT * FROM session_users WHERE session_id = ? AND user_id = ?');
$stmt->execute([$sessionId, $userId]);

if ($stmt->rowCount() === 0) {
    exit('User is not part of this session.');
}
#endregion

#region Načtení obsahu k hodnocení
// Získání jména ostatních uživatelů v session
$stmt = $pdo->prepare("
    SELECT u.username
    FROM users u
    JOIN session_users su ON u.user_id = su.user_id
    WHERE su.session_id = ?
    AND u.user_id <> ?
");
$stmt->execute([$sessionId, $userId]);
$otherUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$otherUsers) {
    $error = 'No second user found in this session.';
}

// Získání obsahu k hodnocení
$stmt = $pdo->prepare("
    SELECT
        ms.content_id,
        ms.title,
        ms.type,
        ms.description,
        ms.poster_url,
        ms.release_date,
        ms.vote_average,
        ms.original_language
    
    FROM session_content sc
    JOIN movies_and_series ms ON sc.content_id = ms.content_id

    WHERE sc.session_id = ?
    ORDER BY RAND()
");
$stmt->execute([$sessionId]);
$contentList = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Rate content - WatchMatch Duel</title>
</head>

<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.html' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($formValidationErrors)): ?>
                <?php foreach ($formValidationErrors as $error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']);?>
            <?php endif; ?>

            <?php if (empty($error)): ?>
                <div class="card border-0 p-4 mt-5 mb-5 slide-right" id="wm-welcome-card">
                    <div class="text-center">

                        <h1 class="my-4">
                            You are in a <span class="text-highlight">Duel</span> with 
                            <span class="text-highlight">
                                <?php echo htmlspecialchars(implode(', ', $otherUsers)); ?>
                            </span>
                        </h1>

                        <div class="d-flex align-items-center my-4">
                            <hr class="flex-grow-1 divider-half">
                            <span class="mx-3 fw-bold text-secondary">Rate each of the items below</span>
                            <hr class="flex-grow-1 divider-half">
                        </div>

                        <form class="margin-top-4" method="POST" action="submit_ratings.php">
                            <?php foreach ($contentList as $item): ?>
                                <div class="my-4 py-4">
                                    <h3 class="text-highlight"><?php echo htmlspecialchars($item['title']); ?></h3>

                                    <div class="flex-row">
                                        <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-fluid mb-2 rate-poster">
                                        <div class="ms-3 text-start">
                                            <p><strong>Release Year:</strong> <?php echo htmlspecialchars(date('Y', strtotime($item['release_date']))); ?></p>
                                            <p><strong>Language:</strong> <?php echo htmlspecialchars($item['original_language']); ?></p>
                                            <p><strong>Type:</strong> <?php echo htmlspecialchars($item['type']); ?></p>
                                            <p><strong>Average Rating:</strong> <?php echo number_format($item['vote_average'], 1); ?> / 10</p>
                                        </div>
                                    </div>
                                    
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>

                                    <label class="fw-bold" for="rating_<?php echo $item['content_id']; ?>">Rate this content:</label>
                                    <div class="star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="ratings[<?php echo $item['content_id']; ?>]" id="rating_<?php echo $item['content_id']; ?>_<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                            <label class="rating-label" for="rating_<?php echo $item['content_id']; ?>_<?php echo $i; ?>">&#9733;</label>
                                        <?php endfor; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary">Submit Ratings</button>
                        </form>

                    </div>
                </div>
            <?php endif; ?>

        </div>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script>
        // Zajištění automatického posouvání na další položku při změně hodnocení
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('form.margin-top-4 > div.my-4.py-4');
            items.forEach((item, id) => {
                const radios = item.querySelectorAll('input[type="radio"]');
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const nextItem = items[id + 1];
                        if (nextItem) {
                            nextItem.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>