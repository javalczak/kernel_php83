<?php
declare(strict_types=1);

namespace Engine;

use Engine\Translation\Translator;

class Kernel
{
    private ServiceContainer $container;
    private Router $router;

    public function __construct()
    {
        $this->container = new ServiceContainer();
        $this->router    = new Router();
    }

    public function boot(): void
    {
        $this->registerCoreServices();
        $this->loadTranslations();
        $this->registerRoutes();
    }

    public function createRequest(): Request
    {
        return new Request();
    }

    public function handle(Request $request): Response
    {
        $route = $this->router->match($request);

        if ($route === null) {
            return Response::notFound('Strona nie istnieje.');
        }

        $controllerClass = $route['controller'];
        $action          = $route['action'];

        if (!class_exists($controllerClass)) {
            return Response::notFound('Kontroler nie istnieje.');
        }

        $controller = new $controllerClass($this->container);

        // method_exists tu nie wystarczy: widzi też metody prywatne i chronione,
        // a wywołanie takiej z zewnątrz kończy się błędem 500 zamiast czystym 404.
        if (!$this->akcjaPubliczna($controller, $action)) {
            return Response::notFound('Akcja nie istnieje.');
        }

        $params = $this->router->getParams($route['path'], $request->uri);

        return $controller->$action($request, $params);
    }

    public function getContainer(): ServiceContainer
    {
        return $this->container;
    }

    // -------------------------------------------------------------------------
    // Prywatne
    // -------------------------------------------------------------------------

    private function akcjaPubliczna(object $controller, string $action): bool
    {
        if (!method_exists($controller, $action)) {
            return false;
        }

        return (new \ReflectionMethod($controller, $action))->isPublic();
    }

    private function registerCoreServices(): void
    {
        // singleton, nie bind: połączenie z bazą ma powstać raz na żądanie i dopiero
        // przy pierwszym użyciu - strona bez zapytań do bazy w ogóle go nie otwiera.
        $this->container->singleton('db', function () {
            $config = require BASE_PATH . '/config/database.php';

            return new Database($config);
        });

        $this->container->singleton('translator', function () {
            // Nieznana wartość w ciasteczku wraca do domyślnego języka (patrz Translator).
            return new Translator((string) ($_COOKIE['locale'] ?? 'en'));
        });
    }

    private function loadTranslations(): void
    {
        /** @var Translator $translator */
        $translator = $this->container->make('translator');
        $translator->load(BASE_PATH . '/src');
    }

    private function registerRoutes(): void
    {
        $module = require BASE_PATH . '/config/module.php';

        foreach ($module['routes'] as $route) {
            $this->router->add($route['method'], $route['path'], $route['controller'], $route['action']);
        }

        // Opcjonalnie: src/routes.php dokłada trasy aplikacji, żeby nie puchł config/module.php.
        $appRoutes = BASE_PATH . '/src/routes.php';

        if (is_file($appRoutes)) {
            foreach (require $appRoutes as $route) {
                $this->router->add($route['method'], $route['path'], $route['controller'], $route['action']);
            }
        }
    }
}
