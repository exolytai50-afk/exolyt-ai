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
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        return $this->db->insert('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $passwordHash,
            'bio' => $data['bio'] ?? null,
            'avatar_url' => $data['avatar_url'] ?? null,
            'location' => $data['location'] ?? null,
            'is_active' => true,
        ]);
    }

    public function findById($id) {
        return $this->db->selectOne('users', '*', 'id = ?', [$id]);
    }

    public function findByEmail($email) {
        return $this->db->selectOne('users', '*', 'email = ?', [$email]);
    }

    public function findByUsername($username) {
        return $this->db->selectOne('users', '*', 'username = ?', [$username]);
    }

    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        return password_verify($password, $user['password_hash']);
    }

    public function update($id, $data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        return $this->db->update('users', $data, 'id = ?', [$id]);
    }

    public function getTopUsers($limit = 10) {
        $sql = "SELECT * FROM users WHERE is_active = TRUE ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }

    public function getAllUsers($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM users WHERE is_active = TRUE ORDER BY username ASC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->fetchAll();
    }

    public function deactivate($id) {
        return $this->db->update('users', ['is_active' => false], 'id = ?', [$id]);
    }

    public function delete($id) {
        return $this->db->delete('users', 'id = ?', [$id]);
    }
}
