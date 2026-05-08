<?php
/**
 * Poketmon Game Page
 * Spiel-Interface für Poketmon
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/MatchService.php';

$auth = new AuthService();
$matchService = new MatchService();

// Login erforderlich
$user = $auth->requireLogin();
$token = $auth->getToken();

// Get match ID from URL
$matchId = $_GET['id'] ?? null;

if (!$matchId) {
    redirect('/pages/poketmon/index.php');
}

// Get match data
$match = $matchService->getMatch($matchId, $token);

if (!$match) {
    redirect('/pages/poketmon/index.php');
}

// Determine if user is player1 or player2
$isPlayer1 = ($match['player1']['id'] ?? '') === ($user['id'] ?? '');
$playerDeck = $isPlayer1 ? ($match['deck1'] ?? []) : ($match['deck2'] ?? []);
$opponentDeck = $isPlayer1 ? ($match['deck2'] ?? []) : ($match['deck1'] ?? []);
$opponent = $isPlayer1 ? ($match['player2'] ?? []) : ($match['player1'] ?? []);

// Get match history
$history = $matchService->getMatchHistory($matchId, $token);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'playCard':
                $result = $matchService->submitMove($matchId, $user['id'], $_POST['cardId'] ?? '', $_POST['position'] ?? 0, $token);
                break;
            case 'endMatch':
                $result = $matchService->endMatch($matchId, $_POST['winnerId'] ?? '', $token);
                redirect('/pages/poketmon/result.php?id=' . $matchId);
                break;
        }
        redirect('/pages/poketmon/game.php?id=' . $matchId);
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poketmon Spiel - TCG Platform</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .game-board {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .player-area {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .battle-area {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border-radius: 1rem;
            padding: 2rem;
            border: 2px solid rgba(233, 69, 96, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }

        .card-slot {
            width: 120px;
            height: 170px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .card-slot:hover {
            border-color: #e94560;
            background: rgba(233, 69, 96, 0.1);
        }

        .card-slot.occupied {
            border-style: solid;
            border-color: #4ade80;
        }

        .card-slot.enemy {
            border-color: #f87171;
        }

        .hand {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 1rem;
        }

        .hand-card {
            width: 100px;
            height: 140px;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0.5rem;
        }

        .hand-card:hover {
            transform: translateY(-10px);
            border-color: #e94560;
        }

        .hand-card.selected {
            border-color: #4ade80;
            box-shadow: 0 0 20px rgba(74, 222, 128, 0.5);
        }

        .hand-card-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .hand-card-name {
            font-size: 0.75rem;
            color: #fff;
            text-align: center;
        }

        .hand-card-stats {
            font-size: 0.625rem;
            color: #a0a0a0;
            margin-top: 0.25rem;
        }

        .battle-field {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            width: 100%;
            max-width: 400px;
        }

        .player-info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .player-name {
            font-size: 1.25rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.25rem;
        }

        .player-stats {
            font-size: 0.875rem;
            color: #a0a0a0;
        }

        .hp-bar {
            width: 100%;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .hp-fill {
            height: 100%;
            background: linear-gradient(90deg, #4ade80, #22c55e);
            transition: width 0.5s ease;
        }

        .hp-fill.low {
            background: linear-gradient(90deg, #f87171, #ef4444);
        }

        .hp-fill.medium {
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
        }

        .turn-indicator {
            background: rgba(233, 69, 96, 0.2);
            border: 1px solid #e94560;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .turn-indicator h3 {
            margin: 0;
            color: #e94560;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
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
                <a href="/pages/index.php">Home</a>
                <a href="/pages/dashboard.php">Dashboard</a>
                <a href="/pages/collection.php">Collection</a>
                <a href="/pages/decks.php">Decks</a>
                <a href="/pages/poketmon/index.php" class="active">Poketmon</a>
                <a href="/pages/trading.php">Trading</a>
                <a href="/pages/shop.php">Shop</a>
                <a href="/pages/rewards.php">Rewards</a>
                <a href="/pages/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>Poketmon <span class="mode-badge <?php echo ($match['mode'] ?? 'unranked'); ?>">
                    <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                </span></h1>
                <p style="color: #a0a0a0;">Match ID: <?php echo htmlspecialchars(substr($matchId ?? '', 0, 8)); ?>...</p>
            </div>
            <a href="/pages/poketmon/index.php" class="btn btn-secondary">Verlassen</a>
        </div>

        <?php if (($match['status'] ?? '') === 'active'): ?>
            <div class="turn-indicator">
                <h3>🎮 Dein Zug</h3>
                <p style="color: #a0a0a0; margin: 0;">Wähle eine Karte aus deiner Hand und spiele sie auf das Spielfeld</p>
            </div>

            <div class="game-board">
                <!-- Gegner Bereich -->
                <div class="player-area">
                    <div class="player-info">
                        <div class="player-name">
                            <?php echo htmlspecialchars($opponent['username'] ?? 'Gegner'); ?>
                        </div>
                        <div class="player-stats">
                            ELO: <?php echo htmlspecialchars($opponent['eloRating'] ?? 1000); ?>
                        </div>
                        <div class="hp-bar">
                            <div class="hp-fill" style="width: 100%;"></div>
                        </div>
                    </div>

                    <h4 style="color: #a0a0a0; margin-bottom: 0.5rem;">Gegner Hand</h4>
                    <div class="hand">
                        <?php for ($i = 0; $i < min(5, count($opponentDeck['cards'] ?? [])); $i++): ?>
                            <div class="hand-card" style="background: linear-gradient(135deg, #2d1b4e, #1a1a2e);">
                                <div class="hand-card-icon">🃏</div>
                                <div class="hand-card-name">?</div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Kampfbereich -->
                <div class="battle-area">
                    <div class="battle-field">
                        <!-- Gegner Feld -->
                        <div class="card-slot enemy"></div>
                        <div class="card-slot enemy"></div>
                        <div class="card-slot enemy"></div>

                        <!-- Mittlere Reihe -->
                        <div class="card-slot"></div>
                        <div class="card-slot" style="border-color: #e94560; background: rgba(233, 69, 96, 0.1);">
                            <span style="font-size: 2rem;">⚔️</span>
                        </div>
                        <div class="card-slot"></div>

                        <!-- Spieler Feld -->
                        <div class="card-slot"></div>
                        <div class="card-slot"></div>
                        <div class="card-slot"></div>
                    </div>
                </div>

                <!-- Spieler Bereich -->
                <div class="player-area">
                    <div class="player-info">
                        <div class="player-name">
                            <?php echo htmlspecialchars($user['username'] ?? 'Du'); ?>
                        </div>
                        <div class="player-stats">
                            ELO: <?php echo htmlspecialchars($user['eloRating'] ?? 1000); ?>
                        </div>
                        <div class="hp-bar">
                            <div class="hp-fill" style="width: 100%;"></div>
                        </div>
                    </div>

                    <h4 style="color: #a0a0a0; margin-bottom: 0.5rem;">Deine Hand</h4>
                    <form method="POST" id="playCardForm">
                        <input type="hidden" name="action" value="playCard">
                        <input type="hidden" name="cardId" id="selectedCardId">
                        <input type="hidden" name="position" id="selectedPosition" value="0">

                        <div class="hand">
                            <?php if ($playerDeck && is_array($playerDeck['cards'] ?? []) && count($playerDeck['cards'] ?? []) > 0): ?>
                                <?php foreach ($playerDeck['cards'] as $index => $card): ?>
                                    <?php $cardData = $card['card'] ?? []; ?>
                                    <div class="hand-card" onclick="selectCard('<?php echo htmlspecialchars($cardData['id'] ?? ''); ?>', this)">
                                        <div class="hand-card-icon">🃏</div>
                                        <div class="hand-card-name">
                                            <?php echo htmlspecialchars($cardData['name'] ?? 'Unknown'); ?>
                                        </div>
                                        <div class="hand-card-stats">
                                            ATK: <?php echo $cardData['attack'] ?? 0; ?> | DEF: <?php echo $cardData['defense'] ?? 0; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #a0a0a0;">Keine Karten in der Hand</p>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary" id="playButton" disabled>
                                Karte spielen
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="endMatch()">
                                Match beenden
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Match Historie -->
            <div class="card">
                <h2>Spielverlauf</h2>
                <?php if ($history && is_array($history) && count($history) > 0): ?>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php foreach (array_reverse($history) as $move): ?>
                            <div style="padding: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <span style="color: #a0a0a0;">
                                    <?php echo date('H:i:s', strtotime($move['timestamp'] ?? 'now')); ?>:
                                </span>
                                <?php echo htmlspecialchars($move['playerId'] ?? 'Unknown'); ?> spielte Karte
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #a0a0a0;">Noch keine Spielzüge</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h2 style="margin-bottom: 1rem;">Match beendet</h2>
                <p style="color: #a0a0a0; margin-bottom: 2rem;">
                    Status: <?php echo ucfirst($match['status'] ?? 'Unknown'); ?>
                </p>
                <a href="/pages/poketmon/result.php?id=<?php echo htmlspecialchars($matchId ?? ''); ?>" class="btn btn-primary">
                    Ergebnisse anzeigen
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Match beenden Modal -->
    <div id="endMatchModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 400px; width: 100%;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Match beenden</h2>
            <p style="color: #a0a0a0; text-align: center; margin-bottom: 1.5rem;">
                Wer hat gewonnen?
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="endMatch">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <button type="submit" name="winnerId" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>" class="btn btn-primary">
                        Ich habe gewonnen
                    </button>
                    <button type="submit" name="winnerId" value="<?php echo htmlspecialchars($opponent['id'] ?? ''); ?>" class="btn btn-secondary">
                        Gegner hat gewonnen
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('endMatchModal').style.display = 'none'">
                        Abbrechen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let selectedCard = null;

    function selectCard(cardId, element) {
        selectedCard = cardId;
        document.getElementById('selectedCardId').value = cardId;

        // Update visual selection
        document.querySelectorAll('.hand-card').forEach(card => {
            card.classList.remove('selected');
        });
        element.classList.add('selected');

        // Enable play button
        document.getElementById('playButton').disabled = false;
    }

    function endMatch() {
        if (confirm('Möchtest du das Match wirklich beenden?')) {
            document.getElementById('endMatchModal').style.display = 'flex';
        }
    }

    // Auto-refresh for active matches
    <?php if (($match['status'] ?? '') === 'active'): ?>
    setInterval(() => {
        location.reload();
    }, 10000);
    <?php endif; ?>
    </script>
</body>
</html>
