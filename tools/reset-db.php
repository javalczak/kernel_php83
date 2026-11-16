<?php
declare(strict_types=1);

/**
 * Reset bazy dla dewelopera: drop + create + fixtures.
 * Wyłącznie lokalnie — na produkcji tego pliku po prostu nie wrzucamy na FTP.
 */

require_once __DIR__ . '/../autoload.php';

use App\Fixtures\AppFixtures;
use App\Schema\SchemaInstaller;
use Engine\Database;

$config = require __DIR__ . '/../config/database.php';

// -------------------------------------------------------------------------
// Tryb konsolowy: php tools/reset-db.php --force
// -------------------------------------------------------------------------

// Z konsoli token jest bez sensu — kto ma dostęp do plików, ma i tak wszystko.
if (PHP_SAPI === 'cli') {
    if (!in_array('--force', $argv ?? [], true)) {
        die("Reset usuwa wszystkie tabele. Uruchom: php tools/reset-db.php --force\n");
    }

    resetujBaze($config);
    echo "Baza zresetowana.\n";
    exit;
}

// -------------------------------------------------------------------------
// Tryb przeglądarkowy: tylko localhost
// -------------------------------------------------------------------------

// Pierwsza zapora: nawet z poprawnym tokenem skrypt nie odpali się spoza tej maszyny.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    die('Reset bazy działa tylko lokalnie.');
}

$token = $config['reset_token'] ?? null;

// Token z repozytorium zna każdy, kto widział kod — póki go nie zmienisz, wejścia nie ma.
if (!$token || $token === 'change-me-to-a-long-random-string') {
    http_response_code(403);
    die('Ustaw własny reset_token w config/database.php.');
}

$provided = $_GET['token'] ?? $_POST['token'] ?? null;

// hash_equals zamiast ===, żeby porównanie trwało tyle samo niezależnie od tego,
// ile znaków się zgadza (obrona przed timing attack).
if (!hash_equals($token, (string) $provided)) {
    http_response_code(403);
    die('Nieautoryzowany dostęp.');
}

// -------------------------------------------------------------------------
// GET pokazuje formularz, POST wykonuje reset
// -------------------------------------------------------------------------

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Reset bazy</title>
        <style>
            body { font-family: sans-serif; max-width: 500px; margin: 80px auto; }
            .danger { color: #c00; }
            button { background: #c00; color: #fff; padding: 10px 24px; border: none; cursor: pointer; font-size: 16px; }
            button:hover { opacity: 0.85; }
        </style>
    </head>
    <body>
    <h2>Reset bazy danych</h2>
    <p class="danger">Operacja usunie wszystkie tabele i utworzy je ponownie. Dane przepadną.</p>

    <form method="POST" action="/tools/reset-db.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <p>Wpisz <strong>RESET</strong>, żeby potwierdzić:</p>
        <input type="text" name="confirm" placeholder="RESET" style="padding:8px; font-size:16px;">
        <br><br>
        <button type="submit">Resetuj bazę</button>
    </form>
    </body>
    </html>
    <?php
    exit;
}

// -------------------------------------------------------------------------
// POST — wykonaj reset
// -------------------------------------------------------------------------

if (trim((string) ($_POST['confirm'] ?? '')) !== 'RESET') {
    http_response_code(422);
    die('Nieprawidłowe potwierdzenie. Wpisz RESET.');
}

try {
    resetujBaze($config);

    echo '<pre style="font-family:sans-serif; max-width:600px; margin:80px auto;">';
    echo "Baza zresetowana.\n";
    echo "Tabele usunięte, utworzone ponownie i wypełnione danymi startowymi.\n\n";
    echo '<a href="/">Przejdź na stronę główną</a>';
    echo '</pre>';
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre style="color:#c00;">Błąd: ' . htmlspecialchars($e->getMessage()) . '</pre>';
}

/** Drop, create, fixtures — wspólne dla trybu konsolowego i przeglądarkowego. */
function resetujBaze(array $config): void
{
    // Najpierw połączenie BEZ nazwy bazy — inaczej nie da się jej utworzyć, gdy jeszcze nie istnieje.
    $configBezBazy = $config;
    $configBezBazy['dbname'] = '';

    $db = new Database($configBezBazy);

    // Nazwa bazy jest z pliku konfiguracyjnego, nie z requestu, ale i tak przechodzi
    // przez identifier() — jeden wzorzec na wszystkie nazwy w SQL-u.
    $db->query('CREATE DATABASE IF NOT EXISTS ' . $db->identifier($config['dbname'])
        . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $db = new Database($config);
    $installer = new SchemaInstaller($db);

    $installer->uninstall();
    $installer->install();

    (new AppFixtures($db))->load();
}
