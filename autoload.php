<?php
declare(strict_types=1);

/**
 * Autoloader bez Composera.
 *
 * Mapuje dwa namespace'y na katalogi i ładuje klasę dopiero wtedy, gdy PHP
 * jej faktycznie potrzebuje. To cały "dependency management" tego projektu —
 * nie ma vendor/, więc nie ma czego instalować na serwerze.
 */

define('BASE_PATH', __DIR__);

require_once __DIR__ . '/engine/Helpers.php';

spl_autoload_register(function (string $class): void {
    $namespaces = [
        'Engine\\' => BASE_PATH . '/engine/',
        'App\\'    => BASE_PATH . '/src/',
    ];

    foreach ($namespaces as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        // Engine\Translation\Translator → engine/Translation/Translator.php
        $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

        if (is_file($file)) {
            require_once $file;

            return;
        }
    }
});
