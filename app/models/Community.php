<?php
/**
 * Community Model
 */

namespace App\Models;

use App\Database\Database;

class Community {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function create($data) {
        return $this->db->insert('communities', [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon_url' => $data['icon_url'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }

    public function findById($id) {
        return $this->db->selectOne('communities', '*', 'id = ?', [$id]);
    }

    public function getAll($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM communities ORDER BY members_count DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->fetchAll();
    }

    public function addMember($communityId, $userId) {
        $this->db->insert('community_members', [
            'community_id' => $communityId,
            'user_id' => $userId,
            'role' => 'member',
        ]);
        
        // Update members count
        $community = $this->findById($communityId);
        $this->db->update('communities', 
            ['members_count' => $community['members_count'] + 1], 
            'id = ?', [$communityId]
        );
    }

    public function removeMember($communityId, $userId) {
        $this->db->delete('community_members', 'community_id = ? AND user_id = ?', [$communityId, $userId]);
        
        $community = $this->findById($communityId);
        $this->db->update('communities', 
            ['members_count' => max(0, $community['members_count'] - 1)], 
            'id = ?', [$communityId]
        );
    }

    public function isMember($communityId, $userId) {
        $member = $this->db->selectOne('community_members', '*', 
            'community_id = ? AND user_id = ?', [$communityId, $userId]
        );
        return !is_null($member);
    }

    public function getMembers($communityId) {
        $sql = "SELECT u.*, cm.role FROM users u 
                JOIN community_members cm ON u.id = cm.user_id 
                WHERE cm.community_id = ?";
        return $this->db->query($sql, [$communityId])->fetchAll();
    }

    public function searchByName($query, $limit = 20) {
        $sql = "SELECT * FROM communities WHERE name LIKE ? ORDER BY members_count DESC LIMIT ?";
        return $this->db->query($sql, ['%' . $query . '%', $limit])->fetchAll();
    }
}
