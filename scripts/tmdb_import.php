<?php
require_once '../includes/db.php';
require_once '../includes/config.secret.php';

$apiKey = $TMDB_API_KEY;
$language = 'en-US';

#region 1. Získání žánrů
$genresJson = file_get_contents("https://api.themoviedb.org/3/genre/movie/list?api_key=$apiKey&language=$language");
$genresData = json_decode($genresJson, true);

foreach ($genresData['genres'] as $genre) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO genres (id, genre_name) VALUES (?, ?)');
    $stmt->execute([$genre['id'], $genre['name']]);
    echo "Inserted genre: {$genre['name']} (ID: {$genre['id']})<br>";
}
echo "--------------------------------<br>";
#endregion


#region 2. Získání filmů
$minVoteCount = 200;
$imageBaseUrl = 'https://image.tmdb.org/t/p/w500';
$baseUrl = 'https://api.themoviedb.org/3';

for ($page = 1; $page <= 5; $page++) {
    $url = "$baseUrl/discover/movie?api_key=$apiKey&language=$language&sort_by=vote_average.desc&vote_count.gte=$minVoteCount&page=$page";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // echo "<pre>";
    // print_r($data);
    // echo "</pre>";

    if(!isset($data['results']) || empty($data['results'])) {
        echo "No results found on page $page.<br>";
        continue;
    }

    foreach ($data['results'] as $movie) {
        $tmdbId = $movie['id'];

        // Kontrola, zda film již existuje
        $stmt = $pdo->prepare('SELECT content_id FROM movies_and_series WHERE tmdb_id = ?');
        $stmt->execute([$tmdbId]);
        if ($stmt->fetch() !== false) {
            echo "Movie with TMDB ID $tmdbId already exists. Skipping...<br>";
            continue;
        }

        $title = $movie['title'] ?? '';
        $description = $movie['overview'] ?? '';
        $posterUrl = isset($movie['poster_path']) ? $imageBaseUrl . $movie['poster_path'] : null;
        $releaseDate = $movie['release_date'] ?? null;
        $voteAverage = $movie['vote_average'] ?? null;
        $voteCount = $movie['vote_count'] ?? null;
        $popularity = $movie['popularity'] ?? null;
        $originalLanguage = $movie['original_language'] ?? null;

        // Vložení filmu do databáze
        $stmt = $pdo->prepare('
            INSERT INTO movies_and_series 
            (title, type, description, poster_url, release_date, vote_average, vote_count, popularity, tmdb_id, original_language)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $title,
            'movie',
            $description,
            $posterUrl,
            $releaseDate,
            $voteAverage,
            $voteCount,
            $popularity,
            $tmdbId,
            $originalLanguage
        ]);

        $contentId = $pdo->lastInsertId();

        // Propojení s žánry
        foreach ($movie['genre_ids'] as $genreId) {
            // checknu, jestli žánr je v db a až potom vložim
            $stmt = $pdo->prepare('SELECT 1 FROM genres WHERE id = ?');
            $stmt->execute([$genreId]);

            if ($stmt->fetch() !== false) {
                $stmt = $pdo->prepare('INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)');
                $stmt->execute([$contentId, $genreId]);
            }
        }

        echo "Inserted movie: $title (ID: $contentId) <br>";
    }
}
#endregion

#region 3. Získání seriálů
$minVoteCount = 200;
$imageBaseUrl = 'https://image.tmdb.org/t/p/w500';
$baseUrl = 'https://api.themoviedb.org/3';

for ($page = 1; $page <= 5; $page++) {
    $url = "$baseUrl/discover/tv?api_key=$apiKey&language=$language&sort_by=vote_average.desc&vote_count.gte=$minVoteCount&page=$page";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    echo "--------------------------------<br>";
    // echo "<pre>";
    // print_r($data);
    // echo "</pre>";

    if(!isset($data['results']) || empty($data['results'])) {
        echo "No results found on page $page.<br>";
        continue;
    }

    foreach ($data['results'] as $serie) {
        $tmdbId = $serie['id'];

        // Kontrola, zda film již existuje
        $stmt = $pdo->prepare('SELECT content_id FROM movies_and_series WHERE tmdb_id = ?');
        $stmt->execute([$tmdbId]);
        if ($stmt->fetch() !== false) {
            echo "Serie with TMDB ID $tmdbId already exists. Skipping...<br>";
            continue;
        }

        $title = $serie['name'] ?? '';
        $description = $serie['overview'] ?? '';
        $posterUrl = isset($serie['poster_path']) ? $imageBaseUrl . $serie['poster_path'] : null;
        $releaseDate = $serie['first_air_date'] ?? null;
        $voteAverage = $serie['vote_average'] ?? null;
        $voteCount = $serie['vote_count'] ?? null;
        $popularity = $serie['popularity'] ?? null;
        $originalLanguage = $serie['original_language'] ?? null;

        // Vložení filmu do databáze
        $stmt = $pdo->prepare('
            INSERT INTO movies_and_series 
            (title, type, description, poster_url, release_date, vote_average, vote_count, popularity, tmdb_id, original_language)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $title,
            'series',
            $description,
            $posterUrl,
            $releaseDate,
            $voteAverage,
            $voteCount,
            $popularity,
            $tmdbId,
            $originalLanguage
        ]);

        $contentId = $pdo->lastInsertId();

        // Propojení s žánry
        foreach ($serie['genre_ids'] as $genreId) {
            // checknu, jestli žánr je v db a až potom vložim
            $stmt = $pdo->prepare('SELECT 1 FROM genres WHERE id = ?');
            $stmt->execute([$genreId]);

            if ($stmt->fetch() !== false) {
                $stmt = $pdo->prepare('INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)');
                $stmt->execute([$contentId, $genreId]);
            }
        }

        echo "Inserted serie: $title (ID: $contentId) <br>";
    }
}
#endregion

?>