<?php
/**
 * Deck Service
 * Verwaltet Decks und Deck-Operationen
 */

require_once __DIR__ . '/../includes/api.php';

class DeckService {
    private $api;

    public function __construct() {
        $this->api = ApiClient::getInstance();
    }

    /**
     * Alle Decks abrufen
     */
    public function getAllDecks($token) {
        $result = $this->api->get('/decks', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [];
    }

    /**
     * Deck abrufen
     */
    public function getDeck($deckId, $token) {
        $result = $this->api->get('/decks/' . $deckId, $token);

        if ($result['success']) {
            return $result['data'];
        }

        return null;
    }

    /**
     * Deck erstellen
     */
    public function createDeck($tcgId, $name, $token) {
        $result = $this->api->post('/decks', [
            'tcgId' => $tcgId,
            'name' => $name
        ], $token);

        if ($result['success']) {
            return [
                'success' => true,
                'deck' => $result['data']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Deck erstellen fehlgeschlagen'
        ];
    }

    /**
     * Deck aktualisieren
     */
    public function updateDeck($deckId, $name, $token) {
        $result = $this->api->put('/decks/' . $deckId, [
            'name' => $name
        ], $token);

        if ($result['success']) {
            return [
                'success' => true,
                'deck' => $result['data']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Deck aktualisieren fehlgeschlagen'
        ];
    }

    /**
     * Deck löschen
     */
    public function deleteDeck($deckId, $token) {
        $result = $this->api->delete('/decks/' . $deckId, $token);

        if ($result['success']) {
            return [
                'success' => true
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Deck löschen fehlgeschlagen'
        ];
    }

    /**
     * Karte zum Deck hinzufügen
     */
    public function addCardToDeck($deckId, $cardId, $quantity = 1, $token) {
        $result = $this->api->post('/decks/' . $deckId . '/cards', [
            'cardId' => $cardId,
            'quantity' => $quantity
        ], $token);

        if ($result['success']) {
            return [
                'success' => true
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Karte hinzufügen fehlgeschlagen'
        ];
    }

    /**
     * Karte aus Deck entfernen
     */
    public function removeCardFromDeck($deckId, $cardId, $quantity = 1, $token) {
        $result = $this->api->delete('/decks/' . $deckId . '/cards/' . $cardId, $token);

        if ($result['success']) {
            return [
                'success' => true
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Karte entfernen fehlgeschlagen'
        ];
    }

    /**
     * Deck validieren
     */
    public function validateDeck($deckId, $token) {
        $result = $this->api->get('/decks/' . $deckId . '/validate', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return null;
    }
}
