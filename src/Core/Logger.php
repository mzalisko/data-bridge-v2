<?php

declare(strict_types=1);

namespace DataBridge\Core;

final class Logger
{
    public static function log(
        string $event,
        ?int   $userId  = null,
        string $level   = 'info',
        array  $context = []
    ): void {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO system_logs (user_id, event, level, context, ip_address)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $event,
                $level,
                $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable) {
            // Logging must never crash the application
        }
    }
}
