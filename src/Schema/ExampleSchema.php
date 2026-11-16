<?php
declare(strict_types=1);

namespace App\Schema;

/**
 * Definicja tabeli jako kod PHP, nie plik .sql.
 *
 * Dzięki temu schemat jedzie na serwer razem z resztą przez FTP i instaluje się
 * z przeglądarki - bez konsoli, bez phpMyAdmina, bez migracji Doctrine.
 */
final class ExampleSchema
{
    public const string TABLE = 'examples';

    public static function createTableSql(): string
    {
        return '
            CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title      VARCHAR(255) NOT NULL,
                is_active  TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                -- indeks pod findActive() i sortowanie listy
                INDEX idx_active_created (is_active, created_at)
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ';
    }

    public static function dropTableSql(): string
    {
        return 'DROP TABLE IF EXISTS ' . self::TABLE;
    }
}
