<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Get deck ID from URL
$deckId = $_GET['id'] ?? null;

// Get deck data
$deck = null;
if ($deckId) {
    $deck = apiCall('/decks/' . $deckId, 'GET', null, $_SESSION['token']);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deck testen - TCG Platform</title>
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
                <a href="decks.php" class="active">Decks</a>
                <a href="matches.php">Matches</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($deck && !isset($deck['error'])): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Deck testen: <?php echo htmlspecialchars($deck['name'] ?? 'Unknown'); ?></h1>
                <a href="decks.php" class="btn btn-secondary">Zurück</a>
            </div>

            <div class="card">
                <h2>Deck Informationen</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($deck['name'] ?? 'Unknown'); ?></p>
                <p><strong>Karten:</strong> <?php echo count($deck['cards'] ?? []); ?>/60</p>
                <p><strong>TCG ID:</strong> <?php echo htmlspecialchars($deck['tcgId'] ?? 'Unknown'); ?></p>
            </div>

            <div class="card">
                <h2>Karten im Deck</h2>
                <div class="grid">
                    <?php if ($deck['cards'] && is_array($deck['cards']) && count($deck['cards']) > 0): ?>
                        <?php foreach ($deck['cards'] as $card): ?>
                        <div class="card" style="text-align: center;">
                            <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                🃏
                            </div>
                            <h3><?php echo htmlspecialchars($card['name'] ?? 'Unknown'); ?></h3>
                            <p style="color: #a0a0a0; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($card['rarity'] ?? 'Common'); ?></p>
                            <p style="color: #e94560; margin-bottom: 0.5rem;">x<?php echo $card['quantity'] ?? 1; ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Keine Karten im Deck.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h2>Aktionen</h2>
                <div style="display: flex; gap: 1rem;">
                    <a href="deck-edit.php?id=<?php echo htmlspecialchars($deckId ?? ''); ?>" class="btn btn-primary">Deck bearbeiten</a>
                    <button class="btn btn-secondary" onclick="startMatch()">Match starten</button>
                </div>
            </div>
        <?php else: ?>
            <p>Deck nicht gefunden.</p>
        <?php endif; ?>
    </div>

    <script>
    function startMatch() {
        alert('Match starten Funktion - Hier würde die API aufgerufen werden');
    }
    </script>
</body>
</html>
