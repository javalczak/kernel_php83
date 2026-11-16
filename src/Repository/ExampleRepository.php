<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Example;
use App\Schema\ExampleSchema;
use Engine\Repository;

/**
 * Repozytorium tabeli `examples`.
 *
 * Cały CRUD przychodzi z Engine\Repository — tu zostaje tylko to,
 * co dotyczy tej jednej domeny. Tak samo buduje się każde kolejne repozytorium.
 *
 * @extends Repository<Example>
 */
final class ExampleRepository extends Repository
{
    protected function table(): string
    {
        return ExampleSchema::TABLE;
    }

    protected function entityClass(): string
    {
        return Example::class;
    }

    /**
     * Przykład własnego zapytania: findBy robi WHERE is_active = 1,
     * a sortowanie przechodzi przez walidację w klasie bazowej.
     *
     * @return list<Example>
     */
    public function findActive(): array
    {
        return $this->findBy(['is_active' => 1], 'created_at DESC');
    }

    /**
     * Przykład zapytania pisanego ręcznie — gdy findBy już nie wystarcza.
     * Wartość zawsze idzie przez ?, nigdy przez sklejanie stringa.
     *
     * @return list<Example>
     */
    public function searchByTitle(string $fragment): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM ' . $this->db->identifier($this->table()) . ' WHERE title LIKE ? ORDER BY title',
            ['%' . $fragment . '%']
        );

        return $this->hydrateAll($rows);
    }
}
