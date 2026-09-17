<?php

/**
 * Authentication Controller
 * Handles Admin Login, Session Management, and Logout
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Admin;
use App\Utils\Response;

class AuthController
{
    /**
     * Start session safely if not already active
     */
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if admin is currently authenticated
     */
    public static function isAuthenticated(): bool
    {
        self::startSession();
        return !empty($_SESSION['admin_user']);
    }

    /**
     * Get current authenticated admin user
     */
    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['admin_user'] ?? null;
    }

    /**
     * POST /api/auth/login
     */
    public function login(): void
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $email = trim(strip_tags($data['email'] ?? ''));
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            Response::error('Email dan kata sandi wajib diisi', 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Format email tidak valid', 422);
            return;
        }

        $admin = Admin::findByEmail($email);
        if (!$admin) {
            Response::error('Akun admin tidak ditemukan', 404);
            return;
        }

        if (!Admin::verifyPassword($password, $admin['password'])) {
            Response::error('Kata sandi yang dimasukkan salah', 401);
            return;
        }

        // Authentication successful: establish session
        self::startSession();
        session_regenerate_id(true);

        $userData = [
            'id'    => (int)$admin['id'],
            'name'  => (string)$admin['name'],
            'email' => (string)$admin['email'],
            'role'  => (string)$admin['role']
        ];

        $_SESSION['admin_user'] = $userData;

        Response::json($userData, 'Login berhasil! Mengalihkan ke dashboard...');
    }

    /**
     * POST /api/auth/logout & GET /logout
     */
    public function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'HEAD') {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $basePath = rtrim(str_ends_with($scriptDir, '/api') ? dirname($scriptDir) : $scriptDir, '/');
            $loginUrl = ($basePath ?: '') . '/login';
            header("Location: {$loginUrl}");
            exit;
        }

        Response::json(null, 'Berhasil logout');
    }

    /**
     * GET /api/auth/me
     */
    public function me(): void
    {
        if (!self::isAuthenticated()) {
            Response::error('Unauthenticated', 401);
            return;
        }

        Response::json(self::user(), 'Sesi aktif');
    }

    /**
     * POST /api/auth/change-password
     */
    public function changePassword(): void
    {
        if (!self::isAuthenticated()) {
            Response::error('Silakan login terlebih dahulu', 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $currentPassword = trim($data['current_password'] ?? '');
        $newPassword = trim($data['new_password'] ?? '');
        $confirmPassword = trim($data['confirm_password'] ?? '');

        if (empty($currentPassword) || empty($newPassword)) {
            Response::error('Password saat ini dan password baru wajib diisi', 422);
            return;
        }

        if (strlen($newPassword) < 6) {
            Response::error('Password baru minimal 6 karakter', 422);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Response::error('Konfirmasi password baru tidak cocok', 422);
            return;
        }

        $userSession = self::user();
        $admin = Admin::findById((int)$userSession['id']);
        if (!$admin) {
            Response::error('Akun admin tidak ditemukan', 404);
            return;
        }

        if (!Admin::verifyPassword($currentPassword, $admin['password'])) {
            Response::error('Password saat ini tidak sesuai', 400);
            return;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $updated = Admin::updatePassword((int)$admin['id'], $newHash);

        if ($updated) {
            Response::json(null, 'Password berhasil diperbarui');
        } else {
            Response::error('Gagal memperbarui password', 500);
        }
    }
}
