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

// Get match ID from URL
$matchId = $_GET['id'] ?? null;

// Get match data if ID is provided
$match = null;
if ($matchId) {
    $match = apiCall('/matches/' . $matchId, 'GET', null, $_SESSION['token']);
}

// Get all matches
$matches = apiCall('/matches', 'GET', null, $_SESSION['token']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - TCG Platform</title>
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
                <a href="decks.php">Decks</a>
                <a href="matches.php" class="active">Matches</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($matchId && $match && !isset($match['error'])): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Match: <?php echo htmlspecialchars($match['id'] ?? 'Unknown'); ?></h1>
                <a href="matches.php" class="btn btn-secondary">Zurück</a>
            </div>

            <div class="card">
                <h2>Match Details</h2>
                <p><strong>Gegner:</strong> <?php echo htmlspecialchars($match['opponent'] ?? 'Unknown'); ?></p>
                <p><strong>Deck:</strong> <?php echo htmlspecialchars($match['deck'] ?? 'Unknown'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($match['status'] ?? 'Unknown'); ?></p>
            </div>

            <div class="card">
                <h2>Match History</h2>
                <?php
                $history = apiCall('/matches/' . $matchId . '/history', 'GET', null, $_SESSION['token']);
                if ($history && !isset($history['error'])):
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Runde</th>
                            <th>Aktion</th>
                            <th>Zeit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $move): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($move['round'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($move['action'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($move['timestamp'] ?? 'Unknown'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Noch keine Spielzüge in diesem Match.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Matches</h1>
                <button class="btn btn-primary">+ Neues Match</button>
            </div>

            <div class="card">
                <h2>Aktive</h2>
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
                <h2>Verlauf</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Gegner</th>
                            <th>Deck</th>
                            <th>Ergebnis</th>
                            <th>Datum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CardMaster</td>
                            <td>Water Deck</td>
                            <td><span style="color: #4ade80;">Gewonnen</span></td>
                            <td>Heute, 14:30</td>
                        </tr>
                        <tr>
                            <td>DragonSlayer</td>
                            <td>Electric Deck</td>
                            <td><span style="color: #f87171;">Verloren</span></td>
                            <td>Gestern, 18:45</td>
                        </tr>
                        <tr>
                            <td>MagicUser</td>
                            <td>Fire Deck</td>
                            <td><span style="color: #4ade80;">Gewonnen</span></td>
                            <td>Gestern, 15:20</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
