<?php
session_start();
require_once 'includes/db.php';
require_once __DIR__ . '/vendor/autoload.php';
use Ramsey\Uuid\Uuid;

$allowed_types = ['movie', 'series', 'both'];
$allowed_counts = [5, 10, 15];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_type = $_POST['type'] ?? 'movie';
    $selected_genres = $_POST['genres'] ?? [12, 14, 16, 18, 27];
    $selected_duel_count = $_POST['duel_count'] ?? 5;

    if (count($selected_genres) > 5) {
        $error = "You can only choose up to 5 genres.";
    } elseif (!in_array($selected_type, $allowed_types, true)) {
        $error = "Invalid content type.";
    } elseif (!in_array((int)$selected_duel_count, $allowed_counts, true)) {
        $error = "Invalid duel items count.";
    } else {
        $_SESSION['selected_type'] = $selected_type;
        $_SESSION['selected_genres'] = $selected_genres;
        $_SESSION['selected_duel_count'] = $selected_duel_count;

        #region uložení výběru do session v databázi
        // Session id a kód pro připojení k duelu
        $sessionId = Uuid::uuid4()->toString();
        $code = bin2hex(random_bytes(4));
        
        $_SESSION['session_id'] = $sessionId;
        $_SESSION['code_to_connect'] = $code;

        // Vytvoření nové session v db
        $stmt = $pdo->prepare('INSERT INTO sessions (session_id, code_to_connect, type, items_in_duel_count) VALUES (?, ?, ?, ?)');
        $stmt->execute([$sessionId, $code, $selected_type, $selected_duel_count]);

        // Uložení vybraných žánrů k session
        $stmt = $pdo->prepare('INSERT INTO session_genres (session_id, genre_id) VALUES (?, ?)');
        foreach ($selected_genres as $genre) {
            $stmt->execute([$sessionId, (int)$genre]);
        }

        #endregion

        header('Location: duel.php');
        exit;
    }
}
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
    <title>WatchMatch Duel</title>
</head>

<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.html' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 p-4 mt-5 mb-5 slide-right" id="wm-welcome-card">
                <div class="text-center">
                    <h1 class="mb-4">Welcome to <span style="color: var(--color-highlight);">WatchMatch Duel!</span></h1>
                    <p class="lead mb-5">
                        Not sure what to watch tonight?<br>
                        <span style="color: var(--color-highlight); font-weight: 600;">Get your partner or friends and find out!</span>
                    </p>
                    <button class="btn btn-primary btn-lg px-5" id="startBtn">Start</button>
                    <div class="mt-4 wm-div-secondary-text">
                        Discover, match, and enjoy movies &amp; series together.<br>
                        <span>It's fun, fast, and free!</span>
                    </div>
                </div>
            </div>

        </div>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script>
        const welcomeCard = document.getElementById('wm-welcome-card');
        const app = document.getElementById('app');
        const welcomeCardHTML = welcomeCard.outerHTML;

        function loadActionConfig(pushHistory) {
            fetch('partials/action_config.php')
                .then(res => res.text())
                .then(html => {
                    app.innerHTML = html;
                    if (pushHistory) {
                        history.pushState({
                            page: 'action_config'
                        }, 'Action_Config', 'index.php');
                    }
                    // Přidání event listeneru pro tlačítko Start New Duel
                    document.getElementById('startNewDuelBtn').addEventListener('click', function(e) {
                        e.preventDefault();
                        loadDuelConfig(true);
                    });
                })
                .catch(err => console.error('Error loading action_config:', err));
        }

        function loadDuelConfig(pushHistory) {
            fetch('partials/duel_config.php')
                .then(res => res.text())
                .then(html => {
                    app.innerHTML = html;
                    if (pushHistory) {
                        history.pushState({
                            page: 'duel_config'
                        }, 'Duel_Config', 'index.php');
                    }
                    
                    /* #region Validace výběru max 5 žánrů */
                    const genreCheckboxes = document.querySelectorAll('input[name="genres[]"]');
                    const maxGenres = 5;

                    genreCheckboxes.forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            const checkedCount = document.querySelectorAll('input[name="genres[]"]:checked').length;
                            if (checkedCount >= maxGenres) {
                                genreCheckboxes.forEach(function(box) {
                                    if (!box.checked) {
                                        box.disabled = true;
                                    }
                                });
                            } else {
                                genreCheckboxes.forEach(function(box) {
                                    box.disabled = false;
                                });
                            }
                        })
                    })
                    /* #endregion */
                })
                .catch(err => console.error('Error loading duel_config:', err));
        }

        document.getElementById('startBtn').addEventListener('click', function(e) {
            e.preventDefault();
            welcomeCard.classList.add('slide-left');

            setTimeout(() => {
                loadActionConfig(true);
            }, 500);
        });

        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.page === 'action_config') {
                loadActionConfig(false);
            } else if (event.state && event.state.page === 'duel_config') {
                loadDuelConfig(false);
            } else {
                // Obnova původní welcome stránky
                app.innerHTML = welcomeCardHTML;
                // Opětovné přidání event listeneru na tlačítko Start
                document.getElementById('startBtn').addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('wm-welcome-card').classList.add('slide-left');
                    setTimeout(() => {
                        loadActionConfig(true);
                    }, 500);
                });
            }
        });
    </script>
</body>

</html>