<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Rewards laden
$rewards = apiCall('/rewards/daily', 'GET', null, $_SESSION['token']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'claim':
                $result = apiCall('/rewards/daily/claim', 'POST', null, $_SESSION['token']);
                break;
        }
        header('Location: rewards.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rewards - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">TCG Platform</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="collection.php">Collection</a>
                <a href="decks.php">Decks</a>
                <a href="matches.php">Matches</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php" class="active">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Rewards</h1>
            <div style="color: #e94560; font-size: 1.25rem;">🏆 Level 5</div>
        </div>

        <div class="card">
            <h2>Tägliche Belohnungen</h2>
            <div class="grid">
                <div class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎁</div>
                    <h3>Täglicher Login</h3>
                    <p style="color: #a0a0a0; margin-bottom: 1rem;">+50 Coins</p>
                    <?php if ($rewards && is_array($rewards) && !($rewards['claimed'] ?? false)): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="claim">
                            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Abholen</button>
                        </form>
                    <?php elseif ($rewards && is_array($rewards) && ($rewards['claimed'] ?? false)): ?>
                        <p style="color: #4ade80;">Bereits abgeholt!</p>
                    <?php else: ?>
                        <p style="color: #f87171;">Fehler beim Abrufen</p>
                    <?php endif; ?>
                </div>

                <div class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
                    <h3>Daily Quest</h3>
                    <p style="color: #a0a0a0; margin-bottom: 1rem;">1 Match gewinnen</p>
                    <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">+100 Coins</button>
                </div>

                <div class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🏆</div>
                    <h3>Weekly Challenge</h3>
                    <p style="color: #a0a0a0; margin-bottom: 1rem;">5 Matches gewinnen</p>
                    <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">+500 Coins</button>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Achievements</h2>
            <table>
                <thead>
                    <tr>
                        <th>Achievement</th>
                        <th>Beschreibung</th>
                        <th>Status</th>
                        <th>Belohnung</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>🎯 First Win</td>
                        <td>Gewinn dein erstes Match</td>
                        <td><span style="color: #4ade80;">Abgeschlossen</span></td>
                        <td>100 Coins</td>
                    </tr>
                    <tr>
                        <td>🃏 Collector</td>
                        <td>Sammle 10 Karten</td>
                        <td><span style="color: #4ade80;">Abgeschlossen</span></td>
                        <td>200 Coins</td>
                    </tr>
                    <tr>
                        <td>🏆 Champion</td>
                        <td>Gewinn 10 Matches</td>
                        <td><span style="color: #fbbf24;">5/10</span></td>
                        <td>500 Coins</td>
                    </tr>
                    <tr>
                        <td>👑 Master</td>
                        <td>Erreiche Level 10</td>
                        <td><span style="color: #fbbf24;">5/10</span></td>
                        <td>1000 Coins</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
