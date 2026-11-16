<?php
declare(strict_types=1);

namespace Engine;

/**
 * Encja: jeden wiersz tabeli jako obiekt.
 *
 * Bez ORM-a i bez refleksji — mapowanie piszesz ręcznie w dwóch metodach.
 * Dzięki temu widać dokładnie, co skąd się bierze, a PHP i IDE pilnują typów:
 * $item->getTitle() zamiast $item['title'], literówka wychodzi od razu, nie na produkcji.
 */
interface Entity
{
    /** Wiersz z bazy (tablica asocjacyjna z PDO) na obiekt. */
    public static function fromRow(array $row): static;

    /**
     * Obiekt na kolumny do zapisu. Bez id i bez kolumn,
     * które wypełnia sama baza (np. created_at).
     *
     * @return array<string, string|int|float|bool|null>
     */
    public function toRow(): array;

    /** null = encja jeszcze nie jest w bazie. */
    public function getId(): ?int;

    /** Kopia z nadanym id — repozytorium woła to po insercie. */
    public function withId(int $id): static;
}
