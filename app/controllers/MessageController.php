<?php
/**
 * Message Controller
 */

namespace App\Controllers;

use App\Models\Message;
use App\Database\Database;

class MessageController {
    private $messageModel;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $db = new Database($config['database']);
        $this->messageModel = new Message($db);
    }

    public function sendMessage($toUserId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['text'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Message text is required']);
            return;
        }

        try {
            $this->messageModel->sendMessage($_SESSION['user_id'], $toUserId, $input['text']);
            http_response_code(201);
            echo json_encode(['message' => 'Message sent successfully']);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getConversation($otherUserId) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $messages = $this->messageModel->getConversation($_SESSION['user_id'], $otherUserId);
        
        // Mark as read
        $this->messageModel->markConversationAsRead($_SESSION['user_id'], $otherUserId);

        echo json_encode($messages);
    }

    public function getInbox() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $inbox = $this->messageModel->getUserInbox($_SESSION['user_id']);
        echo json_encode($inbox);
    }

    public function getUnreadCount() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $count = $this->messageModel->getUnreadCount($_SESSION['user_id']);
        echo json_encode(['count' => $count]);
    }

    public function deleteMessage($messageId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        try {
            $this->messageModel->deleteMessage($messageId);
            echo json_encode(['message' => 'Message deleted successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
