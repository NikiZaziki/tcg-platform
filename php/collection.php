<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Inventory laden
$inventory = apiCall('/inventory', 'GET', null, $_SESSION['token']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">TCG Platform</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="collection.php" class="active">Collection</a>
                <a href="decks.php">Decks</a>
                <a href="matches.php">Matches</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Deine Collection</h1>

        <div class="card">
            <h2>Filter</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
                <select style="padding: 0.5rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 0.5rem;">
                    <option>Alle Typen</option>
                    <option>Monster</option>
                    <option>Spell</option>
                    <option>Trap</option>
                </select>
                <select style="padding: 0.5rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 0.5rem;">
                    <option>Alle Seltenheiten</option>
                    <option>Common</option>
                    <option>Rare</option>
                    <option>Epic</option>
                    <option>Legendary</option>
                </select>
            </div>
        </div>

        <div class="grid">
            <?php if ($inventory && is_array($inventory) && count($inventory) > 0): ?>
                <?php foreach ($inventory as $item): ?>
                <div class="card" style="text-align: center;">
                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                        🃏
                    </div>
                    <h3><?php echo htmlspecialchars($item['name'] ?? 'Unknown'); ?></h3>
                    <p style="color: #a0a0a0; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['rarity'] ?? 'Common'); ?></p>
                    <p style="color: #e94560; margin-bottom: 0.5rem;">x<?php echo $item['quantity'] ?? 1; ?></p>
                    <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Details</button>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Keine Karten in deiner Collection gefunden.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
