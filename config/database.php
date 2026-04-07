<?php

declare(strict_types=1);

/**
 * Database configuration
 * Values come from environment variables (set in .env / docker-compose)
 */
return [
    'host'     => $_ENV['DB_HOST']     ?? 'mysql',
    'port'     => $_ENV['DB_PORT']     ?? '3306',
    'dbname'   => $_ENV['DB_NAME']     ?? 'databridge',
    'username' => $_ENV['DB_USER']     ?? 'dbuser',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
