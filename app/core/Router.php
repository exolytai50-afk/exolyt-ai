<?php
/**
 * Router - Simple URL routing system
 */

namespace App\Core;

class Router {
    private $routes = [];
    private $notFoundHandler;

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
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch($method, $uri) {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = str_replace('/index.php', '', $uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->pathToRegex($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return $this->executeHandler($route['handler'], $matches);
            }
        }

        return $this->notFound();
    }

    private function pathToRegex($path) {
        $path = preg_replace_callback(
            '/{(\w+)}/',
            function($matches) {
                return '(?P<' . $matches[1] . '>[^/]+)';
            },
            $path
        );
        return '#^' . $path . '$#';
    }

    private function executeHandler($handler, $params) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            return call_user_func_array([$controller, $method], $params);
        }

        throw new \Exception('Invalid route handler');
    }

    public function notFound() {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}
