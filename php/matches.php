<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Get all matches
$matches = apiCall('/matches', 'GET', null, $_SESSION['token']);

// Filter matches by user
$userMatches = [];
if ($matches && is_array($matches)) {
    foreach ($matches as $match) {
        if (($match['player1']['id'] ?? '') === ($user['id'] ?? '') ||
            ($match['player2']['id'] ?? '') === ($user['id'] ?? '')) {
            $userMatches[] = $match;
        }
    }
}

// Separate active and finished matches
$activeMatches = array_filter($userMatches, function($match) {
    return ($match['status'] ?? '') === 'active';
});

$finishedMatches = array_filter($userMatches, function($match) {
    return ($match['status'] ?? '') === 'finished';
});
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poketmon Matches - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
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

        .match-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .match-card.active {
            border-color: #e94560;
            box-shadow: 0 0 20px rgba(233, 69, 96, 0.2);
        }

        .match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .match-players {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .match-player {
            text-align: center;
        }

        .match-player-name {
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.25rem;
        }

        .match-player-elo {
            font-size: 0.875rem;
            color: #a0a0a0;
        }

        .match-vs {
            font-size: 1.5rem;
            color: #e94560;
            font-weight: bold;
        }

        .match-result {
            text-align: center;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .match-result.win {
            background: rgba(74, 222, 128, 0.1);
            color: #4ade80;
        }

        .match-result.loss {
            background: rgba(248, 113, 113, 0.1);
            color: #f87171;
        }

        .match-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .tab-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .tab-button {
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            color: #a0a0a0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-button:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .tab-button.active {
            background: rgba(233, 69, 96, 0.2);
            border-color: #e94560;
            color: #e94560;
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Poketmon Matches</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="poketmon-ranks.php" class="btn btn-secondary">Ränge & Statistiken</a>
                <a href="poketmon.php" class="btn btn-primary">Neues Match</a>
            </div>
        </div>

        <div class="tab-buttons">
            <button class="tab-button active" onclick="showTab('active')">Aktive Matches</button>
            <button class="tab-button" onclick="showTab('finished')">Verlauf</button>
        </div>

        <div id="active-matches">
            <h2 style="margin-bottom: 1rem;">Aktive Matches</h2>
            <?php if (count($activeMatches) > 0): ?>
                <?php foreach ($activeMatches as $match): ?>
                    <?php
                    $isPlayer1 = ($match['player1']['id'] ?? '') === ($user['id'] ?? '');
                    $opponent = $isPlayer1 ? ($match['player2'] ?? []) : ($match['player1'] ?? []);
                    ?>
                    <div class="match-card active">
                        <div class="match-header">
                            <div>
                                <span style="color: #a0a0a0;">Match ID:</span>
                                <?php echo htmlspecialchars(substr($match['id'] ?? '', 0, 8)); ?>...
                                <span class="mode-badge <?php echo ($match['mode'] ?? 'unranked'); ?>">
                                    <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                                </span>
                            </div>
                            <div style="color: #a0a0a0; font-size: 0.875rem;">
                                <?php echo date('H:i', strtotime($match['createdAt'] ?? 'now')); ?>
                            </div>
                        </div>

                        <div class="match-players">
                            <div class="match-player">
                                <div class="match-player-name">
                                    <?php echo htmlspecialchars($user['username'] ?? 'Du'); ?>
                                </div>
                                <div class="match-player-elo">
                                    <?php echo htmlspecialchars($user['eloRating'] ?? 1000); ?> ELO
                                </div>
                            </div>

                            <div class="match-vs">VS</div>

                            <div class="match-player">
                                <div class="match-player-name">
                                    <?php echo htmlspecialchars($opponent['username'] ?? 'Gegner'); ?>
                                </div>
                                <div class="match-player-elo">
                                    <?php echo htmlspecialchars($opponent['eloRating'] ?? 1000); ?> ELO
                                </div>
                            </div>
                        </div>

                        <div class="match-actions">
                            <a href="poketmon-game.php?id=<?php echo htmlspecialchars($match['id'] ?? ''); ?>" class="btn btn-primary" style="flex: 1;">
                                Spielen
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 2rem;">
                    <p style="color: #a0a0a0; margin-bottom: 1rem;">Keine aktiven Matches</p>
                    <a href="poketmon.php" class="btn btn-primary">Matchmaking starten</a>
                </div>
            <?php endif; ?>
        </div>

        <div id="finished-matches" style="display: none;">
            <h2 style="margin-bottom: 1rem;">Match Verlauf</h2>
            <?php if (count($finishedMatches) > 0): ?>
                <?php foreach ($finishedMatches as $match): ?>
                    <?php
                    $isWinner = ($match['winnerId'] ?? '') === ($user['id'] ?? '');
                    $isPlayer1 = ($match['player1']['id'] ?? '') === ($user['id'] ?? '');
                    $opponent = $isPlayer1 ? ($match['player2'] ?? []) : ($match['player1'] ?? []);
                    ?>
                    <div class="match-card">
                        <div class="match-header">
                            <div>
                                <span style="color: #a0a0a0;">Match ID:</span>
                                <?php echo htmlspecialchars(substr($match['id'] ?? '', 0, 8)); ?>...
                                <span class="mode-badge <?php echo ($match['mode'] ?? 'unranked'); ?>">
                                    <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                                </span>
                            </div>
                            <div style="color: #a0a0a0; font-size: 0.875rem;">
                                <?php echo date('d.m.Y H:i', strtotime($match['createdAt'] ?? 'now')); ?>
                            </div>
                        </div>

                        <div class="match-players">
                            <div class="match-player">
                                <div class="match-player-name">
                                    <?php echo htmlspecialchars($user['username'] ?? 'Du'); ?>
                                </div>
                                <div class="match-player-elo">
                                    <?php echo htmlspecialchars($user['eloRating'] ?? 1000); ?> ELO
                                </div>
                            </div>

                            <div class="match-vs">VS</div>

                            <div class="match-player">
                                <div class="match-player-name">
                                    <?php echo htmlspecialchars($opponent['username'] ?? 'Gegner'); ?>
                                </div>
                                <div class="match-player-elo">
                                    <?php echo htmlspecialchars($opponent['eloRating'] ?? 1000); ?> ELO
                                </div>
                            </div>
                        </div>

                        <div class="match-result <?php echo $isWinner ? 'win' : 'loss'; ?>">
                            <?php echo $isWinner ? '🏆 Gewonnen!' : '😢 Verloren'; ?>
                        </div>

                        <div class="match-actions">
                            <a href="poketmon-result.php?id=<?php echo htmlspecialchars($match['id'] ?? ''); ?>" class="btn btn-secondary" style="flex: 1;">
                                Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 2rem;">
                    <p style="color: #a0a0a0;">Keine abgeschlossenen Matches</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function showTab(tab) {
        // Hide all tabs
        document.getElementById('active-matches').style.display = 'none';
        document.getElementById('finished-matches').style.display = 'none';

        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Show selected tab
        if (tab === 'active') {
            document.getElementById('active-matches').style.display = 'block';
            event.target.classList.add('active');
        } else {
            document.getElementById('finished-matches').style.display = 'block';
            event.target.classList.add('active');
        }
    }
    </script>
</body>
</html>
