<?php
/**
 * Auth Controller - Handle authentication
 */

namespace App\Controllers;

use App\Models\User;
use App\Database\Database;

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

        if (!$input || !isset($input['username'], $input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        if ($this->userModel->findByEmail($input['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }

        if ($this->userModel->findByUsername($input['username'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Username already exists']);
            return;
        }

        try {
            $this->userModel->create($input);
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully']);
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

        if (!$input || !isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing email or password']);
            return;
        }

        if (!$this->userModel->verifyPassword($input['email'], $input['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $user = $this->userModel->findByEmail($input['email']);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        http_response_code(200);
        echo json_encode([
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
            ]
        ]);
    }

    public function logout() {
        session_destroy();
        echo json_encode(['message' => 'Logged out successfully']);
    }

    public function me() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        echo json_encode($user);
    }
}
