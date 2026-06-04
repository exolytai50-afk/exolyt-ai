<?php
/**
 * Authentication Helper
 */

namespace App\Helpers;

class AuthHelper {
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
    }

    public static function logout() {
        session_destroy();
    }

    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
    }
}
