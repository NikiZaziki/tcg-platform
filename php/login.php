<?php
require_once 'config.php';

// Redirect wenn bereits eingeloggt
requireGuest();

$error = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Bitte alle Felder ausfüllen';
    } else {
        logError("Login attempt for user: $username");

        $result = apiCall('/auth/login', 'POST', [
            'email' => $username,
            'password' => $password
        ]);

        logError("Login result: " . json_encode($result));

        if (isset($result['token'])) {
            $_SESSION['token'] = $result['token'];
            $_SESSION['user'] = $result['user'];
            logError("Login successful for user: $username");
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['error'] ?? 'Login fehlgeschlagen';
            logError("Login failed: $error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">TCG Platform</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="register.php">Registrieren</a>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="card" style="max-width: 400px; width: 100%;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Login</h2>
            <?php if($error): ?>
                <p style="color: #f87171; text-align: center; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email oder Benutzername</label>
                    <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Passwort</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Einloggen</button>
            </form>
            <p style="text-align: center; margin-top: 1rem; color: #a0a0a0;">
                Noch kein Account? <a href="register.php" style="color: #e94560;">Registrieren</a>
            </p>
        </div>
    </div>
</body>
</html>
