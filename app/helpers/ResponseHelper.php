<?php
/**
 * Response Helper
 */

namespace App\Helpers;

class ResponseHelper {
    public static function success($data, $message = 'Success', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error($message = 'Error', $code = 400) {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
        ]);
    }

    public static function paginated($items, $page = 1, $total = 0) {
        return [
            'page' => $page,
            'items' => $items,
            'total' => $total,
            'per_page' => 20,
        ];
    }
}
