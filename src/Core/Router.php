<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];

    public function addRoute(string $method, string $path, string $handler, ?string $name = null): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $this->convertToRegex($path),
            'handler' => $handler,
            'name' => $name,
        ];

        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }

    private function convertToRegex(string $path): string
    {
        return '#^' . preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path) . '$#';
    }

    public function match(string $method, string $uri): ?array
    {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                return [
                    'handler' => $route['handler'],
                    'params' => array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY),
                    'route' => $route,
                ];
            }
        }

        return null;
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route '$name' not found");
        }

        $path = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", (string) $value, $path);
        }

        return $path;
    }

    public function loadRoutes(string $routesFile): void
    {
        foreach (require $routesFile as $route) {
            $this->addRoute($route[0], $route[1], $route[2], $route[3] ?? null);
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
