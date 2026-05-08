<?php
/**
 * API Wrapper
 * Zentraler API-Client für alle Anfragen
 */

require_once __DIR__ . '/../config/config.php';

class ApiClient {
    private static $instance = null;
    private $baseUrl;
    private $timeout;

    private function __construct() {
        $this->baseUrl = API_URL;
        $this->timeout = API_TIMEOUT;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * API Request ausführen
     */
    public function request($endpoint, $method = 'GET', $data = null, $token = null) {
        $url = $this->baseUrl . $endpoint;

        logError("API Request: $method $url", [
            'has_data' => $data !== null,
            'has_token' => $token !== null
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

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

        logError("API Response: HTTP $httpCode", [
            'has_error' => $error !== '',
            'error' => $error
        ]);

        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $decoded,
                'status' => $httpCode
            ];
        }

        return [
            'success' => false,
            'error' => 'API Error: HTTP ' . $httpCode,
            'response' => $response,
            'status' => $httpCode,
            'data' => $decoded
        ];
    }

    /**
     * GET Request
     */
    public function get($endpoint, $token = null) {
        return $this->request($endpoint, 'GET', null, $token);
    }

    /**
     * POST Request
     */
    public function post($endpoint, $data = null, $token = null) {
        return $this->request($endpoint, 'POST', $data, $token);
    }

    /**
     * PUT Request
     */
    public function put($endpoint, $data = null, $token = null) {
        return $this->request($endpoint, 'PUT', $data, $token);
    }

    /**
     * DELETE Request
     */
    public function delete($endpoint, $token = null) {
        return $this->request($endpoint, 'DELETE', null, $token);
    }
}

/**
 * Helper Funktion für API Calls (für Kompatibilität)
 */
function apiCall($endpoint, $method = 'GET', $data = null, $token = null) {
    $api = ApiClient::getInstance();
    $result = $api->request($endpoint, $method, $data, $token);

    if ($result['success']) {
        return $result['data'];
    } else {
        return [
            'error' => $result['error'],
            'response' => $result['response'] ?? null
        ];
    }
}
