<?php
declare(strict_types=1);

namespace Engine;

use Engine\Translation\Translator;

class Kernel
{
    private ServiceContainer $container;
    private Router $router;
    private string $version = '1.0.0';

    public function __construct()
    {
        $this->container = new ServiceContainer();
        $this->router    = new Router();
    }

    public function boot(): void
    {
        $this->registerCoreServices();
        $this->loadModules();
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

        if (!method_exists($controller, $action)) {
            return Response::notFound('Akcja nie istnieje.');
        }

        $params = $this->router->getParams($route['path'], $request->uri);

        return $controller->$action($request, $params);
    }

    // -------------------------------------------------------------------------
    // Prywatne
    // -------------------------------------------------------------------------

    private function registerCoreServices(): void
    {
        // Database
        $this->container->singleton('db', function () {
            $config = require BASE_PATH . '/config/database.php';
            return new Database($config);
        });

        // Translator
        $this->container->singleton('translator', function () {
            $locale = $_COOKIE['locale'] ?? 'en';
            return new Translator($locale);
        });
    }

    private function loadModules(): void
    {
        $modulesPath = BASE_PATH . '/modules';

        if (is_dir($modulesPath)) {
            foreach (glob($modulesPath . '/*/module.php') as $moduleFile) {
                $module = require $moduleFile;

                if (!is_array($module)) {
                    continue;
                }

                // Tłumaczenia modułu
                if (isset($module['path'])) {
                    /** @var Translator $translator */
                    $translator = $this->container->make('translator');
                    $translator->loadModule($module['path']);
                }
            }
        }

        // src/ nadpisuje tłumaczenia
        /** @var Translator $translator */
        $translator = $this->container->make('translator');
        $translator->loadApp(BASE_PATH . '/src');
    }

    private function registerRoutes(): void
    {
        $module = require BASE_PATH . '/config/module.php';

        foreach ($module['routes'] as $route) {
            $this->router->add(
                $route['method'],
                $route['path'],
                $route['controller'],
                $route['action']
            );
        }

        // src/ może dorzucić własne trasy
        $appRoutes = BASE_PATH . '/src/routes.php';
        if (file_exists($appRoutes)) {
            $routes = require $appRoutes;
            foreach ($routes as $route) {
                $this->router->add(
                    $route['method'],
                    $route['path'],
                    $route['controller'],
                    $route['action']
                );
            }
        }
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getContainer(): ServiceContainer
    {
        return $this->container;
    }
}