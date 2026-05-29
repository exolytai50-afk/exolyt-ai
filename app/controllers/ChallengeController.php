<?php
/**
 * Challenge Controller
 */

namespace App\Controllers;

use App\Models\Challenge;
use App\Database\Database;

class ChallengeController {
    private $challengeModel;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $db = new Database($config['database']);
        $this->challengeModel = new Challenge($db);
    }

    public function getActiveChallenges() {
        $challenges = $this->challengeModel->getActive(10);
        echo json_encode($challenges);
    }

    public function getAllChallenges($page = 1) {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $challenges = $this->challengeModel->getAll($limit, $offset);
        echo json_encode([
            'page' => $page,
            'challenges' => $challenges,
        ]);
    }

    public function getChallengeById($id) {
        $challenge = $this->challengeModel->findById($id);
        
        if (!$challenge) {
            http_response_code(404);
            echo json_encode(['error' => 'Challenge not found']);
            return;
        }

        $participants = $this->challengeModel->getChallengeParticipants($id);
        $challenge['participants'] = $participants;
        echo json_encode($challenge);
    }

    public function joinChallenge($id) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        try {
            $this->challengeModel->joinChallenge($_SESSION['user_id'], $id);
            echo json_encode(['message' => 'Joined challenge successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to join challenge']);
        }
    }

    public function completeChallenge($id) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        try {
            $this->challengeModel->completeChallenge($_SESSION['user_id'], $id);
            echo json_encode(['message' => 'Challenge completed successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to complete challenge']);
        }
    }

    public function getUserChallenges() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $challenges = $this->challengeModel->getUserChallenges($_SESSION['user_id']);
        echo json_encode($challenges);
    }
}
