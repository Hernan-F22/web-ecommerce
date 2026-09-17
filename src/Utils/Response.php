<?php

/**
 * Standardized JSON API Response Helper
 */

declare(strict_types=1);

namespace App\Utils;

class Response
{
    /**
     * Send a successful JSON response
     */
    public static function json(mixed $data = null, string $message = 'Success', int $statusCode = 200): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }

        echo json_encode([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send an error JSON response
     */
    public static function error(string $message = 'Internal Server Error', int $statusCode = 500, mixed $errors = null): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }

        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Handle preflight OPTIONS request
     */
    public static function handleOptions(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            http_response_code(204);
            exit;
        }
    }
}
