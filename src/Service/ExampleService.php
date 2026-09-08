<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\ExampleRepository;

final class ExampleService
{
    public function __construct(
        private ExampleRepository $repository
    ) {}

    public function createItem(string $title): int
    {
        $title = trim($title);

        if ($title === '') {
            throw new \InvalidArgumentException('Title cannot be empty.');
        }

        return $this->repository->create($title);
    }
}
