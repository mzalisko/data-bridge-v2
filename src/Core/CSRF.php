<?php

declare(strict_types=1);

namespace DataBridge\Core;

final class CSRF
{
    private const TOKEN_KEY    = '_csrf_token';
    private const TOKEN_LENGTH = 32; // bytes → 64 hex chars

    public static function getToken(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function verify(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';
        $stored    = $_SESSION[self::TOKEN_KEY] ?? '';

        if (!hash_equals($stored, $submitted)) {
            Logger::log('security.csrf.mismatch', null, 'warning', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
            http_response_code(403);
            exit('Invalid CSRF token');
        }

        // Rotate after successful verification (one-time token)
        unset($_SESSION[self::TOKEN_KEY]);
    }
}
