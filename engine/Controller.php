<?php
declare(strict_types=1);

namespace Engine;

use Engine\Translation\Translator;

abstract class Controller
{
    public function __construct(
        protected ServiceContainer $container
    ) {}

    /**
     * Szablon podajesz względem templates/, bez rozszerzenia: 'example/index'.
     * Zmienne z $data stają się zwykłymi zmiennymi w szablonie.
     */
    protected function render(string $template, array $data = []): Response
    {
        // Szablon nigdy nie pochodzi od użytkownika, ale jedna nieuważna zmiana
        // w kontrolerze i mielibyśmy "../config/database" - stąd ten warunek.
        if (preg_match('#^[A-Za-z0-9_/-]+$#', $template) !== 1 || str_contains($template, '..')) {
            throw new \InvalidArgumentException("Niepoprawna nazwa szablonu: {$template}");
        }

        $file = BASE_PATH . '/templates/' . $template . '.php';

        if (!is_file($file)) {
            return Response::notFound("Brak szablonu: {$template}");
        }

        $data['translator'] = $this->container->make('translator');

        extract($data);

        // Bufor: szablon drukuje przez echo, a my chcemy to mieć jako string w Response.
        ob_start();
        require $file;

        return (new Response())->setContent((string) ob_get_clean());
    }

    protected function redirect(string $url, int $code = 302): Response
    {
        return Response::redirect($url, $code);
    }

    protected function json(array $data, int $code = 200): Response
    {
        return (new Response())
            ->setStatusCode($code)
            ->addHeader('Content-Type', 'application/json; charset=utf-8')
            ->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    protected function db(): Database
    {
        return $this->container->make('db');
    }

    protected function trans(string $key, array $params = []): string
    {
        /** @var Translator $translator */
        $translator = $this->container->make('translator');

        return $translator->trans($key, $params);
    }
}
