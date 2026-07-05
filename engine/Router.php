<?php
declare(strict_types=1);

namespace Engine;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    public function match(Request $request): ?array
    {
        foreach ($this->routes as $route) {
            if (
                $route['method'] === $request->method &&
                $this->matchPath($route['path'], $request->uri)
            ) {
                return $route;
            }
        }

        return null;
    }

    private function matchPath(string $routePath, string $requestUri): bool
    {
        // Obsługa parametrów np. /event/{id}
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        return (bool) preg_match($pattern, $requestUri);
    }

    public function getParams(string $routePath, string $requestUri): array
    {
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        preg_match($pattern, $requestUri, $values);
        array_shift($values);

        return array_combine($paramNames[1], $values) ?: [];
    }
}