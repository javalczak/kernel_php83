<?php
declare(strict_types=1);

namespace App\Schema;

use Engine\Database;

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
