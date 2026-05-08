<?php
/**
 * Poketmon Ranks Page
 * Ränge und Statistiken
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../services/MatchService.php';

$auth = new AuthService();
$matchService = new MatchService();

// Login erforderlich
$user = $auth->requireLogin();
$token = $auth->getToken();

// Get all matches
$allMatches = $matchService->getAllMatches($token);

// Filter matches by user
$userMatches = [];
$rankedStats = [
    'wins' => 0,
    'losses' => 0,
    'winRate' => 0,
    'total' => 0
];

if ($allMatches && is_array($allMatches)) {
    foreach ($allMatches as $match) {
        if (($match['player1']['id'] ?? '') === ($user['id'] ?? '') ||
            ($match['player2']['id'] ?? '') === ($user['id'] ?? '')) {
            $userMatches[] = $match;

            if (($match['mode'] ?? '') === 'ranked' && ($match['status'] ?? '') === 'finished') {
                $rankedStats['total']++;
                if (($match['winnerId'] ?? '') === ($user['id'] ?? '')) {
                    $rankedStats['wins']++;
                } else {
                    $rankedStats['losses']++;
                }
            }
        }
    }
}

// Calculate win rate
if ($rankedStats['total'] > 0) {
    $rankedStats['winRate'] = round(($rankedStats['wins'] / $rankedStats['total']) * 100, 1);
}

// Rank thresholds
$rankThresholds = [
    'Bronze' => 0,
    'Silver' => 1200,
    'Gold' => 1400,
    'Platinum' => 1600,
    'Diamond' => 1800,
    'Master' => 2000,
    'Grandmaster' => 2200,
    'Challenger' => 2400
];

// Calculate progress to next rank
$currentElo = $user['eloRating'] ?? 1000;
$currentRank = $user['rankTier'] ?? 'Bronze';
$nextRank = null;
$progress = 0;
$required = 0;

foreach ($rankThresholds as $rank => $threshold) {
    if ($threshold > $currentElo && $nextRank === null) {
        $nextRank = $rank;
        break;
    }
}

if ($nextRank) {
    $currentThreshold = $rankThresholds[$currentRank] ?? 0;
    $nextThreshold = $rankThresholds[$nextRank];
    $required = $nextThreshold - $currentThreshold;
    $progress = $currentElo - $currentThreshold;
    if ($required > 0) {
        $progress = round(($progress / $required) * 100);
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poketmon Ränge - TCG Platform</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .rank-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .rank-card {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
        }

        .rank-card.current {
            border-color: #e94560;
            box-shadow: 0 0 30px rgba(233, 69, 96, 0.3);
        }

        .rank-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .rank-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .rank-elo {
            font-size: 2rem;
            font-weight: bold;
            color: #e94560;
            margin-bottom: 0.5rem;
        }

        .rank-progress {
            width: 100%;
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .rank-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e94560, #f39c12);
            transition: width 0.5s ease;
        }

        .rank-next {
            color: #a0a0a0;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .stats-grid {
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
            text-align: center;
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

        .stat-value.wins {
            color: #4ade80;
        }

        .stat-value.losses {
            color: #f87171;
        }

        .rank-ladder {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
        }

        .rank-tier {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .rank-tier:last-child {
            border-bottom: none;
        }

        .rank-tier-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }

        .rank-tier-info {
            flex: 1;
        }

        .rank-tier-name {
            font-weight: bold;
            color: #fff;
        }

        .rank-tier-range {
            color: #a0a0a0;
            font-size: 0.875rem;
        }

        .rank-tier.current {
            background: rgba(233, 69, 96, 0.1);
            border-left: 3px solid #e94560;
        }

        .match-history {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
        }

        .match-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .match-item:last-child {
            border-bottom: none;
        }

        .match-result {
            font-weight: bold;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
        }

        .match-result.win {
            background: rgba(74, 222, 128, 0.2);
            color: #4ade80;
        }

        .match-result.loss {
            background: rgba(248, 113, 113, 0.2);
            color: #f87171;
        }

        .match-mode {
            font-size: 0.75rem;
            color: #a0a0a0;
            margin-left: 0.5rem;
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
        <h1 style="margin-bottom: 2rem;">Poketmon Ränge & Statistiken</h1>

        <div class="rank-overview">
            <div class="rank-card current">
                <div class="rank-icon">🏆</div>
                <div class="rank-name">
                    <?php echo htmlspecialchars($currentRank); ?>
                </div>
                <div class="rank-elo">
                    <?php echo $currentElo; ?> ELO
                </div>
                <?php if ($nextRank): ?>
                    <div class="rank-progress">
                        <div class="rank-progress-bar" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                    <div class="rank-next">
                        <?php echo $progress; ?>% bis <?php echo $nextRank; ?>
                        (<?php echo ($rankThresholds[$nextRank] - $currentElo); ?> ELO benötigt)
                    </div>
                <?php else: ?>
                    <div class="rank-next">
                        Höchster Rang erreicht!
                    </div>
                <?php endif; ?>
            </div>

            <div class="rank-overview">
                <div class="rank-card">
                    <div class="rank-icon">📊</div>
                    <div class="rank-name">Ranked Statistiken</div>
                    <div class="rank-elo" style="color: #4ade80;">
                        <?php echo $rankedStats['wins']; ?>
                    </div>
                    <div class="stat-label">Siege</div>
                    <div class="rank-elo" style="color: #f87171; margin-top: 1rem;">
                        <?php echo $rankedStats['losses']; ?>
                    </div>
                    <div class="stat-label">Niederlagen</div>
                    <div class="rank-elo" style="color: #fbbf24; margin-top: 1rem;">
                        <?php echo $rankedStats['winRate']; ?>%
                    </div>
                    <div class="stat-label">Win Rate</div>
                </div>

                <div class="rank-card">
                    <div class="rank-icon">🎮</div>
                    <div class="rank-name">Gesamt Matches</div>
                    <div class="rank-elo">
                        <?php echo count($userMatches); ?>
                    </div>
                    <div class="stat-label">Alle Matches</div>
                    <div class="rank-elo" style="color: #e94560; margin-top: 1rem;">
                        <?php echo $rankedStats['total']; ?>
                    </div>
                    <div class="stat-label">Ranked Matches</div>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value wins">
                    +<?php echo $rankedStats['wins'] * 32; ?>
                </div>
                <div class="stat-label">ELO gewonnen</div>
            </div>

            <div class="stat-box">
                <div class="stat-value losses">
                    -<?php echo $rankedStats['losses'] * 32; ?>
                </div>
                <div class="stat-label">ELO verloren</div>
            </div>

            <div class="stat-box">
                <div class="stat-value">
                    <?php echo $rankedStats['wins'] - $rankedStats['losses']; ?>
                </div>
                <div class="stat-label">Netto ELO</div>
            </div>

            <div class="stat-box">
                <div class="stat-value">
                    <?php echo $rankedStats['total'] > 0 ? round($rankedStats['total'] / 10) : 0; ?>
                </div>
                <div class="stat-label">Karten gewonnen/verloren</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div class="rank-ladder">
                <h2 style="margin-bottom: 1.5rem;">Rangleiter</h2>
                <?php foreach (array_reverse($rankThresholds) as $rank => $threshold): ?>
                    <div class="rank-tier <?php echo $rank === $currentRank ? 'current' : ''; ?>">
                        <div class="rank-tier-icon">
                            <?php
                            $icons = [
                                'Challenger' => '👑',
                                'Grandmaster' => '💎',
                                'Master' => '🎖️',
                                'Diamond' => '💠',
                                'Platinum' => '⚪',
                                'Gold' => '🥇',
                                'Silver' => '🥈',
                                'Bronze' => '🥉'
                            ];
                            echo $icons[$rank] ?? '🏅';
                            ?>
                        </div>
                        <div class="rank-tier-info">
                            <div class="rank-tier-name"><?php echo $rank; ?></div>
                            <div class="rank-tier-range"><?php echo $threshold; ?>+ ELO</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="match-history">
                <h2 style="margin-bottom: 1.5rem;">Letzte Matches</h2>
                <?php if (count($userMatches) > 0): ?>
                    <?php foreach (array_slice($userMatches, 0, 10) as $match): ?>
                        <div class="match-item">
                            <div>
                                <div style="font-weight: bold; color: #fff;">
                                    <?php
                                    $opponent = ($match['player1']['id'] ?? '') === ($user['id'] ?? '')
                                        ? ($match['player2']['username'] ?? 'Unknown')
                                        : ($match['player1']['username'] ?? 'Unknown');
                                    echo htmlspecialchars($opponent);
                                    ?>
                                </div>
                                <div style="color: #a0a0a0; font-size: 0.875rem;">
                                    <?php echo date('d.m.Y H:i', strtotime($match['createdAt'] ?? 'now')); ?>
                                </div>
                            </div>
                            <div>
                                <span class="match-result <?php echo ($match['winnerId'] ?? '') === ($user['id'] ?? '') ? 'win' : 'loss'; ?>">
                                    <?php echo ($match['winnerId'] ?? '') === ($user['id'] ?? '') ? 'Sieg' : 'Niederlage'; ?>
                                </span>
                                <span class="match-mode">
                                    <?php echo ucfirst($match['mode'] ?? 'Unranked'); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #a0a0a0;">Keine Matches gespielt</p>
                <?php endif; ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="/pages/poketmon/index.php" class="btn btn-primary">Neues Match starten</a>
            <a href="/pages/dashboard.php" class="btn btn-secondary" style="margin-left: 1rem;">Zum Dashboard</a>
        </div>
    </div>
</body>
</html>
