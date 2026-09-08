<?php
declare(strict_types=1);

namespace App\Repository;

use Engine\Database;
use App\Schema\ExampleSchema;

final class ExampleRepository
{
    private string $table = ExampleSchema::TABLE;

    public function __construct(
        private Database $db
    ) {}

    public function findAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC"
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    public function create(string $title): int
    {
        return $this->db->insert($this->table, [
            'title' => $title,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->delete($this->table, ['id' => $id]);
    }
}
