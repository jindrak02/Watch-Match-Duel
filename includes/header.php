<header>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">

            <?php if(!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']): ?>
                <!-- Obecný Navbar -->
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    WatchMatch
                    <img src="public/images/icon.png" alt="Popcorn Icon" class="wmduel-logo">
                    <span>Duel</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                            fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                        </svg>
                    </span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="login.php">Login</a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']): ?>
                <!-- Navbar pro přihlášeného uživatele -->
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    WatchMatch
                    <img src="public/images/icon.png" alt="Popcorn Icon" class="wmduel-logo">
                    <span>Duel</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                            fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                        </svg>
                    </span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <span class="navbar-text text-white me-4">
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </span>

                        <li class="nav-item me-4">
                            <a class="nav-link active" aria-current="page" href="logout.php" id="logout">Log out</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="history.php">My history</a>
                        </li>
                    </ul>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var logoutLink = document.getElementById('logout');
                        if (logoutLink) {
                            logoutLink.addEventListener('click', function(e) {
                                if (!confirm('Do you really want to log out?')) {
                                    e.preventDefault();
                                }
                            });
                        }
                    });
                </script>
            <?php endif; ?>


        </div>
    </nav>
</header>