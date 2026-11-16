<?php
declare(strict_types=1);

namespace App\Entity;

use Engine\Entity;

/**
 * Przykładowa encja - wiersz tabeli `examples` jako obiekt.
 *
 * Właściwości są readonly, więc raz zbudowana encja się nie zmienia.
 * Zmiana = nowy obiekt (withTitle, withId). Dzięki temu nigdy nie trzeba szukać,
 * kto i gdzie podmienił pole w połowie żądania.
 */
final class Example implements Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly bool $isActive = true,
        public readonly ?string $createdAt = null,
    ) {}

    /** Nowa encja, jeszcze bez id - id nadaje baza przy zapisie. */
    public static function create(string $title, bool $isActive = true): self
    {
        return new self(null, $title, $isActive);
    }

    public static function fromRow(array $row): static
    {
        return new self(
            (int) $row['id'],
            (string) $row['title'],
            // MySQL odda TINYINT(1) jako "0"/"1" (string), więc rzutowanie jest konieczne
            (bool) $row['is_active'],
            $row['created_at'] ?? null,
        );
    }

    public function toRow(): array
    {
        // Bez id (ustawia je baza) i bez created_at (ma DEFAULT CURRENT_TIMESTAMP)
        return [
            'title'     => $this->title,
            'is_active' => (int) $this->isActive,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function withId(int $id): static
    {
        return new self($id, $this->title, $this->isActive, $this->createdAt);
    }

    public function withTitle(string $title): self
    {
        return new self($this->id, $title, $this->isActive, $this->createdAt);
    }

    public function deactivated(): self
    {
        return new self($this->id, $this->title, false, $this->createdAt);
    }
}
