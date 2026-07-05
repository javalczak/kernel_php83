<?php
declare(strict_types=1);

namespace Engine;

class Response
{
    private string $content = '';
    private int $statusCode = 200;
    private array $headers = [];

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    public function addHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }

    public static function notFound(string $message = 'Not found'): static
    {
        return (new static())
            ->setStatusCode(404)
            ->setContent($message);
    }

    public static function redirect(string $url, int $code = 302): static
    {
        return (new static())
            ->setStatusCode($code)
            ->addHeader('Location', $url);
    }
}