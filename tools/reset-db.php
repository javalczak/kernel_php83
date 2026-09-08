<?php
declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

use App\Schema\SchemaInstaller;
use Engine\Database;
use App\Fixtures\AppFixtures;

// -------------------------------------------------------------------------
// Zabezpieczenie tokenem
// -------------------------------------------------------------------------

$config = require __DIR__ . '/../config/database.php';
$token  = $config['reset_token'] ?? null;

if (!$token) {
    die('Brak reset_token w konfiguracji.');
}

$provided = $_GET['token'] ?? $_POST['token'] ?? null;

if (!hash_equals($token, (string) $provided)) {
    http_response_code(403);
    die('Nieautoryzowany dostęp.');
}

// -------------------------------------------------------------------------
// Potwierdzenie – GET pokazuje formularz, POST wykonuje reset
// -------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Reset bazy</title>
        <style>
            body { font-family: sans-serif; max-width: 500px; margin: 80px auto; }
            .danger { color: #c00; }
            button.full  { background: #c00; color: #fff; padding: 10px 24px; border: none; cursor: pointer; font-size: 16px; }
            button.quick { background: #c80; color: #fff; padding: 10px 24px; border: none; cursor: pointer; font-size: 16px; }
            button:hover { opacity: 0.85; }
            hr { margin: 30px 0; }
        </style>
    </head>
    <body>
    <h2>⚠️ Reset bazy danych</h2>
    <p class="danger">Operacja usunie wszystkie tabele i utworzy je ponownie. Wszystkie dane zostaną utracone.</p>

    <form method="POST" action="/tools/reset-db.php?token=<?= htmlspecialchars($token) ?>">
        <p>Wpisz <strong>RESET</strong> aby potwierdzić:</p>
        <input type="text" name="confirm" placeholder="RESET" style="padding:8px; font-size:16px;">
        <br><br>
        <button type="submit" name="action" value="full" class="full">Pełny reset (usuwa wszystko)</button>
    </form>

    </body>
    </html>
    <?php
    exit;
}

// -------------------------------------------------------------------------
// POST – wykonaj reset
// -------------------------------------------------------------------------

$confirm = trim($_POST['confirm'] ?? '');

if ($confirm !== 'RESET') {
    die('Nieprawidłowe potwierdzenie. Wpisz RESET.');
}
try {
    // Najpierw połącz BEZ nazwy bazy – żeby ją utworzyć jeśli nie istnieje
    $configNoDB = $config;
    $configNoDB['dbname'] = '';

    $db = new Database($configNoDB);
    $db->query("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Teraz połącz normalnie z bazą
    $db        = new Database($config);
    $installer = new SchemaInstaller($db);

    $installer->uninstall();
    $installer->install();

    // Sam reset schematu pozostawia osierocone pliki (id-ki są reużywane),
    // dlatego czyścimy też uploads/ — pliki nie są w gicie, więc to bezpieczne.
    // Musi być PRZED fixtures, bo fixtures kopiują pliki do uploads/.
    $uploadsDir = __DIR__ . '/../uploads';
    if (is_dir($uploadsDir)) {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
    }

    (new AppFixtures($db))->load();

    echo '<pre style="font-family:sans-serif; max-width:600px; margin:80px auto;">';
    echo "✅ Baza zresetowana pomyślnie.\n";
    echo "Tabele usunięte i utworzone ponownie, katalog uploads/ wyczyszczony.\n\n";
    echo '<a href="/">→ Przejdź na stronę główną</a>';
    echo '</pre>';

} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre style="color:red;">';
    echo "❌ Błąd: " . $e->getMessage();
    echo '</pre>';
}