<?php
declare(strict_types=1);

namespace App\Repository;

use Engine\Database;
use App\Schema\PropertyTypeSchema;

final class PropertyTypeRepository
{
    private string $table = PropertyTypeSchema::TABLE;

    public function __construct(
        private Database $db
    ) {}

    public function findAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY sort_order ASC, name ASC"
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE code = ?", [$code]);
    }

    public function create(string $code, string $name, ?string $icon = null): int
    {
        return $this->db->insert($this->table, [
            'code' => $code,
            'name' => $name,
            'icon' => $icon,
        ]);
    }

    public function toggleActive(int $id): void
    {
        $type = $this->findById($id);
        if ($type === null) {
            return;
        }

        $this->db->update(
            $this->table,
            ['is_active' => $type['is_active'] ? 0 : 1],
            ['id' => $id]
        );
    }
}
