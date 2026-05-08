<?php
/**
 * Matchmaking Service
 * Verwaltet Matchmaking Queue
 */

require_once __DIR__ . '/../includes/api.php';

class MatchmakingService {
    private $api;

    public function __construct() {
        $this->api = ApiClient::getInstance();
    }

    /**
     * Matchmaking Queue beitreten
     */
    public function joinQueue($tcgId, $mode, $deckId, $token) {
        $result = $this->api->post('/matchmaking/queue', [
            'tcgId' => $tcgId,
            'mode' => $mode,
            'deckId' => $deckId
        ], $token);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => $result['data']['message'] ?? 'Queue beigetreten',
                'position' => $result['data']['position'] ?? 0,
                'mode' => $mode
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Queue beitreten fehlgeschlagen'
        ];
    }

    /**
     * Matchmaking Queue verlassen
     */
    public function leaveQueue($token) {
        $result = $this->api->delete('/matchmaking/queue', $token);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Queue verlassen'
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Queue verlassen fehlgeschlagen'
        ];
    }

    /**
     * Queue Status abrufen
     */
    public function getQueueStatus($token) {
        $result = $this->api->get('/matchmaking/status', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [
            'isInQueue' => false,
            'position' => -1,
            'queueSize' => 0,
            'mode' => null,
            'estimatedWaitTime' => 0
        ];
    }

    /**
     * Prüfen ob User in Queue ist
     */
    public function isInQueue($token) {
        $status = $this->getQueueStatus($token);
        return $status['isInQueue'] ?? false;
    }
}
