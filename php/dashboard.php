<?php
session_start();

// API Base URL
$apiUrl = 'http://45.131.111.6:3000';

// Helper function für API Calls
function apiCall($endpoint, $method = 'GET', $data = null, $token = null) {
    global $apiUrl;

    $ch = curl_init();
    $url = $apiUrl . $endpoint;

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        return ['error' => 'API Error: ' . $httpCode, 'response' => $response];
    }
}

// Check if user is logged in
$user = null;
if (isset($_SESSION['token'])) {
    $user = apiCall('/auth/me', 'GET', null, $_SESSION['token']);
    if (isset($user['error'])) {
        unset($_SESSION['token']);
        unset($_SESSION['user']);
    }
}

// Redirect if not logged in
if (!$user) {
    header('Location: login.php');
    exit;
}

// Get user data
$inventory = apiCall('/inventory', 'GET', null, $_SESSION['token']);
$decks = apiCall('/decks', 'GET', null, $_SESSION['token']);
$matches = apiCall('/matches', 'GET', null, $_SESSION['token']);
$rewards = apiCall('/rewards/daily', 'GET', null, $_SESSION['token']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
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
                <a href="matches.php">Matches</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Willkommen zurück, <?php echo htmlspecialchars($user['username'] ?? 'User'); ?>!</h1>

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo count($inventory ?? 0); ?></h3>
                <p>Karten in Collection</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($decks ?? 0); ?></h3>
                <p>Decks erstellt</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($matches ?? 0); ?></h3>
                <p>Gewonnene Matches</p>
            </div>
            <div class="stat-card">
                <h3>1500</h3>
                <p>Punkte</p>
            </div>
        </div>

        <div class="card">
            <h2>Aktuelle Matches</h2>
            <?php if ($matches && !isset($matches['error'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Gegner</th>
                            <th>Deck</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $match): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($match['opponent'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($match['deck'] ?? 'Unknown'); ?></td>
                            <td><span style="color: <?php echo $match['status'] === 'active' ? '#4ade80' : '#fbbf24'; ?>;"><?php echo htmlspecialchars($match['status'] ?? 'Unknown'); ?></span></td>
                            <td><a href="match.php?id=<?php echo htmlspecialchars($match['id'] ?? ''); ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Beitreten</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Keine aktiven Matches gefunden.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Letzte Aktivitäten</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <span style="color: #a0a0a0;">Vor 2 Stunden:</span> Match gegen Player123 gewonnen
                </li>
                <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <span style="color: #a0a0a0;">Vor 5 Stunden:</span> Neue Karte "Dragon Lord" erhalten
                </li>
                <li style="padding: 0.75rem 0;">
                    <span style="color: #a0a0a0;">Vor 1 Tag:</span> Deck "Fire Deck" erstellt
                </li>
            </ul>
        </div>
    </div>
</body>
</html>
