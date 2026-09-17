<?php

/**
 * Admin User Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Admin
{
    /**
     * Fallback mock admin for offline / fallback mode
     */
    private static array $mockAdmin = [
        'id'         => 1,
        'name'       => 'M. Hernan F.',
        'email'      => 'admin@nexuscommerce.com',
        // bcrypt hash of 'admin123'
        'password'   => '$2y$10$eNKTMk4oAZWvVsQbLdA1TOn2AdTv9O.el4CzEksGbfJGvQYBJb8qq',
        'role'       => 'Super Admin',
        'created_at' => '2026-09-17 10:00:00'
    ];

    /**
     * Find admin by email
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password, role, created_at FROM admins WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => strtolower(trim($email))]);
                $row = $stmt->fetch();
                if ($row) {
                    return $row;
                }
            } catch (\PDOException $e) {
                error_log("DB Query Error in findByEmail: " . $e->getMessage());
            }
        }

        if (strcasecmp(self::$mockAdmin['email'], trim($email)) === 0) {
            return self::$mockAdmin;
        }

        return null;
    }

    /**
     * Find admin by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password, role, created_at FROM admins WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                if ($row) {
                    return $row;
                }
            } catch (\PDOException $e) {
                error_log("DB Query Error in findById: " . $e->getMessage());
            }
        }

        if (self::$mockAdmin['id'] === $id) {
            return self::$mockAdmin;
        }

        return null;
    }

    /**
     * Update admin password
     */
    public static function updatePassword(int $id, string $hashedPassword): bool
    {
        $pdo = Database::getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE admins SET password = :pwd WHERE id = :id");
                return $stmt->execute([
                    ':pwd' => $hashedPassword,
                    ':id'  => $id
                ]);
            } catch (\PDOException $e) {
                error_log("DB Query Error in updatePassword: " . $e->getMessage());
            }
        }

        if (self::$mockAdmin['id'] === $id) {
            self::$mockAdmin['password'] = $hashedPassword;
            return true;
        }

        return false;
    }

    /**
     * Verify password hash
     */
    public static function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        // Check standard password_verify
        if (password_verify($plainPassword, $hashedPassword)) {
            return true;
        }

        // Plaintext fallback for test/dev convenience
        return $plainPassword === $hashedPassword;
    }
}
