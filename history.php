<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in'] || !isset($_SESSION['user_id'])) {
    exit('You must be logged in to view your duels.');
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        s.session_id,
        s.created_at,
        GROUP_CONCAT(u2.username ORDER BY u2.username SEPARATOR ', ') AS partner_names
    FROM sessions s
    JOIN session_users su1 ON su1.session_id = s.session_id AND su1.user_id = ?
    JOIN session_users su2 ON su2.session_id = s.session_id AND su2.user_id != ?
    JOIN users u2 ON u2.user_id = su2.user_id
    WHERE EXISTS (
        SELECT 1 FROM ratings r WHERE r.session_id = s.session_id AND r.user_id = ?
    )
    GROUP BY s.session_id, s.created_at
    ORDER BY s.created_at DESC
");
$stmt->execute([$userId, $userId, $userId]);
$duels = $stmt->fetchAll();
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
    <title>My history - WatchMatch Duel</title>
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

            <div class="card border-0 p-4 mt-5 mb-5 slide-right" id="wm-welcome-card">
                <div class="text-center">
                    <h1 class="mb-4">Your Duel <span class="text-highlight">History</span></h1>
                    <p class="lead mb-5 text-secondary">
                        Here you can see all your duels with ratings.
                    </p>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Partners</th>
                                <th scope="col">Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($duels)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No duels found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($duels as $duel): ?>
                                    <tr>
                                        <td>
                                            <?php
                                                $date = new DateTime($duel['created_at']);
                                                echo $date->format('j. n. Y H:i');
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($duel['partner_names']); ?></td>
                                        <td>
                                             <a href="results.php?duelId=<?php echo htmlspecialchars($duel['session_id']); ?>" class="list-group-item list-group-item-action">
                                                View Duel Result
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>