<?php

/**
 * Lightweight URL Request Router
 */

declare(strict_types=1);

namespace App\Utils;

class Router
{
    private array $routes = [];

    /**
     * Register a GET route
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add route definition
     */
    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => rtrim($path, '/') ?: '/',
            'handler' => $handler
        ];
    }

    /**
     * Dispatch incoming request
     */
    public function dispatch(): void
    {
        Response::handleOptions();

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($requestMethod === 'HEAD') {
            $requestMethod = 'GET';
        }
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Auto-detect and strip subfolder prefix (handles both root index.php and api/index.php)
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if (str_ends_with($scriptDir, '/api')) {
            $baseDir = dirname($scriptDir);
        } else {
            $baseDir = $scriptDir;
        }
        $baseDir = rtrim($baseDir, '/');

        if ($baseDir !== '' && $baseDir !== '/' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir)) ?: '/';
        }

        // Normalize URI: trim trailing slash except root
        $uri = rtrim($uri, '/') ?: '/';

        // Check against registered routes
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $uri)) {
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action();
                } else {
                    call_user_func($handler);
                }
                return;
            }
        }

        // Handle 404
        if (str_starts_with($uri, '/api/')) {
            Response::error("Endpoint not found: {$requestMethod} {$uri}", 404);
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>The requested URL {$uri} was not found on this server.</p>";
        }
    }

    /**
     * Simple path matching
     */
    private function matchPath(string $routePath, string $uri): bool
    {
        return $routePath === $uri;
    }
}
