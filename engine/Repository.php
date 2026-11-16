<?php
declare(strict_types=1);

namespace Engine;

/**
 * Baza repozytorium: jedna tabela, jedna encja.
 *
 * Repozytorium konkretnej tabeli dziedziczy po tym i podaje tylko nazwę tabeli
 * i klasę encji. Resztę — findAll, findById, save, delete — dostaje gotową.
 * Zapytania specyficzne dla domeny (np. findActive) dopisujesz w potomku.
 *
 * @template T of Entity
 */
abstract class Repository
{
    public function __construct(
        protected Database $db
    ) {}

    abstract protected function table(): string;

    /** @return class-string<T> */
    abstract protected function entityClass(): string;

    /** @return list<T> */
    public function findAll(string $orderBy = 'id DESC'): array
    {
        return $this->hydrateAll(
            $this->db->fetchAll('SELECT * FROM ' . $this->db->identifier($this->table()) . $this->orderBy($orderBy))
        );
    }

    /** @return T|null */
    public function findById(int $id): ?Entity
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM ' . $this->db->identifier($this->table()) . ' WHERE id = ?',
            [$id]
        );

        return $row === null ? null : $this->hydrate($row);
    }

    /**
     * @param array<string, scalar|null> $criteria kolumna => wartość, łączone przez AND
     * @return list<T>
     */
    public function findBy(array $criteria, string $orderBy = 'id DESC'): array
    {
        if ($criteria === []) {
            return $this->findAll($orderBy);
        }

        $warunki = [];

        foreach (array_keys($criteria) as $kolumna) {
            $warunki[] = $this->db->identifier($kolumna) . ' = ?';
        }

        return $this->hydrateAll($this->db->fetchAll(
            'SELECT * FROM ' . $this->db->identifier($this->table())
            . ' WHERE ' . implode(' AND ', $warunki) . $this->orderBy($orderBy),
            array_values($criteria)
        ));
    }

    /**
     * @param array<string, scalar|null> $criteria
     * @return T|null
     */
    public function findOneBy(array $criteria): ?Entity
    {
        return $this->findBy($criteria)[0] ?? null;
    }

    /** @param array<string, scalar|null> $criteria */
    public function count(array $criteria = []): int
    {
        return $criteria === []
            ? (int) $this->db->fetchColumn('SELECT COUNT(*) FROM ' . $this->db->identifier($this->table()))
            : $this->db->count($this->table(), $criteria);
    }

    /**
     * Insert albo update — decyduje id encji.
     * Po insercie oddaje kopię z nadanym id, bo encja sama o nim nie wie.
     *
     * @param T $entity
     * @return T
     */
    public function save(Entity $entity): Entity
    {
        $id = $entity->getId();

        if ($id === null) {
            return $entity->withId($this->db->insert($this->table(), $entity->toRow()));
        }

        $this->db->update($this->table(), $entity->toRow(), ['id' => $id]);

        return $entity;
    }

    /** @param int|T $entity id albo sama encja — jak wygodniej w kontrolerze */
    public function delete(int|Entity $entity): void
    {
        $id = $entity instanceof Entity ? $entity->getId() : $entity;

        if ($id === null) {
            throw new \InvalidArgumentException('Nie można usunąć encji, która nie ma id.');
        }

        $this->db->delete($this->table(), ['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     * @return T
     */
    protected function hydrate(array $row): Entity
    {
        return ($this->entityClass())::fromRow($row);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<T>
     */
    protected function hydrateAll(array $rows): array
    {
        return array_map($this->hydrate(...), $rows);
    }

    /**
     * ORDER BY nie da się przekazać przez parametr PDO, więc jedyne wyjście
     * to ścisła walidacja. Dozwolone jest wyłącznie "kolumna ASC|DESC", po przecinku.
     */
    private function orderBy(string $orderBy): string
    {
        $orderBy = trim($orderBy);

        if ($orderBy === '') {
            return '';
        }

        $czesci = [];

        foreach (explode(',', $orderBy) as $czesc) {
            $slowa = preg_split('/\s+/', trim($czesc)) ?: [];
            $kolumna = $this->db->identifier($slowa[0] ?? '');
            $kierunek = strtoupper($slowa[1] ?? 'ASC');

            if (count($slowa) > 2 || !in_array($kierunek, ['ASC', 'DESC'], true)) {
                throw new \InvalidArgumentException("Niepoprawne ORDER BY: {$orderBy}");
            }

            $czesci[] = $kolumna . ' ' . $kierunek;
        }

        return ' ORDER BY ' . implode(', ', $czesci);
    }
}
