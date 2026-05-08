<?php
require_once 'config.php';

// User prüfen
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCG Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">TCG Platform</div>
            <div class="nav-links">
                <a href="index.php" class="active">Home</a>
                <?php if($user): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="collection.php">Collection</a>
                    <a href="decks.php">Decks</a>
                    <a href="matches.php">Matches</a>
                    <a href="trading.php">Trading</a>
                    <a href="shop.php">Shop</a>
                    <a href="rewards.php">Rewards</a>
                    <a href="logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Login</a>
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
                    <a href="dashboard.php" class="btn btn-primary">Zum Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Jetzt starten</a>
                    <a href="register.php" class="btn btn-secondary">Registrieren</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
