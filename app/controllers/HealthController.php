<?php
/**
 * Health Check Endpoint
 */

namespace App\Controllers;

class HealthController {
    public function check() {
        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'php_version' => phpversion(),
            'extensions' => [
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'json' => extension_loaded('json'),
                'curl' => extension_loaded('curl'),
            ],
        ];

        // Check database connection
        try {
            $config = require __DIR__ . '/../config/config.php';
            $db = new \App\Core\Database($config['database']);
            $health['database'] = 'connected';
        } catch (\Exception $e) {
            $health['database'] = 'disconnected';
            $health['status'] = 'warning';
        }

        echo json_encode($health);
    }
}
