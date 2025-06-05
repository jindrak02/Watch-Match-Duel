<?php 
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $error = '';

    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    if (!$username || !$email || !$password || !$confirm_password) {
        $error = 'Vyplňte všechna pole.';
    }

    if ($password !== $confirm_password) {
        $error = 'Hesla se neshodují.';
    }

    if (strlen($password) < 10 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
        $error = 'Heslo musí mít alespoň 10 znaků, obsahovat velké písmeno a číslo.';
    }

    if (empty($error)) {
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetchColumn() > 0) {
            $error = 'Uživatel s tímto emailem již existuje.';
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, is_guest) VALUES (?, ?, ?, 0)');
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['is_logged_in'] = true;

            header('Location: index.php');
            exit;
        } else {
            $error = 'Chyba při registraci. Zkuste to prosím znovu.';
        }
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
    <title>Register - WatchMatch Duel</title>
</head>

<body>

    <div class="d-flex flex-column min-vh-100">
        <?php include 'includes/header.php' ?>

        <div class="container flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="app">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card flex-column-center" style="width: 30rem;">
                <div class="card-body text-center">
                    <h1 class="card-title text-highlight">Register</h5>
                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                            value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                    <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>

        </div>

        <?php include 'includes/footer.html' ?>
    </div>




    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const form = passwordInput.closest('form');
            let feedback = document.createElement('div');
            feedback.className = 'form-text text-start';
            passwordInput.parentNode.appendChild(feedback);

            function validatePassword(pw) {
                const minLength = pw.length >= 10;
                const hasUpper = /[A-Z]/.test(pw);
                const hasNumber = /\d/.test(pw);
                return { minLength, hasUpper, hasNumber };
            }

            function updateFeedback() {
                const pw = passwordInput.value;
                const v = validatePassword(pw);
                feedback.innerHTML = `
                    <span style="color:${v.minLength ? 'green' : 'yellow'}">• At least 10 characters</span><br>
                    <span style="color:${v.hasUpper ? 'green' : 'yellow'}">• At least one uppercase letter</span><br>
                    <span style="color:${v.hasNumber ? 'green' : 'yellow'}">• At least one number</span>
                `;
            }

            passwordInput.addEventListener('input', updateFeedback);
            updateFeedback();

            form.addEventListener('submit', function(e) {
                const pw = passwordInput.value;
                const v = validatePassword(pw);
                if (!(v.minLength && v.hasUpper && v.hasNumber)) {
                    e.preventDefault();
                    feedback.style.color = 'red';
                    feedback.innerHTML += '<br><strong>Password does not meet requirements.</strong>';
                    passwordInput.focus();
                }
                
                const confirmPassword = document.getElementById('confirm_password').value;
                if (pw !== confirmPassword) {
                    e.preventDefault();
                    feedback.style.color = 'red';
                    feedback.innerHTML += '<br><strong>Passwords do not match.</strong>';
                    document.getElementById('confirm_password').focus();
                }
            });
        });
    </script>
</body>

</html>