<?php
declare(strict_types=1);

/**
 * Szybki test warstwy encji i repozytorium, bez PHPUnita i bez Composera.
 *
 *   php tools/smoke-test.php
 *
 * Działa na tabeli examples w bazie z config/database.php - po sobie sprząta.
 * To nie są testy jednostkowe z prawdziwego zdarzenia, tylko kontrola,
 * czy po zmianie w silniku CRUD dalej się trzyma kupy.
 */

// Tylko z konsoli. Przez przeglądarkę ten plik grzebałby komuś w bazie.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('Uruchom z konsoli: php tools/smoke-test.php');
}

require_once __DIR__ . '/../autoload.php';

use App\Entity\Example;
use App\Repository\ExampleRepository;
use App\Service\ExampleService;
use Engine\Database;

$bledy = 0;
$zrobione = 0;

/** Zwykły assert z czytelnym opisem - tyle wystarczy. */
function sprawdz(string $opis, bool $warunek): void
{
    global $bledy, $zrobione;

    $zrobione++;

    if ($warunek) {
        echo "  ok   {$opis}\n";

        return;
    }

    $bledy++;
    echo "  BŁĄD {$opis}\n";
}

function sprawdzWyjatek(string $opis, callable $kod): void
{
    try {
        $kod();
        sprawdz($opis, false);
    } catch (\Throwable) {
        sprawdz($opis, true);
    }
}

$db = new Database(require __DIR__ . '/../config/database.php');
$repozytorium = new ExampleRepository($db);
$serwis = new ExampleService($repozytorium, $db);

$znacznik = 'SMOKE_' . bin2hex(random_bytes(4));

echo "Encja\n";

$encja = Example::create($znacznik . '_a');
sprawdz('nowa encja nie ma id', $encja->getId() === null);
sprawdz('toRow nie zawiera id', !array_key_exists('id', $encja->toRow()));
sprawdz('toRow zamienia bool na int', $encja->toRow()['is_active'] === 1);

$zHydratowana = Example::fromRow(['id' => '7', 'title' => 'x', 'is_active' => '0', 'created_at' => '2026-01-01 10:00:00']);
sprawdz('fromRow rzutuje id na int', $zHydratowana->getId() === 7);
sprawdz('fromRow rzutuje is_active na bool', $zHydratowana->isActive === false);

echo "Repozytorium\n";

$zapisana = $repozytorium->save($encja);
sprawdz('save nadaje id', $zapisana->getId() !== null);
sprawdz('encja wejściowa się nie zmieniła', $encja->getId() === null);

$odczytana = $repozytorium->findById((int) $zapisana->getId());
sprawdz('findById zwraca obiekt Example', $odczytana instanceof Example);
sprawdz('findById zwraca ten sam tytuł', $odczytana?->title === $znacznik . '_a');
sprawdz('findById dla nieistniejącego id zwraca null', $repozytorium->findById(999_999_999) === null);

$zmieniona = $repozytorium->save($zapisana->withTitle($znacznik . '_b'));
sprawdz('save z id robi update, nie insert', $zmieniona->getId() === $zapisana->getId());
sprawdz('update faktycznie zmienił tytuł', $repozytorium->findById((int) $zapisana->getId())?->title === $znacznik . '_b');

sprawdz('findOneBy znajduje po kolumnie', $repozytorium->findOneBy(['title' => $znacznik . '_b'])?->getId() === $zapisana->getId());
sprawdz('findActive zwraca same aktywne', array_filter($repozytorium->findActive(), static fn (Example $e): bool => !$e->isActive) === []);
sprawdz('searchByTitle znajduje po fragmencie', count($repozytorium->searchByTitle($znacznik)) === 1);
sprawdz('count z kryteriami liczy poprawnie', $repozytorium->count(['title' => $znacznik . '_b']) === 1);

echo "Walidacja (to ma rzucać wyjątkami)\n";

sprawdzWyjatek('ORDER BY z lewym SQL-em leci wyjątkiem', static fn () => $repozytorium->findAll('id; DROP TABLE examples'));
sprawdzWyjatek('nazwa kolumny z cudzysłowem leci wyjątkiem', static fn () => $repozytorium->findBy(['ti`tle' => 'x']));
sprawdzWyjatek('UPDATE bez WHERE leci wyjątkiem', static fn () => $db->update('examples', ['title' => 'x'], []));
sprawdzWyjatek('DELETE bez WHERE leci wyjątkiem', static fn () => $db->delete('examples', []));
sprawdzWyjatek('pusty tytuł nie przechodzi przez serwis', static fn () => $serwis->create('   '));
sprawdzWyjatek('duplikat tytułu nie przechodzi przez serwis', static fn () => $serwis->create($znacznik . '_b'));

echo "Transakcja\n";

$przed = $repozytorium->count();

try {
    // Druga pozycja jest duplikatem pierwszej, więc całość ma się wycofać
    $serwis->createMany([$znacznik . '_c', $znacznik . '_c']);
} catch (\Throwable) {
    // tego się spodziewamy
}

sprawdz('nieudana transakcja nie zostawia śmieci', $repozytorium->count() === $przed);

// -------------------------------------------------------------------------
// Sprzątanie
// -------------------------------------------------------------------------

foreach ($repozytorium->searchByTitle($znacznik) as $doUsuniecia) {
    $repozytorium->delete($doUsuniecia);
}

sprawdz('po sprzątaniu nie ma danych testowych', $repozytorium->searchByTitle($znacznik) === []);

echo "\n" . ($bledy === 0 ? "Wszystko przeszło ({$zrobione})." : "BŁĘDY: {$bledy} z {$zrobione}.") . "\n";

exit($bledy === 0 ? 0 : 1);
