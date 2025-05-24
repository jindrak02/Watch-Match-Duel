<?php
require_once '../includes/db.php';
require_once '../includes/config.secret.php';

$apiKey = $TMDB_API_KEY;
$language = 'en-US';

// 1. Získání žánrů
$genresJson = file_get_contents("https://api.themoviedb.org/3/genre/movie/list?api_key=$apiKey&language=$language");
$genresData = json_decode($genresJson, true);

foreach ($genresData['genres'] as $genre) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO genres (id, genre_name) VALUES (?, ?)');
    $stmt->execute([$genre['id'], $genre['name']]);
    echo "Inserted genre: {$genre['name']} (ID: {$genre['id']})<br>";
}

?>