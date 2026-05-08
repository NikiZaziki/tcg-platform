<?php
/**
 * Inventory Service
 * Verwaltet User Inventory
 */

require_once __DIR__ . '/../includes/api.php';

class InventoryService {
    private $api;

    public function __construct() {
        $this->api = ApiClient::getInstance();
    }

    /**
     * User Inventory abrufen
     */
    public function getInventory($token) {
        $result = $this->api->get('/inventory', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [];
    }

    /**
     * Karten-Anzahl abrufen
     */
    public function getCardQuantity($cardId, $token) {
        $inventory = $this->getInventory($token);

        foreach ($inventory as $item) {
            if (($item['card']['id'] ?? '') === $cardId) {
                return $item['quantity'] ?? 0;
            }
        }

        return 0;
    }

    /**
     * Karte abrufen
     */
    public function getCard($cardId, $token) {
        $inventory = $this->getInventory($token);

        foreach ($inventory as $item) {
            if (($item['card']['id'] ?? '') === $cardId) {
                return $item['card'];
            }
        }

        return null;
    }

    /**
     * Prüfen ob User Karte hat
     */
    public function hasCard($cardId, $quantity = 1, $token) {
        return $this->getCardQuantity($cardId, $token) >= $quantity;
    }
}
