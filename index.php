<?php
declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    // PHP's default session.gc_maxlifetime (1440s / 24min) was expiring
    // sessions mid-form on longer admin tasks (e.g. adding several
    // locations/photos in one sitting) — extended 4x to 5760s / 96min.
    $sessionLifetime = 1440 * 4;
    ini_set('session.gc_maxlifetime', (string)$sessionLifetime);

    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

use Engine\Kernel;

$kernel = new Kernel();
$kernel->boot();

$request = $kernel->createRequest();
$response = $kernel->handle($request);
$response->send();