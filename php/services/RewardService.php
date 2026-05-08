<?php
/**
 * Reward Service
 * Verwaltet Rewards und Daily Rewards
 */

require_once __DIR__ . '/../includes/api.php';

class RewardService {
    private $api;

    public function __construct() {
        $this->api = ApiClient::getInstance();
    }

    /**
     * Daily Reward Status abrufen
     */
    public function getDailyReward($token) {
        $result = $this->api->get('/rewards/daily', $token);

        if ($result['success']) {
            return $result['data'];
        }

        return [
            'claimed' => false,
            'canClaim' => false
        ];
    }

    /**
     * Daily Reward abholen
     */
    public function claimDailyReward($token) {
        $result = $this->api->post('/rewards/daily/claim', null, $token);

        if ($result['success']) {
            return [
                'success' => true,
                'reward' => $result['data']
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Reward abholen fehlgeschlagen'
        ];
    }

    /**
     * Prüfen ob Daily Reward abholbar ist
     */
    public function canClaimDailyReward($token) {
        $dailyReward = $this->getDailyReward($token);
        return !($dailyReward['claimed'] ?? false);
    }
}
