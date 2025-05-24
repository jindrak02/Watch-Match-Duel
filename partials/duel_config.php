<?php
require_once '../includes/db.php';

$stmt = $pdo->query('SELECT * FROM movies_and_series');
$content = $stmt->fetchAll();
?>

<div class="card border-0 p-4 mt-5 mb-5 slide-right" id="duel-config">
    <div class="text-center">
        <h1 class="mb-4">Choose what do you want to watch</h1>

        <form method="post" action="">
            <!-- 1. Choose type -->
            <div class="mb-3">
                <label class="form-label" style="color: var(--color-highlight); font-weight: 600;">Type:</label><br>
                <input type="radio" name="type" value="movie" id="type-movie" checked>
                <label for="type-movie">Movie</label>
                <input type="radio" name="type" value="series" id="type-series">
                <label for="type-series">Series</label>
                <input type="radio" name="type" value="both" id="type-both">
                <label for="type-both">Both</label>
            </div>

            <!-- 2. Choose genres (placeholder checkboxes) -->
            <div class="mb-3">
                <label class="form-label" style="color: var(--color-highlight); font-weight: 600;">Select genres:</label><br>

                <fieldset>
                    <label><input type="checkbox" name="genres[]" value="1"> Komedie</label><br>
                    <label><input type="checkbox" name="genres[]" value="2"> Drama</label><br>
                    <label><input type="checkbox" name="genres[]" value="3"> Sci-fi</label><br>
                    <label><input type="checkbox" name="genres[]" value="4"> Akční</label><br>
                    <label><input type="checkbox" name="genres[]" value="5"> Romantika</label><br>
                </fieldset>
            </div>

            <!-- 3. Number of items in duel -->
            <div class="mb-3">
                <label class="form-label" style="color: var(--color-highlight); font-weight: 600;">Number of items in duel:</label><br>
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

