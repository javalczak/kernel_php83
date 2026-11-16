<?php
declare(strict_types=1);

namespace Engine;

class Request
{
    public readonly string $method;
    public readonly string $uri;
    public readonly array $query;
    public readonly array $post;
    public readonly array $server;
    public readonly array $cookies;
    public readonly array $files;

    public function __construct()
    {
        $this->method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // strtok ucina query string: /items?page=2 → /items
        $this->uri     = (string) (strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/');
        $this->query   = $_GET;
        $this->post    = $_POST;
        $this->server  = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files   = $_FILES;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Pole formularza jako string - i tylko jako string.
     *
     * Wysyłając pole o nazwie "title[]" dostajesz w $_POST tablicę. Rzutowanie
     * (string) na tablicy to w PHP 8 błąd, czyli 500 zamiast walidacji.
     * Dlatego wszystko, co nie jest skalarem, traktujemy jak brak wartości.
     */
    public function string(string $key, string $default = ''): string
    {
        $value = $this->post[$key] ?? $this->query[$key] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->string($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }
}
