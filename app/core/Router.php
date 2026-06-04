<?php
/**
 * Router Class - Handle routing
 */

namespace App\Core;

class Router {
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute($method, $path, $handler) {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch($method, $uri) {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');

        // Try to match route
        foreach ($this->routes[$method] as $pattern => $handler) {
            $pattern = trim($pattern, '/');
            
            // Convert pattern to regex
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>\d+)', $pattern);
            $regex = str_replace('/', '\\/', $regex);
            
            if (preg_match("/^$regex$/", $uri, $matches)) {
                // Call handler
                if (is_array($handler)) {
                    $className = $handler[0];
                    $methodName = $handler[1];
                    
                    $class = new $className();
                    
                    // Extract ID parameters
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    
                    if (!empty($params)) {
                        call_user_func_array([$class, $methodName], $params);
                    } else {
                        $class->$methodName();
                    }
                }
                return;
            }
        }

        // Route not found
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
