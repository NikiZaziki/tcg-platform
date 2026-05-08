<?php
/**
 * Index Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/AuthService.php';

$auth = new AuthService();

// User prüfen
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCG Platform</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">TCG Platform</div>
            <div class="nav-links">
                <a href="/pages/index.php" class="active">Home</a>
                <?php if($user): ?>
                    <a href="/pages/dashboard.php">Dashboard</a>
                    <a href="/pages/collection.php">Collection</a>
                    <a href="/pages/decks.php">Decks</a>
                    <a href="/pages/poketmon/index.php">Poketmon</a>
                    <a href="/pages/trading.php">Trading</a>
                    <a href="/pages/shop.php">Shop</a>
                    <a href="/pages/rewards.php">Rewards</a>
                    <a href="/pages/logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="/pages/login.php" class="btn-login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="hero">
        <div class="hero-content">
            <h1>TCG Platform</h1>
            <p>Multi-Game Trading Card Game Platform</p>
            <div class="hero-buttons">
                <?php if($user): ?>
                    <a href="/pages/dashboard.php" class="btn btn-primary">Zum Dashboard</a>
                <?php else: ?>
                    <a href="/pages/login.php" class="btn btn-primary">Jetzt starten</a>
                    <a href="/pages/register.php" class="btn btn-secondary">Registrieren</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
