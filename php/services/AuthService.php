<?php
/**
 * Auth Service
 * Verwaltet Authentifizierung und User-Management
 */

require_once __DIR__ . '/../includes/api.php';

class AuthService {
    private $api;

    public function __construct() {
        $this->api = ApiClient::getInstance();
    }

    /**
     * User registrieren
     */
    public function register($email, $username, $password) {
        $result = $this->api->post('/auth/register', [
            'email' => $email,
            'username' => $username,
            'password' => $password
        ]);

        if ($result['success'] && isset($result['data']['token'])) {
            $this->setSession($result['data']['token'], $result['data']['user']);
            return [
                'success' => true,
                'user' => $result['data']['user']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Registrierung fehlgeschlagen'
        ];
    }

    /**
     * User einloggen
     */
    public function login($email, $password) {
        $result = $this->api->post('/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        if ($result['success'] && isset($result['data']['token'])) {
            $this->setSession($result['data']['token'], $result['data']['user']);
            return [
                'success' => true,
                'user' => $result['data']['user']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Login fehlgeschlagen'
        ];
    }

    /**
     * User ausloggen
     */
    public function logout() {
        $this->clearSession();
        return ['success' => true];
    }

    /**
     * Aktuellen User abrufen
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $token = $this->getToken();
        $result = $this->api->get('/auth/me', $token);

        if ($result['success']) {
            return $result['data'];
        }

        // Token ungültig, Session löschen
        $this->clearSession();
        return null;
    }

    /**
     * Prüfen ob User eingeloggt ist
     */
    public function isLoggedIn() {
        return isset($_SESSION['token']) && !empty($_SESSION['token']);
    }

    /**
     * Token abrufen
     */
    public function getToken() {
        return $_SESSION['token'] ?? null;
    }

    /**
     * Session setzen
     */
    private function setSession($token, $user) {
        $_SESSION['token'] = $token;
        $_SESSION['user'] = $user;
    }

    /**
     * Session löschen
     */
    private function clearSession() {
        unset($_SESSION['token']);
        unset($_SESSION['user']);
    }

    /**
     * Redirect wenn nicht eingeloggt
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            redirect('/pages/login.php');
        }
        return $this->getCurrentUser();
    }

    /**
     * Redirect wenn bereits eingeloggt
     */
    public function requireGuest() {
        if ($this->isLoggedIn()) {
            redirect('/pages/dashboard.php');
        }
    }
}
