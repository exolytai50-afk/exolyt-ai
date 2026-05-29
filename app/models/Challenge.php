<?php
/**
 * Challenge Model
 */

namespace App\Models;

use App\Database\Database;

class Challenge {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function create($data) {
        return $this->db->insert('challenges', [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'start_date' => $data['start_date'] ?? date('Y-m-d'),
            'end_date' => $data['end_date'] ?? date('Y-m-d', strtotime('+7 days')),
            'reward_points' => $data['reward_points'] ?? 100,
        ]);
    }

    public function findById($id) {
        return $this->db->selectOne('challenges', '*', 'id = ?', [$id]);
    }

    public function getActive($limit = 10) {
        $sql = "SELECT * FROM challenges 
                WHERE start_date <= CURDATE() AND end_date >= CURDATE()
                ORDER BY end_date ASC LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }

    public function getAll($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM challenges ORDER BY start_date DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->fetchAll();
    }

    public function joinChallenge($userId, $challengeId) {
        return $this->db->insert('user_challenges', [
            'user_id' => $userId,
            'challenge_id' => $challengeId,
            'status' => 'in_progress',
        ]);
    }

    public function completeChallenge($userId, $challengeId) {
        return $this->db->update('user_challenges', 
            ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')],
            'user_id = ? AND challenge_id = ?',
            [$userId, $challengeId]
        );
    }

    public function getUserChallenges($userId) {
        $sql = "SELECT c.*, uc.status, uc.joined_at, uc.completed_at FROM challenges c
                JOIN user_challenges uc ON c.id = uc.challenge_id
                WHERE uc.user_id = ?
                ORDER BY c.start_date DESC";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function getChallengeParticipants($challengeId) {
        $sql = "SELECT u.*, uc.status, uc.joined_at, uc.completed_at FROM users u
                JOIN user_challenges uc ON u.id = uc.user_id
                WHERE uc.challenge_id = ? AND uc.status = 'completed'
                ORDER BY uc.completed_at ASC";
        return $this->db->query($sql, [$challengeId])->fetchAll();
    }
}
