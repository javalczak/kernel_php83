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

    /** Pierwsza pasująca trasa wygrywa, więc konkretne ścieżki wpisuj przed tymi z {parametrem}. */
    public function match(Request $request): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $request->method && preg_match($this->wzorzec($route['path']), $request->uri) === 1) {
                return $route;
            }
        }

        return null;
    }

    /** @return array<string, string> nazwa parametru => wartość z URL-a */
    public function getParams(string $routePath, string $requestUri): array
    {
        preg_match_all('/\{([^}]+)\}/', $routePath, $nazwy);

        if ($nazwy[1] === [] || preg_match($this->wzorzec($routePath), $requestUri, $wartosci) !== 1) {
            return [];
        }

        array_shift($wartosci); // [0] to całe dopasowanie, nie parametr

        // rawurldecode, bo w URL-u spacja przyjedzie jako %20 i slug by się nie zgadzał
        $wartosci = array_map(rawurldecode(...), $wartosci);

        return array_combine($nazwy[1], $wartosci);
    }

    /**
     * Ścieżka trasy na wyrażenie regularne. preg_quote jest tu kluczowe:
     * bez niego kropka w trasie /plik.json znaczyłaby "dowolny znak".
     */
    private function wzorzec(string $routePath): string
    {
        $czesci = preg_split('/(\{[^}]+\})/', $routePath, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $wzorzec = '';

        foreach ($czesci as $czesc) {
            $wzorzec .= str_starts_with($czesc, '{') ? '([^/]+)' : preg_quote($czesc, '#');
        }

        return '#^' . $wzorzec . '$#';
    }
}
