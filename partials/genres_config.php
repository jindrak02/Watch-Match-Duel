<?php
    require_once '../includes/db.php';

    $stmt = $pdo->query('SELECT * FROM movies_and_series');
    $content = $stmt->fetchAll();
?>

<div class="card border-0 p-4 mt-5 mb-5 slide-right" id="duel-config">
    <div class="text-center">
        <h1 class="mb-4">Choose the genres you want to watch</h1>
        <!-- TODO: načíst žánry a checkboxy k nim -->
    </div>
</div>