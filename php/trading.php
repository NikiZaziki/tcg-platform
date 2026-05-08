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

// Get trades data
$trades = apiCall('/trades', 'GET', null, $_SESSION['token']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = apiCall('/trades', 'POST', [
                    'receiverId' => $_POST['receiverId'] ?? '',
                    'senderCards' => json_decode($_POST['senderCards'] ?? '[]', true),
                    'receiverCards' => json_decode($_POST['receiverCards'] ?? '[]', true)
                ], $_SESSION['token']);
                break;
            case 'accept':
                $result = apiCall('/trades/' . ($_POST['tradeId'] ?? '') . '/accept', 'PUT', null, $_SESSION['token']);
                break;
            case 'reject':
                $result = apiCall('/trades/' . ($_POST['tradeId'] ?? '') . '/reject', 'PUT', null, $_SESSION['token']);
                break;
            case 'cancel':
                $result = apiCall('/trades/' . ($_POST['tradeId'] ?? ''), 'DELETE', null, $_SESSION['token']);
                break;
        }
        header('Location: trading.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading - TCG Platform</title>
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
                <a href="matches.php">Matches</a>
                <a href="trading.php" class="active">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Trading</h1>
            <button class="btn btn-primary" onclick="document.getElementById('createTradeModal').style.display = 'flex'">+ Neues Trade</button>
        </div>

        <div class="card">
            <h2>Aktive</h2>
            <?php if ($trades && !isset($trades['error'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Händler</th>
                            <th>Angebot</th>
                            <th>Gesucht</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trades as $trade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trade['sender']['username'] ?? 'Unknown'); ?></td>
                            <td>
                                <?php foreach ($trade['senderCards'] ?? [] as $card): ?>
                                    <?php echo htmlspecialchars($card['name'] ?? 'Unknown'); ?> x<?php echo $card['quantity'] ?? 1; ?><br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($trade['receiverCards'] ?? [] as $card): ?>
                                    <?php echo htmlspecialchars($card['name'] ?? 'Unknown'); ?> x<?php echo $card['quantity'] ?? 1; ?><br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php if ($trade['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="accept">
                                        <input type="hidden" name="tradeId" value="<?php echo htmlspecialchars($trade['id'] ?? ''); ?>">
                                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Annehmen</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="tradeId" value="<?php echo htmlspecialchars($trade['id'] ?? ''); ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Ablehnen</button>
                                    </form>
                                <?php elseif ($trade['status'] === 'accepted'): ?>
                                    <span style="color: #4ade80;">Angenommen</span>
                                <?php elseif ($trade['status'] === 'rejected'): ?>
                                    <span style="color: #f87171;">Abgelehnt</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Keine aktiven Trades gefunden.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Marktplatz</h2>
            <table>
                <thead>
                    <tr>
                        <th>Karte</th>
                        <th>Preis</th>
                        <th>Verkäufer</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Dragon Lord</td>
                        <td>500 Coins</td>
                        <td>CardMaster</td>
                        <td><button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Kaufen</button></td>
                    </tr>
                    <tr>
                        <td>Phoenix</td>
                        <td>350 Coins</td>
                        <td>DragonSlayer</td>
                        <td><button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Kaufen</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Trade Modal -->
    <div id="createTradeModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
        <div class="card" style="max-width:500px; width: 100%;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Neues Trade erstellen</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Empfänger (User ID)</label>
                    <input type="text" name="receiverId" required>
                </div>
                <div class="form-group">
                    <label>Angebot (JSON Format: [{"cardId": "1", "quantity": 1}])</label>
                    <textarea name="senderCards" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Gesucht (JSON Format: [{"cardId": "2", "quantity": 1}])</label>
                    <textarea name="receiverCards" rows="3" required></textarea>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Erstellen</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createTradeModal').style.display = 'none'" style="flex: 1;">Abbrechen</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
