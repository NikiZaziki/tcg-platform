<?php
/**
 * Register Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/AuthService.php';

$auth = new AuthService();

// Redirect wenn bereits eingeloggt
$auth->requireGuest();

$error = '';
$success = '';

// Handle Registrierung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    logError("Registration attempt for email: $email, username: $username");

    // Validierung
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Bitte alle Felder ausfüllen';
    } elseif (strlen($username) < 3) {
        $error = 'Benutzername muss mindestens 3 Zeichen lang sein';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ungültige Email-Adresse';
    } elseif (strlen($password) < 6) {
        $error = 'Passwort muss mindestens 6 Zeichen lang sein';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwörter stimmen nicht überein';
    } else {
        $result = $auth->register($email, $username, $password);

        logError("Registration result: " . json_encode($result));

        if ($result['success']) {
            logError("Registration successful for user: $username");
            redirect('/pages/dashboard.php');
        } else {
            $error = $result['error'] ?? 'Registrierung fehlgeschlagen';
            logError("Registration failed: $error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren - TCG Platform</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">TCG Platform</div>
            <div class="nav-links">
                <a href="/pages/index.php">Home</a>
                <a href="/pages/login.php">Login</a>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="card" style="max-width: 400px; width: 100%;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Registrieren</h2>
            <?php if($error): ?>
                <p style="color: #f87171; text-align: center; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Benutzername</label>
                    <input type="text" name="username" required minlength="3" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Passwort</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Passwort bestätigen</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Registrieren</button>
            </form>
            <p style="text-align: center; margin-top: 1rem; color: #a0a0a0;">
                Bereits registriert? <a href="/pages/login.php" style="color: #e94560;">Einloggen</a>
            </p>
        </div>
    </div>
</body>
</html>
