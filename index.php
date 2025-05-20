<?php
require_once 'includes/db.php';

$stmt = $pdo->query('SELECT * FROM movies_and_series');
$stmt->execute();

$content = $stmt->fetchAll();

var_dump($content);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WatchMatch Duel</title>
</head>
<body>
    <h1>Welcome to WatchMatch Duel!</h1>

    <script>
        const moviesAndSeries = <?php echo json_encode($content); ?>;
        console.log(moviesAndSeries);
    </script>
</body>
</html>