<?php
require_once 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="icon" href="public/images/icon.png" type="image/png">
    <title>WatchMatch Duel</title>
</head>
<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.html' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">

            <div class="card border-0 p-4 mt-5 mb-5" id="wm-welcome-card">
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

        document.getElementById('startBtn').addEventListener('click', function(e) {
            e.preventDefault();
            welcomeCard.classList.add('slide-left');

            setTimeout(() => {
                fetch('partials/action_config.php')
                .then(res => res.text())
                .then(html => {
                    app.innerHTML = html;
                    history.pushState({page: 'action_config'}, 'Action_Config', 'action_config.php');
                })
                .catch(err => console.error('Error loading action_config:', err));
            }, 500);
        });

        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.page === 'action_config') {
                fetch('partials/action_config.php')
                .then(res => res.text())
                .then(html => {
                    app.innerHTML = html;
                })
                .catch(err => console.error('Error loading action_config:', err));
            } else {
                location.reload();
            }
        });
    </script>
</body>
</html>