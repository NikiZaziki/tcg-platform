<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Get match ID from URL
$matchId = $_GET['id'] ?? null;

if (!$matchId) {
    header('Location: poketmon.php');
    exit;
}

// Get match data
$match = apiCall('/matches/' . $matchId, 'GET', null, $_SESSION['token']);

if (!$match || isset($match['error'])) {
    header('Location: poketmon.php');
    exit;
}

// Determine if user is winner
$isWinner = ($match['winnerId'] ?? '') === ($user['id'] ?? '');
$isPlayer1 = ($match['player1']['id'] ?? '') === ($user['id'] ?? '');
$playerDeck = $isPlayer1 ? ($match['deck1'] ?? []) : ($match['deck2'] ?? []);
$opponent = $isPlayer1 ? ($match['player2'] ?? []) : ($match['player1'] ?? []);

// Get match history
$history = apiCall('/matches/' . $matchId . '/history', 'GET', null, $_SESSION['token']);

// Get ranked transfer info if ranked match
$rankedTransfer = null;
if (($match['mode'] ?? '') === 'ranked' && ($match['status'] ?? '') === 'finished') {
    // This would be implemented in the backend
    // For now, we'll show a placeholder
}

// Handle card selection for ranked transfer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'selectCard') {
    // This would call the backend to process the card transfer
    // For now, we'll just redirect
    header('Location: poketmon.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poketmon Ergebnis - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .result-container {
            text-align: center;
            padding: 3rem;
        }

        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        .result-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .result-subtitle {
            color: #a0a0a0;
            margin-bottom: 2rem;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #a0a0a0;
            font-size: 0.875rem;
        }

        .winner {
            color: #4ade80;
        }

        .loser {
            color: #f87171;
        }

        .card-transfer-section {
            background: rgba(233, 69, 96, 0.1);
            border: 1px solid #e94560;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .transfer-card {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .transfer-card:hover {
            border-color: #e94560;
            transform: translateY(-5px);
        }

        .transfer-card.selected {
            border-color: #4ade80;
            box-shadow: 0 0 20px rgba(74, 222, 128, 0.5);
        }

        .transfer-card-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .transfer-card-name {
            font-size: 0.75rem;
            color: #fff;
            text-align: center;
        }

        .transfer-card-rarity {
            font-size: 0.625rem;
            color: #a0a0a0;
            text-align: center;
            margin-top: 0.25rem;
        }

        .elo-change {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 0.5rem;
        }

        .elo-change.positive {
            color: #4ade80;
        }

        .elo-change.negative {
            color: #f87171;
        }

        .mode-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 0.5rem;
        }

        .mode-badge.ranked {
            background: linear-gradient(135deg, #e74c3c, #f39c12);
            color: #fff;
        }

        .mode-badge.unranked {
            background: rgba(255, 255, 255, 0.1);
            color: #a0a0a0;
        }
    </style>
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
                <a href="poketmon.php" class="active">Poketmon</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="result-container">
            <?php if ($isWinner): ?>
                <div class="result-icon">🏆</div>
                <div class="result-title winner">Gewonnen!</div>
                <div class="result-subtitle">
                    Du hast gegen <?php echo htmlspecialchars($opponent['username'] ?? 'Gegner'); ?> gewonnen
                </div>
            <?php else: ?>
                <div class="result-icon">😢</div>
                <div class="result-title loser">Verloren</div>
                <div class="result-subtitle">
                    <?php echo htmlspecialchars($opponent['username'] ?? 'Gegner'); ?> hat gewonnen
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 1rem;">
                <span class="mode-badge <?php echo ($match['mode'] ?? 'unranked'); ?>">
                    <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                </span>
            </div>

            <div class="result-stats">
                <div class="stat-box">
                    <div class="stat-value">
                        <?php echo count($history ?? []); ?>
                    </div>
                    <div class="stat-label">Spielzüge</div>
                </div>

                <div class="stat-box">
                    <div class="stat-value">
                        <?php echo count($playerDeck['cards'] ?? []); ?>
                    </div>
                    <div class="stat-label">Karten im Deck</div>
                </div>

                <?php if (($match['mode'] ?? '') === 'ranked'): ?>
                    <div class="stat-box">
                        <div class="stat-value">
                            <?php echo htmlspecialchars($user['eloRating'] ?? 1000); ?>
                        </div>
                        <div class="stat-label">Neues ELO</div>
                    </div>

                    <div class="stat-box">
                        <div class="elo-change <?php echo $isWinner ? 'positive' : 'negative'; ?>">
                            <?php echo $isWinner ? '+' : '-'; ?>32
                        </div>
                        <div class="stat-label">ELO Änderung</div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (($match['mode'] ?? '') === 'ranked' && !$isWinner && ($match['status'] ?? '') === 'finished'): ?>
                <div class="card-transfer-section">
                    <h2 style="margin-bottom: 1rem;">📦 Karten-Transfer</h2>
                    <p style="color: #a0a0a0; margin-bottom: 1rem;">
                        Da du diesen Ranked-Match verloren hast, musst du eine Karte aus deinem Deck an den Gewinner abgeben.
                    </p>

                    <form method="POST">
                        <input type="hidden" name="action" value="selectCard">

                        <div class="card-grid">
                            <?php if ($playerDeck && is_array($playerDeck['cards'] ?? []) && count($playerDeck['cards'] ?? []) > 0): ?>
                                <?php foreach ($playerDeck['cards'] as $card): ?>
                                    <?php $cardData = $card['card'] ?? []; ?>
                                    <div class="transfer-card" onclick="selectCard(this)">
                                        <div class="transfer-card-icon">🃏</div>
                                        <div class="transfer-card-name">
                                            <?php echo htmlspecialchars($cardData['name'] ?? 'Unknown'); ?>
                                        </div>
                                        <div class="transfer-card-rarity">
                                            <?php echo htmlspecialchars($cardData['rarity'] ?? 'Common'); ?>
                                        </div>
                                        <input type="hidden" name="cardId" value="<?php echo htmlspecialchars($cardData['id'] ?? ''); ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #a0a0a0;">Keine Karten im Deck</p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 1rem; width: 100%;" id="transferButton" disabled>
                            Karte übertragen
                        </button>
                    </form>
                </div>
            <?php elseif (($match['mode'] ?? '') === 'ranked' && $isWinner && ($match['status'] ?? '') === 'finished'): ?>
                <div class="card-transfer-section" style="border-color: #4ade80; background: rgba(74, 222, 128, 0.1);">
                    <h2 style="margin-bottom: 1rem;">🎁 Belohnung erhalten</h2>
                    <p style="color: #a0a0a0; margin-bottom: 1rem;">
                        Du hast eine Karte vom Verlierer erhalten!
                    </p>
                    <div style="font-size: 3rem; margin: 1rem;">🃏</div>
                    <p style="color: #4ade80; font-weight: bold;">Karte wurde deiner Collection hinzugefügt</p>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-top: 2rem;">
                <h2>Spielverlauf</h2>
                <?php if ($history && is_array($history) && count($history) > 0): ?>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($history as $move): ?>
                            <div style="padding: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <span style="color: #a0a0a0;">
                                    <?php echo date('H:i:s', strtotime($move['timestamp'] ?? 'now')); ?>:
                                </span>
                                <?php echo htmlspecialchars($move['playerId'] ?? 'Unknown'); ?> spielte Karte
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #a0a0a0;">Keine Spielzüge aufgezeichnet</p>
                <?php endif; ?>
            </div>

            <div style="margin-top: 2rem;">
                <a href="poketmon.php" class="btn btn-primary">Weiteres Match</a>
                <a href="dashboard.php" class="btn btn-secondary" style="margin-left: 1rem;">Zum Dashboard</a>
            </div>
        </div>
    </div>

    <script>
    let selectedCard = null;

    function selectCard(element) {
        selectedCard = element;

        // Update visual selection
        document.querySelectorAll('.transfer-card').forEach(card => {
            card.classList.remove('selected');
        });
        element.classList.add('selected');

        // Enable transfer button
        document.getElementById('transferButton').disabled = false;
    }
    </script>
</body>
</html>
