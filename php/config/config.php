<?php
/**
 * Konfiguration
 */

// API Konfiguration
define('API_URL', 'http://45.131.111.6:3000');
define('API_TIMEOUT', 30);

// Debug Modus
define('DEBUG', true);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? 1 : 0);

// Session Konfiguration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Setze auf 1 wenn HTTPS verwendet wird

// Session starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logging Funktion
function logError($message, $context = []) {
    if (DEBUG) {
        $logMessage = '[TCG-DEBUG] ' . $message;
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }
        error_log($logMessage);
    }
}

// Helper für JSON-Ausgabe
function jsonOutput($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper für Redirect
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Helper für CSRF-Token (später implementieren)
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
