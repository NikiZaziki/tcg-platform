<?php
/**
 * Rewards Page
 * Daily Rewards und Achievements
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/RewardService.php';
require_once __DIR__ . '/../services/MatchService.php';

$auth = new AuthService();
$rewardService = new RewardService();
$matchService = new MatchService();

// Login erforderlich
$user = $auth->requireLogin();
$token = $auth->getToken();

// Daily Reward Status
$dailyReward = $rewardService->getDailyReward($token);

// Matches für Achievements laden
$matches = $matchService->getUserMatches($user['id'], $token);

// Achievements definieren
$achievements = [
    'fighter' => [
        'name' => 'Kämpfer',
        'icon' => '⚔️',
        'levels' => [
            1 => ['description' => 'Gewinne 5 Matches', 'reward' => 10, 'required' => 5],
            2 => ['description' => 'Gewinne 10 Matches', 'reward' => 15, 'required' => 10],
            3 => ['description' => 'Gewinne 20 Matches', 'reward' => 20, 'required' => 20],
            4 => ['description' => 'Gewinne 25 Matches', 'reward' => 20, 'required' => 25],
            5 => ['description' => 'Gewinne 50 Matches', 'reward' => 35, 'required' => 50],
            6 => ['description' => 'Gewinne 75 Matches', 'reward' => 40, 'required' => 75],
        ]
    ],
    'rank_hunter' => [
        'name' => 'Rangjäger',
        'icon' => '🏆',
        'levels' => [
            1 => ['description' => 'Hole dir ein Rank Up', 'reward' => 10, 'required' => 1],
            2 => ['description' => 'Erreiche Silber Rang', 'reward' => 25, 'required' => 2],
            3 => ['description' => 'Erreiche Gold Rang', 'reward' => 50, 'required' => 3],
            4 => ['description' => 'Erreiche Platin Rang', 'reward' => 75, 'required' => 4],
            5 => ['description' => 'Erreiche Diamant Rang', 'reward' => 100, 'required' => 5],
        ]
    ],
    'collector' => [
        'name' => 'Sammler',
        'icon' => '📚',
        'levels' => [
            1 => ['description' => 'Sammle 10 Karten', 'reward' => 10, 'required' => 10],
            2 => ['description' => 'Sammle 25 Karten', 'reward' => 20, 'required' => 25],
            3 => ['description' => 'Sammle 50 Karten', 'reward' => 35, 'required' => 50],
            4 => ['description' => 'Sammle 100 Karten', 'reward' => 50, 'required' => 100],
        ]
    ],
    'deck_builder' => [
        'name' => 'Deck-Bauer',
        'icon' => '🃏',
        'levels' => [
            1 => ['description' => 'Erstelle dein erstes Deck', 'reward' => 15, 'required' => 1],
            2 => ['description' => 'Erstelle 3 Decks', 'reward' => 25, 'required' => 3],
            3 => ['description' => 'Erstelle 5 Decks', 'reward' => 40, 'required' => 5],
        ]
    ],
    'veteran' => [
        'name' => 'Veteran',
        'icon' => '🎖️',
        'levels' => [
            1 => ['description' => 'Spiele 10 Matches', 'reward' => 10, 'required' => 10],
            2 => ['description' => 'Spiele 50 Matches', 'reward' => 30, 'required' => 50],
            3 => ['description' => 'Spiele 100 Matches', 'reward' => 50, 'required' => 100],
        ]
    ],
];

// Statistiken berechnen
$stats = [
    'totalWins' => 0,
    'totalMatches' => 0,
    'rankUps' => 0,
];

foreach ($matches as $match) {
    if (($match['status'] ?? '') === 'finished') {
        $stats['totalMatches']++;
        if (($match['winnerId'] ?? '') === $user['id']) {
            $stats['totalWins']++;
        }
    }
}

// Rank basierend auf Rang-Tier berechnen
$ranks = ['bronze' => 1, 'silver' => 2, 'gold' => 3, 'platinum' => 4, 'diamond' => 5];
$currentRank = $ranks[strtolower($user['rankTier'] ?? 'bronze')] ?? 1;
$stats['rankUps'] = $currentRank - 1;

// Kartenanzahl (aus Inventory)
require_once __DIR__ . '/../services/InventoryService.php';
$inventoryService = new InventoryService();
$inventory = $inventoryService->getInventory($token);
$stats['cardCount'] = is_array($inventory) ? count($inventory) : 0;

// Deck-Anzahl
require_once __DIR__ . '/../services/DeckService.php';
$deckService = new DeckService();
$decks = $deckService->getAllDecks($token);
$stats['deckCount'] = is_array($decks) ? count($decks) : 0;

// Achievement-Status berechnen
$achievementProgress = [];
foreach ($achievements as $key => $achievement) {
    $achievementProgress[$key] = [];
    foreach ($achievement['levels'] as $level => $data) {
        $currentValue = 0;
        switch ($key) {
            case 'fighter':
                $currentValue = $stats['totalWins'];
                break;
            case 'rank_hunter':
                $currentValue = $stats['rankUps'];
                break;
            case 'collector':
                $currentValue = $stats['cardCount'];
                break;
            case 'deck_builder':
                $currentValue = $stats['deckCount'];
                break;
            case 'veteran':
                $currentValue = $stats['totalMatches'];
                break;
        }
        $achievementProgress[$key][$level] = [
            'current' => $currentValue,
            'required' => $data['required'],
            'completed' => $currentValue >= $data['required'],
            'progress' => min(100, round(($currentValue / $data['required']) * 100))
        ];
    }
}

// Daily Reward abholen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_daily'])) {
    $result = $rewardService->claimDailyReward($token);
    if ($result['success']) {
        header('Location: /pages/rewards.php?claimed=true');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rewards - TCG Platform</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .daily-reward-card {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 2px solid #e94560;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .daily-reward-card.claimed {
            border-color: #27ae60;
            background: linear-gradient(135deg, #1a2e1a, #162e16);
        }

        .daily-reward-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .daily-reward-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .daily-reward-desc {
            color: #a0a0a0;
            margin-bottom: 1.5rem;
        }

        .daily-reward-timer {
            color: #e94560;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .achievement-section {
            margin-bottom: 2rem;
        }

        .achievement-category {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .achievement-category-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .achievement-category-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }

        .achievement-category-name {
            font-size: 1.25rem;
            font-weight: bold;
            color: #fff;
        }

        .achievement-level {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .achievement-level.completed {
            background: rgba(39, 174, 96, 0.1);
            border-color: #27ae60;
        }

        .achievement-level-info {
            flex: 1;
        }

        .achievement-level-title {
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.25rem;
        }

        .achievement-level-desc {
            color: #a0a0a0;
            font-size: 0.875rem;
        }

        .achievement-level-reward {
            color: #f39c12;
            font-weight: bold;
            font-size: 0.875rem;
        }

        .achievement-level-status {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .progress-bar {
            width: 150px;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #e94560, #f39c12);
            transition: width 0.3s ease;
        }

        .progress-fill.completed {
            background: #27ae60;
        }

        .achievement-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #27ae60;
            color: #fff;
            font-weight: bold;
        }

        .achievement-badge.pending {
            background: rgba(255, 255, 255, 0.1);
            color: #a0a0a0;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }

        .stat-item-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #e94560;
        }

        .stat-item-label {
            color: #a0a0a0;
            font-size: 0.875rem;
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
                <a href="/pages/poketmon/index.php">Poketmon</a>
                <a href="/pages/trading.php">Trading</a>
                <a href="/pages/shop.php">Shop</a>
                <a href="/pages/rewards.php" class="active">Rewards</a>
                <a href="/pages/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 style="margin-bottom: 2rem;">🎁 Rewards & Achievements</h1>

        <?php if (isset($_GET['claimed'])): ?>
            <div style="background: rgba(39, 174, 96, 0.2); border: 1px solid #27ae60; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; color: #27ae60;">
                ✅ Daily Reward erfolgreich abgeholt!
            </div>
        <?php endif; ?>

        <!-- Daily Reward -->
        <div class="daily-reward-card <?php echo ($dailyReward['claimed'] ?? false) ? 'claimed' : ''; ?>">
            <div class="daily-reward-icon">🎁</div>
            <div class="daily-reward-title">Daily Reward</div>
            <?php if ($dailyReward['claimed'] ?? false): ?>
                <div class="daily-reward-timer">
                    Nächste Reward in: <?php echo htmlspecialchars($dailyReward['nextClaim'] ?? '24 Stunden'); ?>
                </div>
                <div class="daily-reward-desc">Du hast deine tägliche Belohnung bereits abgeholt!</div>
                <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed;">Bereits abgeholt</button>
            <?php else: ?>
                <div class="daily-reward-desc">Hole dir deine tägliche Belohnung von 50 Coins!</div>
                <form method="POST">
                    <button type="submit" name="claim_daily" class="btn btn-primary">🎁 Jetzt abholen</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Statistiken Übersicht -->
        <div class="stats-overview">
            <div class="stat-item">
                <div class="stat-item-value"><?php echo $stats['totalWins']; ?></div>
                <div class="stat-item-label">Gewonnene Matches</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-value"><?php echo $stats['totalMatches']; ?></div>
                <div class="stat-item-label">Gesamte Matches</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-value"><?php echo $stats['cardCount']; ?></div>
                <div class="stat-item-label">Karten gesammelt</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-value"><?php echo $stats['deckCount']; ?></div>
                <div class="stat-item-label">Decks erstellt</div>
            </div>
        </div>

        <!-- Achievements -->
        <h2 style="margin-bottom: 1.5rem;">🏆 Achievements</h2>

        <?php foreach ($achievements as $key => $achievement): ?>
            <div class="achievement-category">
                <div class="achievement-category-header">
                    <div class="achievement-category-icon"><?php echo $achievement['icon']; ?></div>
                    <div class="achievement-category-name"><?php echo $achievement['name']; ?></div>
                </div>

                <?php foreach ($achievement['levels'] as $level => $data): ?>
                    <?php $progress = $achievementProgress[$key][$level]; ?>
                    <div class="achievement-level <?php echo $progress['completed'] ? 'completed' : ''; ?>">
                        <div class="achievement-level-info">
                            <div class="achievement-level-title">Stufe <?php echo $level; ?></div>
                            <div class="achievement-level-desc"><?php echo $data['description']; ?></div>
                            <div class="achievement-level-reward">🪙 <?php echo $data['reward']; ?> Coins</div>
                        </div>
                        <div class="achievement-level-status">
                            <div class="progress-bar">
                                <div class="progress-fill <?php echo $progress['completed'] ? 'completed' : ''; ?>" style="width: <?php echo $progress['progress']; ?>%;"></div>
                            </div>
                            <div class="achievement-badge <?php echo $progress['completed'] ? '' : 'pending'; ?>">
                                <?php echo $progress['completed'] ? '✓' : $level; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
