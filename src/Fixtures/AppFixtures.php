<?php
declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\Example;
use App\Repository\ExampleRepository;
use Engine\Database;

/**
 * Dane startowe dla środowiska dev - ładowane przez tools/reset-db.php.
 * Zapisujemy przez repozytorium i encje, czyli tą samą drogą co aplikacja.
 */
final class AppFixtures
{
    public function __construct(
        private Database $db
    ) {}

    public function load(): void
    {
        $repozytorium = new ExampleRepository($this->db);

        $pozycje = [
            Example::create('Pierwsza pozycja'),
            Example::create('Druga pozycja'),
            Example::create('Trzecia pozycja, nieaktywna', isActive: false),
        ];

        foreach ($pozycje as $pozycja) {
            $repozytorium->save($pozycja);
        }
    }
}
