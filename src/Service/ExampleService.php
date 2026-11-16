<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Example;
use App\Repository\ExampleRepository;
use Engine\Database;

/**
 * Logika domenowa: reguły, których nie chcemy mieć w kontrolerze ani w repozytorium.
 *
 * Kontroler zna HTTP, repozytorium zna SQL, serwis zna zasady.
 * Dzięki temu te same reguły działają tak samo z kontrolera, z crona i z CLI.
 */
final class ExampleService
{
    private const MAKS_DLUGOSC_TYTULU = 255;

    public function __construct(
        private ExampleRepository $repository,
        private Database $db,
    ) {}

    /** @throws \InvalidArgumentException gdy tytuł jest pusty, za długi albo się powtarza */
    public function create(string $title): Example
    {
        $title = $this->czystyTytul($title);

        if ($this->repository->findOneBy(['title' => $title]) !== null) {
            throw new \InvalidArgumentException('Taki tytuł już istnieje.');
        }

        return $this->repository->save(Example::create($title));
    }

    /**
     * Przykład transakcji: albo zapisują się wszystkie pozycje, albo żadna.
     * Wyjątek w środku pętli cofa wszystko, co zdążyło wejść do bazy.
     *
     * @param list<string> $titles
     * @return list<Example>
     */
    public function createMany(array $titles): array
    {
        return $this->db->transaction(function () use ($titles): array {
            $zapisane = [];

            foreach ($titles as $title) {
                $zapisane[] = $this->create($title);
            }

            return $zapisane;
        });
    }

    private function czystyTytul(string $title): string
    {
        $title = trim($title);

        if ($title === '') {
            throw new \InvalidArgumentException('Tytuł jest wymagany.');
        }

        if (mb_strlen($title) > self::MAKS_DLUGOSC_TYTULU) {
            throw new \InvalidArgumentException('Tytuł jest za długi (maksymalnie ' . self::MAKS_DLUGOSC_TYTULU . ' znaków).');
        }

        return $title;
    }
}
