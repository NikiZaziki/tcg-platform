<?php
require_once 'config.php';

// Login erforderlich
$user = requireLogin();

// Daten laden
$inventory = apiCall('/inventory', 'GET', null, $_SESSION['token']);
$decks = apiCall('/decks', 'GET', null, $_SESSION['token']);
$matches = apiCall('/matches', 'GET', null, $_SESSION['token']);
$rewards = apiCall('/rewards/daily', 'GET', null, $_SESSION['token']);

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

// Calculate ranked stats
$rankedStats = [
    'wins' => 0,
    'losses' => 0,
    'total' => 0
];

foreach ($userMatches as $match) {
    if (($match['mode'] ?? '') === 'ranked' && ($match['status'] ?? '') === 'finished') {
        $rankedStats['total']++;
        if (($match['winnerId'] ?? '') === ($user['id'] ?? '')) {
            $rankedStats['wins']++;
        } else {
            $rankedStats['losses']++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .rank-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: bold;
        }

        .rank-badge.bronze {
            background: #cd7f32;
            color: #fff;
        }

        .rank-badge.silver {
            background: #c0c0c0;
            color: #000;
        }

        .rank-badge.gold {
            background: #ffd700;
            color: #000;
        }

        .rank-badge.platinum {
            background: #e5e4e2;
            color: #000;
        }

        .rank-badge.diamond {
            background: #b9f2ff;
            color: #000;
        }

        .rank-badge.master {
            background: #9b59b6;
            color: #fff;
        }

        .rank-badge.grandmaster {
            background: #e74c3c;
            color: #fff;
        }

        .rank-badge.challenger {
            background: linear-gradient(135deg, #e74c3c, #f39c12);
            color: #fff;
        }

        .quick-action {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-action:hover {
            transform: translateY(-5px);
            border-color: #e94560;
        }

        .quick-action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .quick-action-title {
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.25rem;
        }

        .quick-action-desc {
            color: #a0a0a0;
            font-size: 0.875rem;
        }

        .active-match {
            background: rgba(233, 69, 96, 0.1);
            border: 1px solid #e94560;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
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
                <a href="dashboard.php" class="active">Dashboard</a>
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
            <div>
                <h1>Willkommen zurück, <?php echo htmlspecialchars($user['username'] ?? 'User'); ?>!</h1>
                <p style="color: #a0a0a0;">
                    Rang: <span class="rank-badge <?php echo strtolower($user['rankTier'] ?? 'bronze'); ?>">
                        <?php echo htmlspecialchars($user['rankTier'] ?? 'Bronze'); ?>
                    </span>
                    | ELO: <?php echo htmlspecialchars($user['eloRating'] ?? 1000); ?>
                </p>
            </div>
            <a href="poketmon.php" class="btn btn-primary">🎮 Poketmon spielen</a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo is_array($inventory) ? count($inventory) : 0; ?></h3>
                <p>Karten in Collection</p>
            </div>
            <div class="stat-card">
                <h3><?php echo is_array($decks) ? count($decks) : 0; ?></h3>
                <p>Decks erstellt</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $rankedStats['wins']; ?></h3>
                <p>Ranked Siege</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $rankedStats['total']; ?></h3>
                <p>Gesamte Matches</p>
            </div>
        </div>

        <?php
        // Check for active matches
        $activeMatches = array_filter($userMatches, function($match) {
            return ($match['status'] ?? '') === 'active';
        });
        ?>

        <?php if (count($activeMatches) > 0): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem;">⚔️ Aktive Matches</h2>
                <?php foreach ($activeMatches as $match): ?>
                    <?php
                    $isPlayer1 = ($match['player1']['id'] ?? '') === ($user['id'] ?? '');
                    $opponent = $isPlayer1 ? ($match['player2'] ?? []) : ($match['player1'] ?? []);
                    ?>
                    <div class="active-match">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Gegner:</strong> <?php echo htmlspecialchars($opponent['username'] ?? 'Unknown'); ?>
                                <span class="mode-badge <?php echo ($match['mode'] ?? 'unranked'); ?>">
                                    <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                                </span>
                            </div>
                            <a href="poketmon-game.php?id=<?php echo htmlspecialchars($match['id'] ?? ''); ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Spielen
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Schnellaktionen</h2>
            <div class="grid">
                <a href="poketmon.php" class="quick-action">
                    <div class="quick-action-icon">🎮</div>
                    <div class="quick-action-title">Poketmon spielen</div>
                    <div class="quick-action-desc">Ranked & Unranked Matches</div>
                </a>

                <a href="decks.php" class="quick-action">
                    <div class="quick-action-icon">🃏</div>
                    <div class="quick-action-title">Deck erstellen</div>
                    <div class="quick-action-desc">Baue dein perfektes Deck</div>
                </a>

                <a href="shop.php" class="quick-action">
                    <div class="quick-action-icon">📦</div>
                    <div class="quick-action-title">Booster Packs</div>
                    <div class="quick-action-desc">Karten kaufen</div>
                </a>

                <a href="poketmon-ranks.php" class="quick-action">
                    <div class="quick-action-icon">🏆</div>
                    <div class="quick-action-title">Ränge & Statistiken</div>
                    <div class="quick-action-desc">Dein Fortschritt</div>
                </a>
            </div>
        </div>

        <div class="card">
            <h2>Letzte Aktivitäten</h2>
            <ul style="list-style: none; padding: 0;">
                <?php if (count($userMatches) > 0): ?>
                    <?php foreach (array_slice($userMatches, 0, 3) as $match): ?>
                        <?php
                        $isWinner = ($match['winnerId'] ?? '') === ($user['id'] ?? '');
                        $isPlayer1 = ($match['player1']['id'] ?? '') === ($user['id'] ?? '');
                        $opponent = $isPlayer1 ? ($match['player2']['username'] ?? 'Unknown') : ($match['player1']['username'] ?? 'Unknown');
                        ?>
                        <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: #a0a0a0;">
                                <?php echo date('d.m.Y H:i', strtotime($match['createdAt'] ?? 'now')); ?>:
                            </span>
                            <?php echo $isWinner ? 'Gewonnen' : 'Verloren'; ?> gegen
                            <?php echo htmlspecialchars($opponent); ?>
                            <span class="mode-badge <?php echo ($match['mode'] ?? 'unranked'); ?>">
                                <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <span style="color: #a0a0a0;">Noch keine Matches gespielt</span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
