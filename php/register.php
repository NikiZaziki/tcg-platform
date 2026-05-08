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

// Redirect if already logged in
if ($user) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $result = apiCall('/auth/register', 'POST', [
                    'username' => $_POST['username'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? ''
                ]);

                if (isset($result['access_token'])) {
                    $_SESSION['token'] = $result['access_token'];
                    $_SESSION['user'] = $result['user'];
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = $result['error'] ?? 'Registrierung fehlgeschlagen';
                }
                break;
        }
    }
}

$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren - TCG Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">TCG Platform</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="card" style="max-width: 400px; width: 100%;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Registrieren</h2>
            <?php if($error): ?>
                <p style="color: #f87171; text-align: center; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Benutzername</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Passwort</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Passwort bestätigen</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Registrieren</button>
            </form>
            <p style="text-align: center; margin-top: 1rem; color: #a0a0a0;">
                Bereits registriert? <a href="login.php" style="color: #e94560;">Einloggen</a>
            </p>
        </div>
    </div>
</body>
</html>
