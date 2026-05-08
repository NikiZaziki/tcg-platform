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

// Get deck ID from URL
$deckId = $_GET['id'] ?? null;

// Get deck data
$deck = null;
if ($deckId) {
    $deck = apiCall('/decks/' . $deckId, 'GET', null, $_SESSION['token']);
}

// Get user inventory for adding cards
$inventory = apiCall('/inventory', 'GET', null, $_SESSION['token']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $result = apiCall('/decks/' . $deckId, 'PUT', [
                    'name' => $_POST['name'] ?? ''
                ], $_SESSION['token']);
                break;
            case 'addCard':
                $result = apiCall('/decks/' . $deckId . '/cards', 'POST', [
                    'cardId' => $_POST['cardId'] ?? '',
                    'quantity' => $_POST['quantity'] ?? 1
                ], $_SESSION['token']);
                break;
            case 'removeCard':
                $result = apiCall('/decks/' . $deckId . '/cards/' . ($_POST['cardId'] ?? ''), 'DELETE', [
                    'quantity' => $_POST['quantity'] ?? 1
                ], $_SESSION['token']);
                break;
        }
        header('Location: deck-edit.php?id=' . $deckId);
        exit;
    }
}

// Get deck validation
$validation = null;
if ($deckId) {
    $validation = apiCall('/decks/' . $deckId . '/validate', 'GET', null, $_SESSION['token']);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deck bearbeiten - TCG Platform</title>
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
                <a href="decks.php" class="active">Decks</a>
                <a href="matches.php">Matches</a>
                <a href="trading.php">Trading</a>
                <a href="shop.php">Shop</a>
                <a href="rewards.php">Rewards</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($deck && !isset($deck['error'])): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Deck bearbeiten: <?php echo htmlspecialchars($deck['name'] ?? 'Unknown'); ?></h1>
                <a href="decks.php" class="btn btn-secondary">Zurück</a>
            </div>

            <div class="card">
                <h2>Deck Informationen</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Deck Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($deck['name'] ?? ''); ?>" required>
                    </div>
                    <button type="submit" name="action" value="update" class="btn btn-primary">Speichern</button>
                </form>
            </div>

            <div class="card">
                <h2>Karten im Deck</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Karte</th>
                            <th>Anzahl</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($deck['cards'] && !isset($deck['cards']['error'])): ?>
                            <?php foreach ($deck['cards'] as $card): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($card['name'] ?? 'Unknown'); ?></td>
                                <td><?php echo $card['quantity'] ?? 1; ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="removeCard">
                                        <input type="hidden" name="cardId" value="<?php echo htmlspecialchars($card['id'] ?? ''); ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #f87171 !important;">Entfernen</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Keine Karten im Deck.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Karte hinzufügen</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Karte auswählen</label>
                        <select name="cardId" required>
                            <option value="">-- Karte auswählen --</option>
                            <?php if ($inventory && !isset($inventory['error'])): ?>
                                <?php foreach ($inventory as $item): ?>
                                    <option value="<?php echo htmlspecialchars($item['id'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($item['name'] ?? 'Unknown'); ?> (x<?php echo $item['quantity'] ?? 1; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Anzahl</label>
                        <input type="number" name="quantity" value="1" min="1" required>
                    </div>
                    <button type="submit" name="action" value="addCard" class="btn btn-primary">Hinzufügen</button>
                </form>
            </div>

            <div class="card">
                <h2>Deck Validierung</h2>
                <?php if ($validation && !isset($validation['error'])): ?>
                    <p><strong>Status:</strong> <?php echo $validation['valid'] ? 'Valid' : 'Invalid'; ?></p>
                    <p><strong>Karten:</strong> <?php echo $validation['cardCount'] ?? 0; ?>/60</p>
                    <p><strong>Regeln:</strong> <?php echo $validation['rules'] ?? 'Keine'; ?></p>
                <?php else: ?>
                    <p>Deck Validierung fehlgeschlagen.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Deck nicht gefunden.</p>
        <?php endif; ?>
    </div>
</body>
</html>
