<?php
/**
 * Poketmon Main Page
 * Matchmaking mit Ranked/Unranked
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/DeckService.php';
require_once __DIR__ . '/../../services/MatchmakingService.php';
require_once __DIR__ . '/../../services/MatchService.php';

$auth = new AuthService();
$deckService = new DeckService();
$matchmakingService = new MatchmakingService();
$matchService = new MatchService();

// Login erforderlich
$user = $auth->requireLogin();
$token = $auth->getToken();

// TCG ID für Poketmon (muss in der Datenbank existieren)
$POKETMON_TCG_ID = 'poketmon-tcg-id';

// Decks laden
$decks = $deckService->getAllDecks($token);

// Matchmaking Status prüfen
$queueStatus = $matchmakingService->getQueueStatus($token);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'joinQueue':
                $result = $matchmakingService->joinQueue($POKETMON_TCG_ID, $_POST['mode'] ?? 'unranked', $_POST['deckId'] ?? '', $token);
                break;
            case 'leaveQueue':
                $result = $matchmakingService->leaveQueue($token);
                break;
        }
        redirect('/pages/poketmon/index.php');
    }
}

// Aktive Matches laden
$activeMatches = $matchService->getActiveMatches($user['id'], $token);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poketmon - TCG Platform</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .mode-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .mode-card {
            flex: 1;
            padding: 2rem;
            border-radius: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .mode-card:hover {
            transform: translateY(-5px);
        }

        .mode-card.selected {
            border-color: #e94560;
        }

        .mode-card.unranked {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
        }

        .mode-card.ranked {
            background: linear-gradient(135deg, #2d1b4e, #1a1a2e);
        }

        .mode-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .mode-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .mode-description {
            color: #a0a0a0;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .mode-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mode-features li {
            color: #a0a0a0;
            font-size: 0.875rem;
            padding: 0.25rem 0;
        }

        .mode-features li::before {
            content: "✓ ";
            color: #4ade80;
        }

        .queue-status {
            background: rgba(233, 69, 96, 0.1);
            border: 1px solid #e94560;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .queue-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .queue-timer {
            font-size: 2rem;
            font-weight: bold;
            color: #e94560;
        }

        .rank-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: bold;
        }

        .rank-badge.bronze { background: #cd7f32; color: #fff; }
        .rank-badge.silver { background: #c0c0c0; color: #000; }
        .rank-badge.gold { background: #ffd700; color: #000; }
        .rank-badge.platinum { background: #e5e4e2; color: #000; }
        .rank-badge.diamond { background: #b9f2ff; color: #000; }
        .rank-badge.master { background: #9b59b6; color: #fff; }
        .rank-badge.grandmaster { background: #e74c3c; color: #fff; }
        .rank-badge.challenger { background: linear-gradient(135deg, #e74c3c, #f39c12); color: #fff; }
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
                <h1>Poketmon</h1>
                <p style="color: #a0a0a0;">Wähle deinen Spielmodus</p>
            </div>
            <div style="text-align: right;">
                <div style="color: #a0a0a0; font-size: 0.875rem;">Dein Rang</div>
                <span class="rank-badge <?php echo strtolower($user['rankTier'] ?? 'bronze'); ?>">
                    <?php echo htmlspecialchars($user['rankTier'] ?? 'Bronze'); ?>
                </span>
                <div style="color: #e94560; font-size: 1.25rem; margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($user['eloRating'] ?? 1000); ?> ELO
                </div>
            </div>
        </div>

        <?php if ($queueStatus['isInQueue'] ?? false): ?>
            <div class="queue-status">
                <div class="queue-info">
                    <div>
                        <h3 style="margin-bottom: 0.5rem;">Suche nach Gegner...</h3>
                        <p style="color: #a0a0a0; margin-bottom: 0;">
                            Modus: <strong><?php echo ucfirst($queueStatus['mode'] ?? 'Unranked'); ?></strong> |
                            Position: <strong><?php echo $queueStatus['position'] ?? 0; ?></strong> |
                            Spieler in Warteschlange: <strong><?php echo $queueStatus['queueSize'] ?? 0; ?></strong>
                        </p>
                    </div>
                    <div class="queue-timer" id="queueTimer">
                        <?php echo $queueStatus['estimatedWaitTime'] ?? 0; ?>s
                    </div>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="leaveQueue">
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">Warteschlange verlassen</button>
                </form>
            </div>
        <?php else: ?>
            <div class="mode-selector">
                <div class="mode-card unranked" onclick="selectMode('unranked')">
                    <div class="mode-icon">🎮</div>
                    <div class="mode-title">Unranked</div>
                    <div class="mode-description">Trainingskampf ohne Risiko</div>
                    <ul class="mode-features">
                        <li>Keine Punkte-Änderungen</li>
                        <li>Keine Karten-Verluste</li>
                        <li>Ideal zum Üben</li>
                        <li>Später auch gegen Bots</li>
                    </ul>
                </div>

                <div class="mode-card ranked" onclick="selectMode('ranked')">
                    <div class="mode-icon">🏆</div>
                    <div class="mode-title">Ranked</div>
                    <div class="mode-description">Kampf um Ränge und Karten</div>
                    <ul class="mode-features">
                        <li>ELO-Punkte gewinnen/verlieren</li>
                        <li>Rang aufsteigen</li>
                        <li>Verlierer gibt Karte ab</li>
                        <li>Nur für erfahrene Spieler</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <h2>Deck auswählen</h2>
                <form method="POST" id="matchForm">
                    <input type="hidden" name="action" value="joinQueue">
                    <input type="hidden" name="mode" id="selectedMode" value="unranked">

                    <div class="form-group">
                        <label>Wähle dein Deck</label>
                        <select name="deckId" id="deckSelect" required>
                            <option value="">-- Deck auswählen --</option>
                            <?php if ($decks && is_array($decks) && count($decks) > 0): ?>
                                <?php foreach ($decks as $deck): ?>
                                    <option value="<?php echo htmlspecialchars($deck['id'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($deck['name'] ?? 'Unknown'); ?>
                                        (<?php echo count($deck['cards'] ?? []); ?> Karten)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Keine Decks verfügbar</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        Matchmaking starten
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($activeMatches && is_array($activeMatches) && count($activeMatches) > 0): ?>
            <div class="card" style="margin-top: 2rem;">
                <h2>Aktive Matches</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Gegner</th>
                            <th>Modus</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeMatches as $match): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars(
                                        ($match['player1']['id'] ?? '') === ($user['id'] ?? '')
                                            ? ($match['player2']['username'] ?? 'Unknown')
                                            : ($match['player1']['username'] ?? 'Unknown')
                                    ); ?>
                                </td>
                                <td>
                                    <span class="rank-badge <?php echo strtolower($match['mode'] ?? 'unranked'); ?>">
                                        <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #4ade80;">
                                        <?php echo ucfirst($match['status'] ?? 'Active'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/pages/poketmon/game.php?id=<?php echo htmlspecialchars($match['id'] ?? ''); ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                        Spielen
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
    let selectedMode = 'unranked';

    function selectMode(mode) {
        selectedMode = mode;
        document.getElementById('selectedMode').value = mode;

        // Update visual selection
        document.querySelectorAll('.mode-card').forEach(card => {
            card.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
    }

    // Queue timer countdown
    <?php if ($queueStatus['isInQueue'] ?? false): ?>
    let timeLeft = <?php echo $queueStatus['estimatedWaitTime'] ?? 0; ?>;

    setInterval(() => {
        if (timeLeft > 0) {
            timeLeft--;
            document.getElementById('queueTimer').textContent = timeLeft + 's';
        }
    }, 1000);

    // Auto-refresh to check for match
    setInterval(() => {
        location.reload();
    }, 5000);
    <?php endif; ?>
    </script>
</body>
</html>
