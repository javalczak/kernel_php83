<?php
declare(strict_types=1);

/**
 * Router wbudowanego serwera PHP (php -S localhost:8000 router.php).
 * Na produkcji tego pliku nie ma — tam rolę przejmuje Apache i .htaccess.
 */

// Katalogi, z których wolno serwować pliki wprost. Bez tej listy serwer oddawał
// w przeglądarce KAŻDY plik z projektu, razem z config/database.php i .git.
// tools/ jest tu tylko dlatego, że to serwer deweloperski — na produkcji
// .htaccess blokuje ten katalog, a i tak się go nie wgrywa na FTP.
const KATALOGI_PUBLICZNE = ['/assets/', '/tools/'];

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$publiczny = false;

// ".." odrzucamy od razu — inaczej /assets/../config/database.php przechodzi przez listę.
if (!str_contains($path, '..')) {
    foreach (KATALOGI_PUBLICZNE as $katalog) {
        if (str_starts_with($path, $katalog)) {
            $publiczny = true;
            break;
        }
    }
}

// is_file, nie file_exists — katalog nie jest plikiem do odesłania.
if ($publiczny && is_file(__DIR__ . $path)) {
    return false; // false = serwer sam odda plik
}

require __DIR__ . '/index.php';
