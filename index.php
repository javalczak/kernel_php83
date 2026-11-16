<?php
declare(strict_types=1);

/**
 * Front controller - jedyny plik, przez który wchodzi ruch.
 * Każdy adres trafia tutaj (na produkcji kieruje go .htaccess, lokalnie router.php).
 */

require_once __DIR__ . '/autoload.php';

use Engine\Kernel;

if (session_status() === PHP_SESSION_NONE) {
    // Domyślne 24 minuty PHP potrafią wygasnąć w trakcie wypełniania formularza,
    // a wygasła sesja to unieważniony token CSRF. Stąd 96 minut.
    $czasZycia = 1440 * 4;
    ini_set('session.gc_maxlifetime', (string) $czasZycia);

    session_set_cookie_params([
        'lifetime' => $czasZycia,
        'path'     => '/',
        'domain'   => '',
        'secure'   => !empty($_SERVER['HTTPS']), // po HTTPS ciasteczko nie wyjdzie czystym HTTP
        'httponly' => true,                      // JavaScript go nie odczyta
        'samesite' => 'Lax',                     // nie leci przy żądaniach z obcych stron
    ]);

    session_start();
}

// nosniff: przeglądarka nie zgaduje typu pliku. DENY: strony nie da się wsadzić w ramkę
// (clickjacking). same-origin: nasz adres nie wycieka w nagłówku Referer na obce strony.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

$kernel = new Kernel();
$kernel->boot();

$kernel->handle($kernel->createRequest())->send();
