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
        $this->uri     = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
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

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }
}