<?php
session_start();
require_once 'includes/db.php';

$sessionId = $_GET['duelId'] ?? null;
$errors = [];
$debug = false;

if (!$sessionId || !preg_match('/^[a-f0-9\-]{36}$/', $sessionId)) {
    exit('Invalid or missing duel ID.');
}

#region Načtení výsledků z db a metadat obsahu
$stmt = $pdo->prepare("
    SELECT r.user_id, r.content_id, r.rating, ms.title, ms.poster_url, ms.imdb_url
    FROM ratings r
    JOIN movies_and_series ms ON r.content_id = ms.content_id
    WHERE r.session_id = ?
");
$stmt->execute([$sessionId]);
$ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($ratings)) {
    exit('No ratings found for this session.');
}
#endregion

#region Výpočet průměrného hodnocení obsahu a míry shody
// 1. Příprava struktury content_id => [user_id => rating]
$contentRatings = [];
$metadata = [];

foreach ($ratings as $rating) {
    $contentId = $rating['content_id'];
    $userId = $rating['user_id'];
    $contentRatings[$contentId][$userId] = (int)$rating['rating'];

    if (!isset($metadata[$contentId])) {
        $metadata[$contentId] = [
            'title' => $rating['title'],
            'poster_url' => $rating['poster_url'],
            'imdb_url' => $rating['imdb_url'],
        ];
    }
}

// 2. Výpočet průměrného hodnocení pro každý content_id
$averageRatings = [];
foreach ($contentRatings as $contentId => $ratings) {
    $averageRatings[$contentId] = array_sum($ratings) / count($ratings);
}

// 3. Výpočet míry shody pro každý content_id (pomoc od AI)
$matchScores = [];
foreach ($contentRatings as $contentId => $ratings) {
    $userCount = count($ratings);
    if ($userCount < 2) {
        $matchScores[$contentId] = 0; // Not enough ratings to calculate match score
        continue;
    }

    $ratingValues = array_values($ratings);
    $mean = array_sum($ratingValues) / $userCount;
    $variance = array_sum(array_map(function($x) use ($mean) {
        return pow($x - $mean, 2);
    }, $ratingValues)) / ($userCount - 1);
    
    $stdDev = sqrt($variance);
    if ($stdDev == 0) {
        $matchScores[$contentId] = 100; // Perfect match
    } else {
        $matchScores[$contentId] = (1 - ($stdDev / 2.5)) * 100; // Scale to percentage
    }
}
#endregion

#region Příprava dat pro zobrazení
$contentData = [];
foreach ($metadata as $contentId => $data) {
    $contentData[] = [
        'content_id' => $contentId,
        'title' => $data['title'],
        'poster_url' => $data['poster_url'],
        'imdb_url' => $data['imdb_url'],
        'average_rating' => round($averageRatings[$contentId], 2),
        'match_score' => round($matchScores[$contentId], 2),
        'ratings' => $contentRatings[$contentId],
    ];
}

usort($contentData, function ($a, $b) {
    return $b['average_rating'] <=> $a['average_rating'];
});

$topResults = array_slice($contentData, 0, 3);
$otherResults = array_slice($contentData, 3);

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
    <title>Duel results - WatchMatch Duel</title>
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

        <?php if($debug): ?>
        <div id="debug">
            <h3>$contentRatings</h3>
            <pre><?php echo htmlspecialchars(print_r($contentRatings, true)); ?></pre>
            <hr>
            <h3>$metadata</h3>
            <pre><?php echo htmlspecialchars(print_r($metadata, true)); ?></pre>
            <hr>
            <h3>$averageRatings</h3>
            <pre><?php echo htmlspecialchars(print_r($averageRatings, true)); ?></pre>
            <hr>
            <h3>$matchScores</h3>
            <pre><?php echo htmlspecialchars(print_r($matchScores, true)); ?></pre>
            <hr>
            <h3>$contentData</h3>
            <pre><?php echo htmlspecialchars(print_r($contentData, true)); ?></pre>
        </div>
        <?php endif; ?>

        <?php if (empty($errors)): ?>

            <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">
                <h1 class="my-4 slide-right">Your <span class="text-highlight">Top 3 Results:</span></h3>

                <div class="my-2 py-4 row slide-right">
                    <?php foreach ($topResults as $content): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo htmlspecialchars($content['poster_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($content['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <span class="badge bg-secondary me-2">#<?php echo array_search($content, $topResults) + 1; ?></span>
                                        <?php echo htmlspecialchars($content['title']); ?>
                                    </h5>
                                    <p class="card-text">
                                        <span class="text-highlight">
                                            Your Average Rating: <strong><?php echo htmlspecialchars($content['average_rating']); ?>/5</strong><br>
                                        </span>
                                        Your Match Score: <strong><?php echo htmlspecialchars($content['match_score']); ?>%</strong>
                                    </p>
                                    <a href="https://www.imdb.com/find/?q=<?php echo htmlspecialchars($content['title']); ?>" class="btn btn-primary" target="_blank">View on IMDb</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex align-items-center my-4 py-4">
                        <hr class="flex-grow-1" style="border-top: 2px solid #ccc; margin: 0;">
                        <span class="mx-3 fw-bold text-secondary">Other results (booring)</span>
                        <hr class="flex-grow-1" style="border-top: 2px solid #ccc; margin: 0;">
                    </div>

                    <?php foreach ($otherResults as $content): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo htmlspecialchars($content['poster_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($content['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($content['title']); ?></h5>
                                    <p class="card-text">
                                        <span class="text-highlight">
                                            Your Average Rating: <strong><?php echo htmlspecialchars($content['average_rating']); ?>/5</strong><br>
                                        </span>
                                        Your Match Score: <strong><?php echo htmlspecialchars($content['match_score']); ?>%</strong>
                                    </p>
                                    <a href="https://www.imdb.com/find/?q=<?php echo htmlspecialchars($content['title']); ?>" class="btn btn-primary" target="_blank">View on IMDb</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endif; ?>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>