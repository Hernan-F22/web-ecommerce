<?php

/**
 * Database Connection Manager (PDO)
 * Designed for TiDB Cloud Serverless & Standard MySQL 8.x
 */

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static bool $isMockMode = false;
    private static ?string $connectionError = null;

    /**
     * Get or create PDO Database Connection
     */
    public static function getConnection(): ?PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Read configuration from environment or defaults
        $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? '127.0.0.1'));
        $port = (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? 3306)));
        $dbname = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? 'ecommerce_ops'));
        $username = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? 'root'));
        $password = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? ($_SERVER['DB_PASSWORD'] ?? ''));
        $sslCa = getenv('DB_SSL_CA') ?: ($_ENV['DB_SSL_CA'] ?? ($_SERVER['DB_SSL_CA'] ?? ''));

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];

        // TiDB Cloud Serverless requires SSL
        // Check for specified SSL CA path or standard Linux/Vercel paths
        if (!empty($sslCa) && file_exists($sslCa)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        } elseif (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
        } elseif (file_exists('/etc/pki/tls/certs/ca-bundle.crt')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/pki/tls/certs/ca-bundle.crt';
        } else {
            // Fallback for environments with internal CA management or local dev
            if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
        }

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
            self::$isMockMode = false;
            return self::$instance;
        } catch (PDOException $e) {
            // If database connection fails (e.g. credentials not configured yet),
            // record error and allow graceful degradation to realistic mock repository
            self::$connectionError = $e->getMessage();
            self::$isMockMode = true;
            return null;
        }
    }

    /**
     * Check if currently running in mock repository fallback mode
     */
    public static function isMockMode(): bool
    {
        if (self::$instance === null && self::$connectionError === null) {
            self::getConnection();
        }
        return self::$isMockMode;
    }

    /**
     * Get the connection error message if any
     */
    public static function getConnectionError(): ?string
    {
        return self::$connectionError;
    }
}
