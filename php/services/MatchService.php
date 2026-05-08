<?php
/**
 * Match Service
 * Verwaltet Matches und Matchmaking
 */

require_once __DIR__ . '/../includes/api.php';

class MatchService {
    private $api;

    public function __construct() {
        $this->api = ApiClient::getInstance();
    }

    /**
     * Alle Matches abrufen
     */
    public function getAllMatches($token) {
        $result = $this->api->get('/matches', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [];
    }

    /**
     * Match abrufen
     */
    public function getMatch($matchId, $token) {
        $result = $this->api->get('/matches/' . $matchId, $token);

        if ($result['success']) {
            return $result['data'];
        }

        return null;
    }

    /**
     * Spielzug machen
     */
    public function submitMove($matchId, $playerId, $cardId, $position, $token) {
        $result = $this->api->post('/matches/' . $matchId . '/moves', [
            'playerId' => $playerId,
            'cardId' => $cardId,
            'position' => $position
        ], $token);

        if ($result['success']) {
            return [
                'success' => true,
                'gameState' => $result['data']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Spielzug fehlgeschlagen'
        ];
    }

    /**
     * Match beenden
     */
    public function endMatch($matchId, $winnerId, $token) {
        $result = $this->api->post('/matches/' . $matchId . '/end', [
            'winnerId' => $winnerId
        ], $token);

        if ($result['success']) {
            return [
                'success' => true,
                'match' => $result['data']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Match beenden fehlgeschlagen'
        ];
    }

    /**
     * Match-Historie abrufen
     */
    public function getMatchHistory($matchId, $token) {
        $result = $this->api->get('/matches/' . $matchId . '/history', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [];
    }

    /**
     * User-Matches filtern
     */
    public function getUserMatches($userId, $token) {
        $allMatches = $this->getAllMatches($token);
        $userMatches = [];

        foreach ($allMatches as $match) {
            if (($match['player1']['id'] ?? '') === $userId ||
                ($match['player2']['id'] ?? '') === $userId) {
                $userMatches[] = $match;
            }
        }

        return $userMatches;
    }

    /**
     * Aktive Matches filtern
     */
    public function getActiveMatches($userId, $token) {
        $userMatches = $this->getUserMatches($userId, $token);
        return array_filter($userMatches, function($match) {
            return ($match['status'] ?? '') === 'active';
        });
    }

    /**
     * Abgeschlossene Matches filtern
     */
    public function getFinishedMatches($userId, $token) {
        $userMatches = $this->getUserMatches($userId, $token);
        return array_filter($userMatches, function($match) {
            return ($match['status'] ?? '') === 'finished';
        });
    }
}
