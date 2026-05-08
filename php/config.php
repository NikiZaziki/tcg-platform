<?php
// Konfiguration
define('API_URL', 'http://45.131.111.6:3000');
define('DEBUG', true);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? 1 : 0);

// Session starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logging Funktion
function logError($message) {
    if (DEBUG) {
        error_log('[TCG-DEBUG] ' . $message);
    }
}

// API Call Funktion
function apiCall($endpoint, $method = 'GET', $data = null, $token = null) {
    $url = API_URL . $endpoint;

    logError("API Call: $method $url");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    logError("API Response: HTTP $httpCode, Error: " . ($error ?: 'none'));

    if ($error) {
        return ['error' => 'Connection error: ' . $error];
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        return ['error' => 'Invalid JSON response'];
    }

    return ['error' => 'API Error: HTTP ' . $httpCode, 'response' => $response];
}

// User prüfen
function getCurrentUser() {
    if (!isset($_SESSION['token'])) {
        return null;
    }

    $user = apiCall('/auth/me', 'GET', null, $_SESSION['token']);
    if (isset($user['error'])) {
        logError('User validation failed: ' . $user['error']);
        unset($_SESSION['token']);
        unset($_SESSION['user']);
        return null;
    }

    return $user;
}

// Redirect Helper
function requireLogin() {
    $user = getCurrentUser();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

// Redirect wenn bereits eingeloggt
function requireGuest() {
    $user = getCurrentUser();
    if ($user) {
        header('Location: dashboard.php');
        exit;
    }
}
