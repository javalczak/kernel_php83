<?php
declare(strict_types=1);

namespace App\Fixtures;

use Engine\Database;
use App\Schema\ExampleSchema;

final class AppFixtures
{
    public function __construct(
        private Database $db
    ) {}

    public function load(): void
    {
        $this->insertItems();
    }

    private function insertItems(): void
    {
        $items = [
            'First item',
            'Second item',
            'Third item',
        ];

        foreach ($items as $title) {
            $this->db->insert(ExampleSchema::TABLE, [
                'title' => $title,
            ]);
        }
    }
}
