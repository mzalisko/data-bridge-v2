<?php

declare(strict_types=1);

namespace DataBridge\Core;

use RuntimeException;

final class Router
{
    /** @var array<string, array<string, array{0: string, 1: string}>> */
    private array $routes = [];

    public function get(string $uri, array $handler): void
    {
        $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, array $handler): void
    {
        $this->addRoute('POST', $uri, $handler);
    }

    private function addRoute(string $method, string $uri, array $handler): void
    {
        $this->routes[$method][$uri] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = rtrim($uri, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $params = $this->match($pattern, $uri);
            if ($params !== null) {
                [$class, $action] = $handler;
                $controller = new $class();
                $controller->$action(...$params);
                return;
            }
        }

        $this->notFound();
    }

    /**
     * Match URI against a route pattern.
     * Returns array of param values on match, null on no match.
     * Pattern segments like {id} capture int-only values.
     *
     * @return int[]|null
     */
    private function match(string $pattern, string $uri): ?array
    {
        $pattern = rtrim($pattern, '/') ?: '/';

        // Build regex: replace {param} with named capture group (digits only)
        $regex = preg_replace('/\{([a-z_]+)\}/', '([0-9]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $uri, $matches)) {
            return null;
        }

        array_shift($matches); // remove full match
        return array_map('intval', $matches);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }
}
