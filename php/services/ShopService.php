<?php
/**
 * Shop Service
 * Verwaltet Shop und Bestellungen
 */

require_once __DIR__ . '/../includes/api.php';

class ShopService {
    private $api;

    public function __construct() {
        $this->api = ApiClient::getInstance();
    }

    /**
     * Alle Packs abrufen
     */
    public function getPacks($token) {
        $result = $this->api->get('/shop/packs', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [];
    }

    /**
     * Bestellung erstellen
     */
    public function createOrder($items, $token) {
        $result = $this->api->post('/shop/orders', [
            'items' => $items
        ], $token);

        if ($result['success']) {
            return [
                'success' => true,
                'order' => $result['data']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Bestellung erstellen fehlgeschlagen'
        ];
    }

    /**
     * Bestellungen abrufen
     */
    public function getOrders($token) {
        $result = $this->api->get('/shop/orders', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [];
    }

    /**
     * Bestellung abrufen
     */
    public function getOrder($orderId, $token) {
        $result = $this->api->get('/shop/orders/' . $orderId, $token);

        if ($result['success']) {
            return $result['data'];
        }

        return null;
    }
}
