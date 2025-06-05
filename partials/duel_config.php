<?php
session_start();
require_once '../includes/db.php';

$stmt = $pdo->query('SELECT * FROM genres ORDER BY genre_name');
$genres = $stmt->fetchAll();
?>

<div class="card border-0 p-4 mt-5 mb-5 slide-right" id="duel-config">
    <div class="text-center">
        <h1 class="mb-4">Choose what do you want to watch</h1>

        <form method="post" action="">
            <input type="hidden" name="form_type" value="duel_config">

            <?php if(!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']): ?>
                <div class="mb-3 flex-column-center">
                    <label class="form-label text-highlight" for="username">Your Username:</label>
                    <input type="text" class="form-control w-50" id="username" name="username" required maxlength="30">
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label text-highlight">Type:</label><br>
                <input type="radio" name="type" value="movie" id="type-movie" checked>
                <label for="type-movie">Movie</label>
                <input type="radio" name="type" value="series" id="type-series">
                <label for="type-series">Series</label>
                <input type="radio" name="type" value="both" id="type-both">
                <label for="type-both">Both</label>
            </div>

            <div class="mb-3">
                <label class="form-label text-highlight">Select genres:</label><br>

                <fieldset>
                    <?php foreach ($genres as $genre): ?>
                        <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>" id="genre-<?php echo $genre['id']; ?>">
                        <label for="genre-<?php echo $genre['id']; ?>"><?php echo htmlspecialchars($genre['genre_name']); ?></label><br>
                    <?php endforeach; ?>
                </fieldset>
            </div>

            <div class="mb-3">
                <label class="form-label text-highlight">Number of items in duel:</label><br>
                <input type="radio" name="duel_count" value="5" id="duel5" checked>
                <label for="duel5">5</label>
                <input type="radio" name="duel_count" value="10" id="duel10">
                <label for="duel10">10</label>
                <input type="radio" name="duel_count" value="15" id="duel15">
                <label for="duel15">15</label>
            </div>

            <button type="submit" class="btn btn-primary">Start Duel</button>
        </form>

    </div>
</div>