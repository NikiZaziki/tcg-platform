<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Decks laden
$decks = apiCall('/decks', 'GET', null, $_SESSION['token']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = apiCall('/decks', 'POST', [
                    'tcgId' => $_POST['tcgId'] ?? '',
                    'name' => $_POST['name'] ?? ''
                ], $_SESSION['token']);
                break;
            case 'delete':
                $result = apiCall('/decks/' . ($_POST['deckId'] ?? ''), 'DELETE', null, $_SESSION['token']);
                break;
        }
        header('Location: decks.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decks - TCG Platform</title>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Deine Decks</h1>
            <button class="btn btn-primary" onclick="document.getElementById('createDeckModal').style.display = 'flex'">+ Neues Deck</button>
        </div>

        <div class="grid">
            <?php if ($decks && is_array($decks) && count($decks) > 0): ?>
                <?php foreach ($decks as $deck): ?>
                <div class="card">
                    <h2><?php echo htmlspecialchars($deck['name'] ?? 'Unknown'); ?></h2>
                    <p style="color: #a0a0a0; margin-bottom: 1rem;"><?php echo count($deck['cards'] ?? 0); ?>/60 Karten</p>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="deck-edit.php?id=<?php echo htmlspecialchars($deck['id'] ?? ''); ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Bearbeiten</a>
                        <a href="deck-test.php?id=<?php echo htmlspecialchars($deck['id'] ?? ''); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Testen</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="deckId" value="<?php echo htmlspecialchars($deck['id'] ?? ''); ?>">
                            <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #f87171 !important;">Löschen</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Keine Decks gefunden. Erstelle dein erstes Deck!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Deck Modal -->
    <div id="createDeckModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 400px; width: 100%;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Neues Deck erstellen</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Deck Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>TCG ID</label>
                    <input type="text" name="tcgId" required>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Erstellen</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createDeckModal').style.display = 'none'" style="flex: 1;">Abbrechen</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
