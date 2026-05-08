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

// Get shop data
$packs = apiCall('/shop/packs', 'GET', null, $_SESSION['token']);
$orders = apiCall('/shop/orders', 'GET', null, $_SESSION['token']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'buy':
                $result = apiCall('/shop/orders', 'POST', [
                    'items' => json_decode($_POST['items'] ?? '[]', true)
                ], $_SESSION['token']);
                break;
        }
        header('Location: shop.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - TCG Platform</title>
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
                <a href="trading.php">Trading</a>
                <a href="shop.php" class="active">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Shop</h1>
            <div style="color: #e94560; font-size: 1.25rem;">💰 1500 Coins</div>
        </div>

        <div class="card">
            <h2>Booster Packs</h2>
            <?php if ($packs && !isset($packs['error'])): ?>
                <div class="grid">
                    <?php foreach ($packs as $pack): ?>
                    <div class="card" style="text-align: center;">
                        <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                            📦
                        </div>
                        <h3><?php echo htmlspecialchars($pack['name'] ?? 'Unknown'); ?></h3>
                        <p style="color: #a0a0a0; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($pack['description'] ?? 'Unknown'); ?></p>
                        <p style="color: #e94560; font-weight: bold; margin-bottom: 1rem;"><?php echo $pack['price'] ?? '0'; ?> Coins</p>
                        <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;" onclick="buyPack('<?php echo htmlspecialchars($pack['id'] ?? ''); ?>')">Kaufen</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Keine Booster Packs verfügbar.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Einzelne Karten</h2>
            <div class="grid">
                <div class="card" style="text-align: center;">
                    <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                        🃏
                    </div>
                    <h3>Dragon Lord</h3>
                    <p style="color: #a0a0a0; margin-bottom: 0.5rem;">Legendary</p>
                    <p style="color: #e94560; font-weight: bold; margin-bottom: 1rem;">500 Coins</p>
                    <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Kaufen</button>
                </div>

                <div class="card" style="text-align: center;">
                    <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                        🃏
                    </div>
                    <h3>Phoenix</h3>
                    <p style="color: #a0a0a0; margin-bottom: 0.5rem;">Epic</p>
                    <p style="color: #e94560; font-weight: bold; margin-bottom: 1rem;">350 Coins</p>
                    <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Kaufen</button>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Bestellungen</h2>
            <table>
                <thead>
                    <tr>
                        <th>Bestellung</th>
                        <th>Datum</th>
                        <th>Status</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders && !isset($orders['error'])): ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($order['createdAt'] ?? 'Unknown'); ?></td>
                            <td><span style="color: <?php echo $order['status'] === 'completed' ? '#4ade80' : '#fbbf24'; ?>;"><?php echo htmlspecialchars($order['status'] ?? 'Unknown'); ?></span></td>
                            <td><a href="#" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Details</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Keine Bestellungen gefunden.</p>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function buyPack(packId) {
        const items = [{packId: packId, quantity: 1}];
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="buy"><input type="hidden" name="items" value="' + JSON.stringify(items) + '">';
        document.body.appendChild(form);
        form.submit();
    }
    </script>
</body>
</html>
