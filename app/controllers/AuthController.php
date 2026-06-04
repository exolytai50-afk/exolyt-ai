<?php
/**
 * Update Auth Controller with proper implementation
 */

namespace App\Controllers;

use App\Models\User;
use App\Database\Database;
use App\Helpers\AuthHelper;
use App\Helpers\ResponseHelper;

class AuthController {
    private $userModel;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $db = new Database($config['database']);
        $this->userModel = new User($db);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['username'], $input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        // Check if user exists
        if ($this->userModel->findByEmail($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }

        if ($this->userModel->findByUsername($input['username'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username already exists']);
            return;
        }

        try {
            $userId = $this->userModel->create($input);
            http_response_code(201);
            echo json_encode(['message' => 'Registration successful', 'user_id' => $userId]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing email or password']);
            return;
        }

        if ($this->userModel->verifyPassword($input['email'], $input['password'])) {
            $user = $this->userModel->findByEmail($input['email']);
            unset($user['password_hash']);
            
            AuthHelper::login($user);
            
            echo json_encode(['message' => 'Login successful', 'user' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }

    public function logout() {
        AuthHelper::logout();
        echo json_encode(['message' => 'Logout successful']);
    }

    public function me() {
        if (!AuthHelper::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $user = $this->userModel->findById(AuthHelper::getCurrentUserId());
        unset($user['password_hash']);
        
        echo json_encode($user);
    }
}
