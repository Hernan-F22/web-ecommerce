<?php

/**
 * Application Entrypoint (Serverless on Vercel & Local PHP Server Compatible)
 */

declare(strict_types=1);

// Handle static files directly when running on PHP built-in web server
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $publicPath = dirname(__DIR__) . '/public' . $path;
    if (is_file($publicPath)) {
        return false;
    }
}

// Simple & robust PSR-4 autoloader without Composer requirement
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));

    // Map namespaces to folders with case-insensitive fallback (for Linux/Vercel)
    $candidates = [];
    if (str_starts_with($relativeClass, 'Config\\')) {
        $sub = substr($relativeClass, 7);
        $norm = str_replace('\\', '/', $sub);
        $candidates[] = $baseDir . 'config/' . $norm . '.php';
        $candidates[] = $baseDir . 'config/' . strtolower($norm) . '.php';
        $candidates[] = $baseDir . 'config/' . ucfirst($norm) . '.php';
    } else {
        // App\Controllers\... -> src/Controllers/...
        // App\Models... -> src/Models/...
        // App\Utils\... -> src/Utils/...
        $norm = str_replace('\\', '/', $relativeClass);
        $candidates[] = $baseDir . 'src/' . $norm . '.php';
        $candidates[] = $baseDir . 'src/' . strtolower($norm) . '.php';
    }

    foreach ($candidates as $cand) {
        if (file_exists($cand)) {
            require_once $cand;
            return;
        }
    }
});

// Global exception handler to always return JSON on API routes
set_exception_handler(function (\Throwable $e) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (str_starts_with($uri, '/api/')) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Server Error: ' . $e->getMessage()
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    http_response_code(500);
    echo "<h1>500 Internal Server Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
});

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Utils\Router;
use App\Controllers\DashboardController;
use App\Controllers\InventoryController;
use App\Controllers\ReportController;
use App\Controllers\AuthController;

$router = new Router();

// Auto-detect base path for redirects
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if (str_ends_with($scriptDir, '/api')) {
    $basePath = dirname($scriptDir);
} else {
    $basePath = $scriptDir;
}
$basePath = rtrim($basePath, '/');

// -----------------------------------------------------------------------------
// View Routes
// -----------------------------------------------------------------------------
$router->get('/login', function () use ($basePath) {
    if (AuthController::isAuthenticated()) {
        header("Location: " . ($basePath ?: '') . "/");
        exit;
    }
    $viewFile = dirname(__DIR__) . '/views/login.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "Login view not found.";
    }
});

$router->get('/logout', function () {
    (new AuthController())->logout();
});

$router->get('/', function () use ($basePath) {
    if (!AuthController::isAuthenticated()) {
        header("Location: " . ($basePath ?: '') . "/login");
        exit;
    }
    $viewFile = dirname(__DIR__) . '/views/dashboard.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "Dashboard view not found.";
    }
});

$router->get('/index.php', function () use ($basePath) {
    if (!AuthController::isAuthenticated()) {
        header("Location: " . ($basePath ?: '') . "/login");
        exit;
    }
    $viewFile = dirname(__DIR__) . '/views/dashboard.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "Dashboard view not found.";
    }
});

// -----------------------------------------------------------------------------
// Authentication Endpoints
// -----------------------------------------------------------------------------
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);
$router->post('/api/auth/change-password', [AuthController::class, 'changePassword']);

// -----------------------------------------------------------------------------
// API Endpoints
// -----------------------------------------------------------------------------
$router->get('/api/dashboard-summary', [DashboardController::class, 'summary']);
$router->post('/api/marketing', [DashboardController::class, 'recordMetric']);
$router->post('/api/marketing/reset', [DashboardController::class, 'resetMetrics']);
$router->get('/api/inventory', [InventoryController::class, 'index']);
$router->post('/api/inventory', [InventoryController::class, 'store']);
$router->get('/api/reports', [ReportController::class, 'index']);

// Dispatch routing
$router->dispatch();
