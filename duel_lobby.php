<?php
session_start();
require_once 'includes/db.php';

$stmt = $pdo->prepare('SELECT code_to_connect FROM sessions WHERE session_id = ?');
$stmt->execute([$_SESSION['session_id']]);
$session_code = $stmt->fetch();

if ($session_code && isset($session_code['code_to_connect'])) {
    $code = $session_code['code_to_connect'];
} else {
    $code = null;
}

if (!$code) {
    $error = "No code to connect found. Please start a new duel.";
}

//$joinUrl = "http://localhost/watchMatchDuel/join.php?code=" . urlencode($code);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);

$joinUrl = $protocol . $host . $path . "/join.php?code=" . urlencode($code);


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
    <title>Start a Duel - WatchMatch Duel</title>
</head>

<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.php' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 p-4 mt-5 mb-5 slide-right" id="wm-welcome-card">
                <div class="text-center">
                    <h1 class="mb-4">Send this <span class="text-highlight">URL</span> to the other player</span></h1>
                    <p class="lead mb-5 text-highlight">
                        <a class="no-link-style" href="<?php echo htmlspecialchars($joinUrl); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo htmlspecialchars($joinUrl); ?>
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="copy-link-btn" title="Copy link">
                            Copy link
                        </button>
                    </p>

                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1" style="border-top: 2px solid #ccc; margin: 0;">
                        <span class="mx-3 fw-bold text-secondary">OR</span>
                        <hr class="flex-grow-1" style="border-top: 2px solid #ccc; margin: 0;">
                    </div>

                    <h2 class="mb-4">Your <span class="text-highlight">code</span> to connect is</span></h1>
                    <p class="lead mb-5 text-highlight">
                        <?php echo htmlspecialchars($code); ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="copy-code-btn" title="Copy code">
                            Copy code
                        </button>
                    </p>
                </div>
            </div>

        </div>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script>
        const copyLinkBtn = document.getElementById('copy-link-btn');
        const copyCodeBtn = document.getElementById('copy-code-btn');
        const code = "<?php echo htmlspecialchars($code); ?>";
        const joinUrl = "<?php echo htmlspecialchars($joinUrl); ?>";

        copyLinkBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(joinUrl).then(() => {
                copyLinkBtn.textContent = 'Copied!';
                setTimeout(() => {
                    copyLinkBtn.textContent = 'Copy link';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });

        copyCodeBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(code).then(() => {
                copyCodeBtn.textContent = 'Copied!';
                setTimeout(() => {
                    copyCodeBtn.textContent = 'Copy code';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });
    </script>
    <script>
        setInterval(function(){
            fetch('check_connection.php')
                .then(response => response.json())
                .then(data => {
                    if (data.ready) {
                        window.location.href = 'duel.php?duelId=' + encodeURIComponent(data.duelId);
                    }
                })
                .catch(error => console.error('Error checking connection:', error));
        }, 1000);
    </script>
</body>

</html>