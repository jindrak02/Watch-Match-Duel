<?php
require_once 'includes/db.php';

$stmt = $pdo->query('SELECT * FROM movies_and_series');
$stmt->execute();

$content = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="public/css/styles.css">
    <title>WatchMatch Duel</title>
</head>
<body class="bg-light">

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.html' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center">
            <div class="text-center">
                <h1 class="mb-4">Welcome to WatchMatch Duel!</h1>
                <p class="lead mb-5">Not sure what to watch tonight? Get your partner or friends and find out!</p>
                <a href="#" class="btn btn-primary btn-lg" id="startBtn">Start</a>
            </div>
        </div>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script>
        const moviesAndSeries = <?php echo json_encode($content); ?>;
        console.log(moviesAndSeries);

        document.getElementById('startBtn').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Start button clicked!');
        });
    </script>
</body>
</html>