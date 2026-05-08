<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Get match ID from URL
$matchId = $_GET['id'] ?? null;

// Get match data
$match = null;
if ($matchId) {
    $match = apiCall('/matches/' . $matchId, 'GET', null, $_SESSION['token']);
}

// Get match history
$history = null;
if ($matchId) {
    $history = apiCall('/matches/' . $matchId . '/history', 'GET', null, $_SESSION['token']);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Details - TCG Platform</title>
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
                <a href="matches.php" class="active">Matches</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($match && !isset($match['error'])): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Match: <?php echo htmlspecialchars($match['id'] ?? 'Unknown'); ?></h1>
                <a href="matches.php" class="btn btn-secondary">Zurück</a>
            </div>

            <div class="card">
                <h2>Match Details</h2>
                <p><strong>Gegner:</strong> <?php echo htmlspecialchars($match['opponent'] ?? 'Unknown'); ?></p>
                <p><strong>Deck:</strong> <?php echo htmlspecialchars($match['deck'] ?? 'Unknown'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($match['status'] ?? 'Unknown'); ?></p>
            </div>

            <div class="card">
                <h2>Match History</h2>
                <?php if ($history && is_array($history) && count($history) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Runde</th>
                            <th>Aktion</th>
                            <th>Zeit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $move): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($move['round'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($move['action'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($move['timestamp'] ?? 'Unknown'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Noch keine Spielzüge in diesem Match.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Aktionen</h2>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary" onclick="submitMove()">Zug machen</button>
                    <button class="btn btn-secondary" onclick="endMatch()">Match beenden</button>
                </div>
            </div>
        <?php else: ?>
            <p>Match nicht gefunden.</p>
        <?php endif; ?>
    </div>

    <script>
    function submitMove() {
        alert('Zug machen Funktion - Hier würde die API aufgerufen werden');
    }

    function endMatch() {
        if (confirm('Möchtest du das Match wirklich beenden?')) {
            alert('Match beenden Funktion - Hier würde die API aufgerufen werden');
        }
    }
    </script>
</body>
</html>
