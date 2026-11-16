<?php
declare(strict_types=1);

namespace App\Schema;

use Engine\Database;

/**
 * Zakłada i usuwa tabele aplikacji.
 *
 * Kolejność ma znaczenie: tabele z kluczami obcymi instaluj PO tych, na które
 * wskazują, a usuwaj w odwrotnej kolejności. Przy jednej tabeli to nieistotne,
 * przy pięciu to pierwsza rzecz, która się wywala.
 */
final class SchemaInstaller
{
    public function __construct(
        private Database $db
    ) {}

    public function install(): void
    {
        $this->db->query(ExampleSchema::createTableSql());
    }

    public function uninstall(): void
    {
        $this->db->query(ExampleSchema::dropTableSql());
    }
}
