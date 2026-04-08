<?php

declare(strict_types=1);

namespace DataBridge\Core;

use RuntimeException;

final class View
{
    /**
     * Render a page template wrapped in the CRM shell layout.
     *
     * @param string               $template  Relative path inside src/Views/ (no .php)
     * @param array<string, mixed> $data      Variables extracted into template scope
     */
    public static function render(string $template, array $data = []): void
    {
        $templatePath = self::resolve($template);

        // Capture page-specific HTML
        ob_start();
        (static function () use ($templatePath, $data): void {
            extract($data, EXTR_SKIP);
            require $templatePath;
        })();
        $content = ob_get_clean();

        // Inject into layout shell
        $title   = htmlspecialchars((string) ($data['title'] ?? 'DataBridge CRM'), ENT_QUOTES, 'UTF-8');
        $csrf    = $data['csrf'] ?? '';
        $layoutPath = BASE_PATH . '/src/Views/Layout.php';
        require $layoutPath;
    }

    /**
     * Render a standalone template without the shell (login page, errors, API).
     *
     * @param string               $template
     * @param array<string, mixed> $data
     */
    public static function renderBare(string $template, array $data = []): void
    {
        $templatePath = self::resolve($template);
        (static function () use ($templatePath, $data): void {
            extract($data, EXTR_SKIP);
            require $templatePath;
        })();
    }

    private static function resolve(string $template): string
    {
        $path = BASE_PATH . '/src/Views/' . $template . '.php';
        if (!file_exists($path)) {
            throw new RuntimeException("View not found: {$path}");
        }
        return $path;
    }
}
