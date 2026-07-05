<?php
declare(strict_types=1);

namespace Engine;

use Engine\Translation\Translator;

abstract class Controller
{
    public function __construct(
        protected ServiceContainer $container
    ) {}

    protected function getModuleName(): string
    {
        preg_match(
            '#^Module\\\\([^\\\\]+)\\\\#',
            static::class,
            $matches
        );

        if (!isset($matches[1])) {
            throw new \RuntimeException(
                'Cannot determine module name from controller: ' . static::class
            );
        }

        return $matches[1];
    }

    protected function render(string $template, array $data = []): Response
    {
        $templateFile = $template . '.php';

        if (!file_exists($templateFile)) {
            return Response::notFound("Brak szablonu: $template");
        }

        $data['translator'] = $this->container->make('translator');

        extract($data);

        ob_start();
        require $templateFile;
        $content = ob_get_clean();

        return (new Response())->setContent($content);
    }

    protected function redirect(string $url, int $code = 302): Response
    {
        return Response::redirect($url, $code);
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