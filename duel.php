<?php
session_start();
require_once 'includes/db.php';


?>

<ul>
    <li>
        <?php echo htmlspecialchars($_SESSION['selected_type']) ?>
    </li>
    <ul>
        <?php foreach ($_SESSION['selected_genres'] as $genre): ?>
            <li><?php echo htmlspecialchars($genre); ?></li>
        <?php endforeach; ?>
    </ul>
    <li>
        <?php echo htmlspecialchars($_SESSION['selected_duel_count']) ?>
    </li>
</ul>