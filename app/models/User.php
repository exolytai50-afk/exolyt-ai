<?php
/**
 * User Model
 */

namespace App\Models;

use App\Database\Database;

class User {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        return $this->db->insert('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $hashedPassword,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
        ]);
    }

    public function findByEmail($email) {
        return $this->db->selectOne('users', '*', 'email = ?', [$email]);
    }

    public function findByUsername($username) {
        return $this->db->selectOne('users', '*', 'username = ?', [$username]);
    }

    public function findById($id) {
        return $this->db->selectOne('users', '*', 'id = ?', [$id]);
    }

    public function update($id, $data) {
        return $this->db->update('users', $data, 'id = ?', [$id]);
    }

    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);
        if (!$user) return false;
        return password_verify($password, $user['password_hash']);
    }

    public function getAllUsers($limit = 50, $offset = 0) {
        $sql = "SELECT id, username, email, first_name, last_name, avatar_url, bio, created_at 
                FROM users WHERE is_active = TRUE 
                ORDER BY created_at DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->fetchAll();
    }

    public function getTopUsers($limit = 10) {
        $sql = "SELECT u.*, COUNT(r.id) as recipe_count, COUNT(DISTINCT f.id) as followers
                FROM users u
                LEFT JOIN recipes r ON u.id = r.user_id
                LEFT JOIN friendships f ON u.id = f.user_id_2 AND f.status = 'accepted'
                WHERE u.is_active = TRUE
                GROUP BY u.id
                ORDER BY recipe_count DESC, followers DESC
                LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }
}
